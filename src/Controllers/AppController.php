<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AgencyRepository;
use App\Repositories\TripRepository;
use App\Repositories\UserRepository;
use App\Support\TripValidator;
use DateTimeImmutable;
use PDOException;

/** HTTP actions, authorization, CSRF checks and flash redirects. */
final class AppController
{
    public function __construct(private TripRepository $trips, private AgencyRepository $agencies, private UserRepository $users) {}

    /** Dispatches an allowlisted path and method; mutations only accept POST. */
    public function handle(string $method, string $path): void
    {
        try {
            if ($method === 'POST') {
                $this->csrf();
            }
            if ($method === 'GET' && $path === '/') {
                $this->render('home', ['trips' => $this->trips->all()]);
            } elseif ($path === '/login' && $method === 'GET') {
                $this->render('login');
            } elseif ($path === '/login' && $method === 'POST') {
                $this->login();
            } elseif ($path === '/logout' && $method === 'POST') {
                $_SESSION = [];
                session_regenerate_id(true);
                $this->redirect('/', 'Vous êtes déconnecté.');
            } elseif ($path === '/trips/new' && $method === 'GET') {
                $this->requireUser();
                $this->render('trip-form', ['agencies' => $this->agencies->all(), 'trip' => [], 'action' => '/trips']);
            } elseif ($path === '/trips' && $method === 'POST') {
                $this->saveTrip(null);
            } elseif (preg_match('#^/trips/(\d+)/edit$#', $path, $matches) && $method === 'GET') {
                $this->tripForm((int) $matches[1]);
            } elseif (preg_match('#^/trips/(\d+)$#', $path, $matches) && $method === 'POST') {
                $this->saveTrip((int) $matches[1]);
            } elseif (preg_match('#^/trips/(\d+)/delete$#', $path, $matches) && $method === 'POST') {
                $this->deleteTrip((int) $matches[1]);
            } elseif ($path === '/admin' && $method === 'GET') {
                $this->requireAdmin();
                $this->render('admin', ['users' => $this->users->all(), 'agencies' => $this->agencies->all(), 'trips' => $this->trips->all(false)]);
            } elseif ($path === '/admin/agencies' && $method === 'POST') {
                $this->saveAgency(null);
            } elseif (preg_match('#^/admin/agencies/(\d+)$#', $path, $matches) && $method === 'POST') {
                $this->saveAgency((int) $matches[1]);
            } elseif (preg_match('#^/admin/agencies/(\d+)/delete$#', $path, $matches) && $method === 'POST') {
                $this->deleteAgency((int) $matches[1]);
            } else {
                http_response_code(404);
                $this->render('error', ['message' => 'Page introuvable.']);
            }
        } catch (PDOException $exception) {
            error_log((string) $exception);
            http_response_code(409);
            $this->render('error', ['message' => 'Cette opération est impossible : donnée déjà utilisée ou contrainte de base de données.']);
        }
    }

    private function login(): void
    {
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $user = is_string($email) ? $this->users->byEmail($email) : false;
        if (!$user || !password_verify((string) ($_POST['password'] ?? ''), (string) $user['password_hash'])) {
            http_response_code(401);
            $this->render('login', ['error' => 'Identifiants incorrects.']);
            return;
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $this->redirect($user['role'] === 'admin' ? '/admin' : '/', 'Bienvenue !');
    }

    private function tripForm(int $id): void
    {
        $this->requireUser();
        $trip = $this->trips->find($id);
        $this->authorizeTrip($trip);
        $this->render('trip-form', ['trip' => $trip, 'agencies' => $this->agencies->all(), 'action' => '/trips/' . $id]);
    }

    private function saveTrip(?int $id): void
    {
        $this->requireUser();
        if ($id !== null) {
            $this->authorizeTrip($this->trips->find($id));
        }
        $errors = TripValidator::validate($_POST, new DateTimeImmutable());
        foreach (['departure_agency_id', 'arrival_agency_id'] as $field) {
            if (!empty($_POST[$field]) && !$this->agencies->exists((int) $_POST[$field])) {
                $errors[] = 'Une agence sélectionnée est introuvable.';
            }
        }
        if ($errors !== []) {
            http_response_code(422);
            $this->render('trip-form', ['errors' => $errors, 'trip' => $_POST, 'agencies' => $this->agencies->all(), 'action' => $id === null ? '/trips' : '/trips/' . $id]);
            return;
        }
        if ($id === null) {
            $this->trips->create($_POST, (int) $_SESSION['user_id']);
        } else {
            $this->trips->update($id, $_POST);
        }
        $this->redirect($this->isAdmin() ? '/admin' : '/', $id === null ? 'Le trajet a été créé.' : 'Le trajet a été modifié.');
    }

    private function deleteTrip(int $id): void
    {
        $this->requireUser();
        $this->authorizeTrip($this->trips->find($id));
        $this->trips->delete($id);
        $this->redirect($this->isAdmin() ? '/admin' : '/', 'Le trajet a été supprimé.');
    }

    private function saveAgency(?int $id): void
    {
        $this->requireAdmin();
        if ($id !== null && !$this->agencies->find($id)) {
            $this->abort(404, 'Agence introuvable.');
        }
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '' || mb_strlen($name) > 100) {
            $this->abort(422, 'Le nom de l’agence est obligatoire (100 caractères maximum).');
        }
        if ($id === null) {
            $this->agencies->create($name);
        } else {
            $this->agencies->update($id, $name);
        }
        $this->redirect('/admin#agences', 'L’agence a été enregistrée.');
    }

    private function deleteAgency(int $id): void
    {
        $this->requireAdmin();
        if (!$this->agencies->find($id)) {
            $this->abort(404, 'Agence introuvable.');
        }
        $this->agencies->delete($id);
        $this->redirect('/admin#agences', 'L’agence a été supprimée.');
    }

    /** @param array<string,mixed>|false $trip */
    private function authorizeTrip(array|false $trip): void
    {
        if (!$trip) {
            $this->abort(404, 'Trajet introuvable.');
        }
        if (!$this->isAdmin() && (int) $trip['author_id'] !== (int) $_SESSION['user_id']) {
            $this->abort(403, 'Vous ne pouvez pas modifier ce trajet.');
        }
    }

    private function csrf(): void
    {
        if (!is_string($_POST['_csrf'] ?? null) || !hash_equals((string) $_SESSION['csrf'], $_POST['_csrf'])) {
            $this->abort(403, 'Formulaire expiré ou invalide.');
        }
    }

    private function requireUser(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->abort(403, 'Connectez-vous pour accéder à cette page.');
        }
    }

    private function isAdmin(): bool
    {
        if (empty($_SESSION['user_id'])) {
            return false;
        }
        $user = $this->users->find((int) $_SESSION['user_id']);
        return $user && $user['role'] === 'admin';
    }

    private function requireAdmin(): void
    {
        $this->requireUser();
        if (!$this->isAdmin()) {
            $this->abort(403, 'Accès réservé à l’administrateur.');
        }
    }

    private function abort(int $code, string $message): never
    {
        http_response_code($code);
        $this->render('error', ['message' => $message]);
        exit;
    }

    private function redirect(string $path, string $message): never
    {
        $_SESSION['flash'] = $message;
        header('Location: ' . $path, true, 303);
        exit;
    }

    /** @param array<string,mixed> $variables */
    private function render(string $view, array $variables = []): void
    {
        $user = !empty($_SESSION['user_id']) ? $this->users->find((int) $_SESSION['user_id']) : false;
        extract($variables, EXTR_SKIP);
        require dirname(__DIR__, 2) . '/views/layout.php';
    }
}
