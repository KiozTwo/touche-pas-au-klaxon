<?php
/** @var string $view */
/** @var array<string,mixed>|false $user */
function e(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function token(): string { return '<input type="hidden" name="_csrf" value="' . e($_SESSION['csrf']) . '">'; }
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Touche pas au klaxon</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="/assets/style.css"></head>
<body class="d-flex flex-column min-vh-100"><header class="container mt-3"><nav class="navbar navbar-expand-lg border border-dark rounded-4 px-3" aria-label="Navigation principale"><a class="navbar-brand fw-bold" href="<?= $user && $user['role'] === 'admin' ? '/admin' : '/' ?>">Touche pas au klaxon</a><div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
<?php if (!$user): ?><a class="btn btn-dark" href="/login">Connexion</a>
<?php else: ?><?php if ($user['role'] === 'admin'): ?><a class="btn btn-secondary" href="/admin#utilisateurs">Utilisateurs</a><a class="btn btn-secondary" href="/admin#agences">Agences</a><a class="btn btn-secondary" href="/admin#trajets">Trajets</a><?php else: ?><a class="btn btn-dark" href="/trips/new">Créer un trajet</a><?php endif; ?>
<span class="mx-1">Bonjour <?= e($user['first_name'] . ' ' . $user['last_name']) ?></span><form action="/logout" method="post" class="m-0"><?= token() ?><button class="btn btn-dark">Déconnexion</button></form><?php endif; ?></div></nav></header>
<main class="container py-4 flex-grow-1"><?php if (isset($_SESSION['flash'])): ?><div class="alert alert-secondary" role="status"><?= e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); endif; ?><?php require __DIR__ . '/' . $view . '.php'; ?></main>
<footer class="text-center py-3">© <?= date('Y') ?> Touche pas au klaxon</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>
