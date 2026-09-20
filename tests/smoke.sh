#!/usr/bin/env bash
# End-to-end HTTP smoke test on an isolated MySQL database; generates disposable credentials.
set -euo pipefail
work_dir="$(mktemp -d)"
server_pid=''
cleanup() { local result=$?; if [[ "$result" -ne 0 ]]; then echo 'Smoke test diagnostic:'; tail -30 "$work_dir/server.log" 2>/dev/null || true; for f in login.html home.html create.html; do if [[ -f "$work_dir/$f" ]]; then echo "$f: $(wc -c < "$work_dir/$f") bytes"; fi; done; fi; if [[ -n "$server_pid" ]]; then kill "$server_pid" 2>/dev/null || true; fi; rm -rf "$work_dir"; }
trap cleanup EXIT
smoke_password="$(openssl rand -hex 20)"
export SMOKE_PASS="$smoke_password"
smoke_hash="$(php -r 'echo password_hash(getenv("SMOKE_PASS"), PASSWORD_DEFAULT);')"
mysql -h 127.0.0.1 -u root -proot klaxon_test -e "INSERT INTO agencies(name) VALUES ('Demo A'),('Demo B'); INSERT INTO users(last_name,first_name,phone,email,password_hash,role) VALUES ('Test','Auteur','0000000000','smoke@example.test','$smoke_hash','employee'),('Test','Autre','0000000000','other@example.test','$smoke_hash','employee');"
DB_NAME=klaxon_test DB_HOST=127.0.0.1 DB_USER=root DB_PASSWORD=root php -S 127.0.0.1:8765 -t public public/router.php > "$work_dir/server.log" 2>&1 &
server_pid=$!
for _ in {1..20}; do curl -fsS -c "$work_dir/cookies" -b "$work_dir/cookies" http://127.0.0.1:8765/login -o "$work_dir/login.html" && break || sleep .5; done
grep -q 'name="email"' "$work_dir/login.html"
echo "Login page rendered"
csrf="$(sed -n 's/.*name="_csrf" value="\([^"]*\)".*/\1/p' "$work_dir/login.html" | head -1)"
[[ -n "$csrf" ]]
echo "CSRF token found"
status="$(curl -sS -c "$work_dir/cookies" -b "$work_dir/cookies" -o "$work_dir/home.html" -w '%{http_code}' -d "_csrf=$csrf" -d 'email=smoke@example.test' --data-urlencode "password=$smoke_password" http://127.0.0.1:8765/login)"
echo "Login HTTP status: $status"
[[ "$status" == 303 ]]
curl -fsS -b "$work_dir/cookies" http://127.0.0.1:8765/ -o "$work_dir/home.html"
grep -q 'Créer un trajet' "$work_dir/home.html"
echo "Authenticated homepage rendered"
agency_a="$(mysql -N -h 127.0.0.1 -u root -proot klaxon_test -e "SELECT id FROM agencies WHERE name='Demo A'")"
agency_b="$(mysql -N -h 127.0.0.1 -u root -proot klaxon_test -e "SELECT id FROM agencies WHERE name='Demo B'")"
start="$(date -u -d '+2 days' '+%Y-%m-%dT%H:%M')"
end="$(date -u -d '+2 days 2 hours' '+%Y-%m-%dT%H:%M')"
status="$(curl -sS -b "$work_dir/cookies" -o "$work_dir/create.html" -w '%{http_code}' -d "_csrf=$csrf" -d "departure_agency_id=$agency_a" -d "arrival_agency_id=$agency_b" --data-urlencode "departure_at=$start" --data-urlencode "arrival_at=$end" -d 'total_seats=4' -d 'available_seats=3' http://127.0.0.1:8765/trips)"
echo "Create HTTP status: $status"
if [[ "$status" != 303 ]]; then sed -n 's/.*role="alert">\([^<]*\)<.*/Validation: \1/p' "$work_dir/create.html"; fi
[[ "$status" == 303 ]]
count="$(mysql -N -h 127.0.0.1 -u root -proot klaxon_test -e "SELECT COUNT(*) FROM trips WHERE author_id=(SELECT id FROM users WHERE email='smoke@example.test')")"
[[ "$count" == 1 ]]
curl -fsS -b "$work_dir/cookies" http://127.0.0.1:8765/ -o "$work_dir/home.html"
grep -q 'Demo A' "$work_dir/home.html"
grep -q 'data-bs-toggle="modal"' "$work_dir/home.html"
curl -fsS http://127.0.0.1:8765/ -o "$work_dir/visitor.html"
grep -q 'Demo A' "$work_dir/visitor.html"
if grep -q 'smoke@example.test' "$work_dir/visitor.html"; then echo 'Public contact leak'; exit 1; fi
mysql -h 127.0.0.1 -u root -proot klaxon_test -e "INSERT INTO trips(departure_agency_id,arrival_agency_id,departure_at,arrival_at,total_seats,available_seats,author_id) VALUES($agency_a,$agency_b,NOW()+INTERVAL 3 DAY,NOW()+INTERVAL 3 DAY+INTERVAL 2 HOUR,4,3,(SELECT id FROM users WHERE email='other@example.test'));"
other_id="$(mysql -N -h 127.0.0.1 -u root -proot klaxon_test -e "SELECT id FROM trips WHERE author_id=(SELECT id FROM users WHERE email='other@example.test') LIMIT 1")"
status="$(curl -sS -b "$work_dir/cookies" -o /dev/null -w '%{http_code}' -d "_csrf=$csrf" "http://127.0.0.1:8765/trips/$other_id/delete")"
[[ "$status" == 403 ]]
[[ "$(mysql -N -h 127.0.0.1 -u root -proot klaxon_test -e "SELECT COUNT(*) FROM trips WHERE id=$other_id")" == 1 ]]
echo 'HTTP smoke test passed: login, create trip, ownership guard.'
