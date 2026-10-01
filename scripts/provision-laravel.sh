#!/bin/bash
# 라라벨 프로젝트 원클릭 빌드
# 비밀번호는 인자로 받지 않고, PHP가 만든 인증 파일을 source 한 뒤 바로 삭제한다.
#
# 서버에서 한 번만 준비할 것 (루트 키는 이 준비에만 사용):
# 1. 빌드 계정 sudoers. 큐 실행 계정에서 아래 명령만 NOPASSWD.
#    useradd usermod chpasswd chown chmod find git mkdir cp ln rm nginx systemctl certbot
# 2. MySQL 전용 계정 (CREATE USER, GRANT 가능). 비밀번호는 서버 .env 의 PROVISION_DB_ADMIN_PASSWORD.
# 3. 템플릿 미러
#    sudo mkdir -p /opt/templates
#    sudo git clone --bare https://github.com/HOMEPAGE-KOREA/hk-laravel.git /opt/templates/hk-laravel.git
# 4. 큐 워커 상시 실행 (QUEUE_CONNECTION=database)

set -euo pipefail

CREDS_FILE="${1:-}"
if [ -z "$CREDS_FILE" ] || [ ! -f "$CREDS_FILE" ]; then
    echo "인증 파일을 찾을 수 없습니다." >&2
    exit 1
fi

set -a
# shellcheck disable=SC1090
source "$CREDS_FILE"
set +a
rm -f "$CREDS_FILE"

MYSQL_CNF=""
SQL_FILE=""
TMP_NGINX=""
GH_BODY=""
CURL_CFG=""
GIT_ASKPASS_FILE=""

cleanup() {
    rm -f "${MYSQL_CNF:-}" "${SQL_FILE:-}" "${TMP_NGINX:-}" "${GH_BODY:-}" "${CURL_CFG:-}" "${GIT_ASKPASS_FILE:-}"
}
trap cleanup EXIT

as_root() {
    if [ "$(id -u)" -eq 0 ]; then
        "$@"
    else
        sudo -n "$@"
    fi
}

require_var() {
    local name="$1"
    if [ -z "${!name:-}" ]; then
        echo "필수 값이 없습니다: ${name}" >&2
        exit 1
    fi
}

step() {
    echo "[[STEP]] $1"
}

assert_identifier() {
    local label="$1"
    local value="$2"
    if ! [[ "$value" =~ ^[a-z][a-z0-9-]{1,30}$ ]]; then
        echo "${label} 형식이 올바르지 않습니다." >&2
        exit 1
    fi
}

for name in PROJECT_NAME ROOT_PASSWORD FTP_USERNAME FTP_PASSWORD DB_USERNAME DB_PASSWORD DB_DATABASE \
    PROVISION_DB_ADMIN_USER PROVISION_DB_ADMIN_PASSWORD GITHUB_TOKEN GITHUB_ORG TEMPLATE_MIRROR BASE_PATH DOMAIN_SUFFIX WEB_USER; do
    require_var "$name"
done

assert_identifier "프로젝트명" "$PROJECT_NAME"
assert_identifier "FTP 아이디" "$FTP_USERNAME"
assert_identifier "DB 아이디" "$DB_USERNAME"

if [ "$FTP_USERNAME" = "$PROJECT_NAME" ]; then
    echo "FTP 아이디는 프로젝트명과 달라야 합니다." >&2
    exit 1
fi

PROVISION_PHP_SOCKET="${PROVISION_PHP_SOCKET:-unix:/run/php/php8.4-fpm.sock}"
PROVISION_SSL_FULLCHAIN="${PROVISION_SSL_FULLCHAIN:-}"
PROVISION_SSL_PRIVKEY="${PROVISION_SSL_PRIVKEY:-}"
PROVISION_CERTBOT_EMAIL="${PROVISION_CERTBOT_EMAIL:-admin@homepagekorea.com}"

case "$PROVISION_PHP_SOCKET" in
    unix:*|*:*) ;;
    /*) PROVISION_PHP_SOCKET="unix:${PROVISION_PHP_SOCKET}" ;;
esac

PROJECT_DIR="${BASE_PATH}/${PROJECT_NAME}"
APP_URL="https://${PROJECT_NAME}.${DOMAIN_SUFFIX}"
export GIT_TERMINAL_PROMPT=0

step mysql
echo "데이터베이스 생성을 시작합니다."

if [ -e "$PROJECT_DIR" ]; then
    echo "프로젝트 디렉터리가 이미 있습니다: ${PROJECT_DIR}" >&2
    exit 1
fi

if id "$PROJECT_NAME" >/dev/null 2>&1; then
    echo "리눅스 계정이 이미 있습니다: ${PROJECT_NAME}" >&2
    exit 1
fi

if id "$FTP_USERNAME" >/dev/null 2>&1; then
    echo "FTP 계정이 이미 있습니다: ${FTP_USERNAME}" >&2
    exit 1
fi

MYSQL_CNF=$(mktemp)
SQL_FILE=$(mktemp)
chmod 600 "$MYSQL_CNF" "$SQL_FILE"
export MYSQL_CNF SQL_FILE
php <<'PHP'
<?php
$quote = static function (string $value): string {
    return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
};
$cnf = "[client]\n"
    ."user=".$quote((string) getenv('PROVISION_DB_ADMIN_USER'))."\n"
    ."password=".$quote((string) getenv('PROVISION_DB_ADMIN_PASSWORD'))."\n"
    ."host=127.0.0.1\n"
    ."protocol=tcp\n";
file_put_contents(getenv('MYSQL_CNF'), $cnf);
PHP

DB_EXISTS=$(mysql --defaults-extra-file="$MYSQL_CNF" -N -e "SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='${DB_DATABASE}'")
DB_EXISTS="${DB_EXISTS//[[:space:]]/}"
if [ "$DB_EXISTS" != "0" ]; then
    echo "데이터베이스가 이미 있습니다: ${DB_DATABASE}" >&2
    exit 1
fi

php <<'PHP'
<?php
$db = getenv('DB_DATABASE');
$user = getenv('DB_USERNAME');
$pass = str_replace(["\\", "'"], ["\\\\", "\\'"], (string) getenv('DB_PASSWORD'));
$sql = <<<SQL
CREATE DATABASE `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER '{$user}'@'127.0.0.1' IDENTIFIED BY '{$pass}';
CREATE USER '{$user}'@'localhost' IDENTIFIED BY '{$pass}';
GRANT ALL PRIVILEGES ON `{$db}`.* TO '{$user}'@'127.0.0.1';
GRANT ALL PRIVILEGES ON `{$db}`.* TO '{$user}'@'localhost';
FLUSH PRIVILEGES;
SQL;
file_put_contents(getenv('SQL_FILE'), $sql);
PHP

if ! mysql --defaults-extra-file="$MYSQL_CNF" < "$SQL_FILE"; then
    echo "데이터베이스 사용자 생성에 실패했습니다. 계정이 이미 있거나 권한이 없습니다." >&2
    exit 1
fi
echo "데이터베이스와 사용자를 만들었습니다."

step clone
echo "템플릿 복제를 시작합니다."

if [ ! -d "$TEMPLATE_MIRROR" ]; then
    echo "템플릿 미러가 없습니다: ${TEMPLATE_MIRROR}" >&2
    exit 1
fi

if git --git-dir="$TEMPLATE_MIRROR" show-ref --verify --quiet refs/heads/main; then
    BRANCH=main
elif git --git-dir="$TEMPLATE_MIRROR" show-ref --verify --quiet refs/heads/master; then
    BRANCH=master
else
    echo "템플릿 미러에 main 또는 master 브랜치가 없습니다." >&2
    exit 1
fi

as_root mkdir -p "$BASE_PATH"
as_root git clone --branch "$BRANCH" "$TEMPLATE_MIRROR" "$PROJECT_DIR"
as_root chown -R "$(id -un):$(id -gn)" "$PROJECT_DIR"
echo "템플릿을 복제했습니다."

step github
echo "깃 저장소 생성을 시작합니다."

GH_BODY=$(mktemp)
CURL_CFG=$(mktemp)
GIT_ASKPASS_FILE=$(mktemp)
chmod 600 "$CURL_CFG" "$GIT_ASKPASS_FILE"
export CURL_CFG GIT_ASKPASS_FILE
php <<'PHP'
<?php
$token = (string) getenv('GITHUB_TOKEN');
$quoted = "'".str_replace("'", "'\\''", $token)."'";
$header = 'header = "Authorization: Bearer '.str_replace(['\\', '"'], ['\\\\', '\\"'], $token).'"'."\n";
file_put_contents(getenv('CURL_CFG'), $header);
file_put_contents(getenv('GIT_ASKPASS_FILE'), "#!/bin/sh\nprintf 'username=x-access-token\\npassword=%s\\n' {$quoted}\n");
PHP
chmod 700 "$GIT_ASKPASS_FILE"

HTTP_CODE=$(curl -sS --config "$CURL_CFG" -o "$GH_BODY" -w "%{http_code}" -X POST \
    -H "Accept: application/vnd.github+json" \
    -H "X-GitHub-Api-Version: 2022-11-28" \
    "https://api.github.com/orgs/${GITHUB_ORG}/repos" \
    -d "{\"name\":\"${PROJECT_NAME}\",\"private\":true}")

if [ "$HTTP_CODE" != "201" ] && [ "$HTTP_CODE" != "422" ]; then
    echo "깃허브 저장소 생성에 실패했습니다. HTTP ${HTTP_CODE}" >&2
    exit 1
fi

cd "$PROJECT_DIR"
git remote remove origin 2>/dev/null || true
git remote add origin "https://github.com/${GITHUB_ORG}/${PROJECT_NAME}.git"
PUSH_BRANCH=$(git rev-parse --abbrev-ref HEAD)
GIT_ASKPASS="$GIT_ASKPASS_FILE" git -c "credential.helper=!${GIT_ASKPASS_FILE}" push -u origin "$PUSH_BRANCH"
git remote set-url origin "https://github.com/${GITHUB_ORG}/${PROJECT_NAME}.git"
echo "[[RESULT_GIT]] https://github.com/${GITHUB_ORG}/${PROJECT_NAME}"
echo "깃 저장소를 만들었습니다."

step laravel
echo "라라벨 설치를 시작합니다."

cd "$PROJECT_DIR"
if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! command -v composer >/dev/null 2>&1; then
    echo "composer 명령을 찾을 수 없습니다." >&2
    exit 1
fi

php <<'PHP'
<?php
$envPath = '.env';
$env = file_get_contents($envPath);
$map = [
    'APP_NAME' => getenv('PROJECT_NAME'),
    'APP_URL' => 'https://'.getenv('PROJECT_NAME').'.'.getenv('DOMAIN_SUFFIX'),
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_DATABASE' => getenv('DB_DATABASE'),
    'DB_USERNAME' => getenv('DB_USERNAME'),
    'DB_PASSWORD' => getenv('DB_PASSWORD'),
];
foreach ($map as $key => $value) {
    $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $value);
    $line = $key.'="'.$escaped.'"';
    $pattern = '/^'.preg_quote($key, '/').'=.*/m';
    if (preg_match($pattern, $env)) {
        $env = preg_replace($pattern, $line, $env, 1);
    } else {
        $env .= PHP_EOL.$line;
    }
}
file_put_contents($envPath, $env);
PHP

composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
php artisan key:generate --force
if [ ! -L public/storage ]; then
    php artisan storage:link
fi
php artisan migrate --force
php artisan db:seed --force
php artisan config:clear
php artisan view:clear
echo "라라벨 설치를 마쳤습니다."

step permission
echo "권한과 FTP 계정을 설정합니다."

NOLOGIN=/usr/sbin/nologin
if [ ! -x "$NOLOGIN" ]; then
    NOLOGIN=/bin/false
fi

as_root useradd -d "$PROJECT_DIR" -s /bin/bash "$PROJECT_NAME"
printf '%s:%s\n' "$PROJECT_NAME" "$ROOT_PASSWORD" | as_root chpasswd
as_root useradd -d "$PROJECT_DIR" -s "$NOLOGIN" -G "$WEB_USER" "$FTP_USERNAME"
printf '%s:%s\n' "$FTP_USERNAME" "$FTP_PASSWORD" | as_root chpasswd
as_root usermod -aG "$WEB_USER" "$PROJECT_NAME"

as_root chown -R "${PROJECT_NAME}:${WEB_USER}" "$PROJECT_DIR"
as_root find "$PROJECT_DIR" -type d -exec chmod 2775 {} \;
as_root find "$PROJECT_DIR" -type f -exec chmod 664 {} \;
as_root chmod 775 "$PROJECT_DIR/artisan"
if [ -d "$PROJECT_DIR/vendor/bin" ]; then
    as_root chmod -R 775 "$PROJECT_DIR/vendor/bin"
fi
as_root chmod -R ug+rwX "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache"
echo "디렉터리 권한과 FTP 계정을 설정했습니다."

step nginx
echo "웹서버 설정을 반영합니다."

CERT="${PROVISION_SSL_FULLCHAIN}"
KEY="${PROVISION_SSL_PRIVKEY}"
if [ -z "$CERT" ] && [ -f "/etc/letsencrypt/live/${DOMAIN_SUFFIX}/fullchain.pem" ]; then
    CERT="/etc/letsencrypt/live/${DOMAIN_SUFFIX}/fullchain.pem"
    KEY="/etc/letsencrypt/live/${DOMAIN_SUFFIX}/privkey.pem"
fi

SSL_ON=0
if [ -n "$CERT" ] && [ -f "$CERT" ] && [ -n "$KEY" ] && [ -f "$KEY" ]; then
    SSL_ON=1
fi

if [ -d /etc/nginx/sites-available ]; then
    NGINX_FILE="/etc/nginx/sites-available/${PROJECT_NAME}.conf"
    NGINX_ENABLED="/etc/nginx/sites-enabled/${PROJECT_NAME}.conf"
else
    NGINX_FILE="/etc/nginx/conf.d/${PROJECT_NAME}.conf"
    NGINX_ENABLED=""
fi

TMP_NGINX=$(mktemp)
if [ "$SSL_ON" -eq 1 ]; then
    cat > "$TMP_NGINX" <<EOF
server {
    listen 80;
    listen 443 ssl;
    server_name ${PROJECT_NAME}.${DOMAIN_SUFFIX};
    root ${PROJECT_DIR}/public;
    index index.php index.html;
    charset utf-8;

    ssl_certificate ${CERT};
    ssl_certificate_key ${KEY};

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_pass ${PROVISION_PHP_SOCKET};
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
else
    cat > "$TMP_NGINX" <<EOF
server {
    listen 80;
    server_name ${PROJECT_NAME}.${DOMAIN_SUFFIX};
    root ${PROJECT_DIR}/public;
    index index.php index.html;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_pass ${PROVISION_PHP_SOCKET};
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
fi

as_root cp "$TMP_NGINX" "$NGINX_FILE"
if [ -n "$NGINX_ENABLED" ]; then
    as_root ln -sfn "$NGINX_FILE" "$NGINX_ENABLED"
fi
as_root nginx -t
as_root systemctl reload nginx

if [ "$SSL_ON" -eq 0 ]; then
    if as_root certbot --nginx -d "${PROJECT_NAME}.${DOMAIN_SUFFIX}" --non-interactive --agree-tos -m "$PROVISION_CERTBOT_EMAIL" --redirect; then
        echo "인증서를 발급했습니다."
    else
        APP_URL="http://${PROJECT_NAME}.${DOMAIN_SUFFIX}"
        echo "인증서 발급에 실패해 HTTP로 열어둡니다."
    fi
fi

echo "[[RESULT_URL]] ${APP_URL}"
echo "웹서버 설정을 반영했습니다."
