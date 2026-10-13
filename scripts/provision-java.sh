#!/bin/bash
# 자바 공통모듈 원클릭 빌드
# 데이터베이스, 소스 복제, 깃 저장소, 푸시 웹훅, jar 서비스, 관리자 화면까지 만든다.

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
TMP_APACHE=""
GH_BODY=""
GH_HOOK_BODY=""
CURL_CFG=""
GIT_ASKPASS_FILE=""
MIRROR_FETCH_LOG=""
MIRROR_PUBLIC_URL=""
ENV_FILE=""
UNIT_FILE=""

cleanup() {
    if [ -n "${MIRROR_PUBLIC_URL:-}" ] && [ -d "${TEMPLATE_MIRROR:-}" ]; then
        as_root git --git-dir="$TEMPLATE_MIRROR" remote set-url origin "$MIRROR_PUBLIC_URL" || true
    fi
    rm -f "${MYSQL_CNF:-}" "${SQL_FILE:-}" "${TMP_NGINX:-}" "${TMP_APACHE:-}" "${GH_BODY:-}" "${GH_HOOK_BODY:-}" "${CURL_CFG:-}" "${GIT_ASKPASS_FILE:-}" "${MIRROR_FETCH_LOG:-}" "${ENV_FILE:-}" "${UNIT_FILE:-}"
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

port_busy() {
    ss -lntH "sport = :$1" | grep -q .
}

pick_port() {
    local port=$1
    while port_busy "$port"; do
        port=$((port + 1))
        if [ "$port" -gt 6999 ]; then
            echo "사용할 수 있는 포트가 없습니다." >&2
            exit 1
        fi
    done
    printf '%s' "$port"
}

for name in PROJECT_NAME FTP_USERNAME FTP_PASSWORD DB_USERNAME DB_PASSWORD DB_DATABASE \
    PROVISION_DB_ADMIN_USER PROVISION_DB_ADMIN_PASSWORD GITHUB_TOKEN GITHUB_ORG GITHUB_WEBHOOK_SECRET \
    TEMPLATE_MIRROR JAVA_TEMPLATE_SOURCE JAVA_TEMPLATE_DATABASE JAVA_ADM_JAR JAVA_USER_JAR \
    BASE_PATH DOMAIN_SUFFIX WEB_USER; do
    require_var "$name"
done

assert_identifier "프로젝트명" "$PROJECT_NAME"
assert_identifier "FTP 아이디" "$FTP_USERNAME"
assert_identifier "DB 아이디" "$DB_USERNAME"

if [ "$PROJECT_NAME" = "hk-java" ]; then
    echo "공통모듈 원본과 같은 이름은 사용할 수 없습니다." >&2
    exit 1
fi

if [ "$FTP_USERNAME" = "$PROJECT_NAME" ]; then
    echo "FTP 아이디는 프로젝트명과 달라야 합니다." >&2
    exit 1
fi

if ! [[ "$JAVA_TEMPLATE_DATABASE" =~ ^[A-Za-z0-9_]+$ ]]; then
    echo "공통모듈 데이터베이스 이름이 올바르지 않습니다." >&2
    exit 1
fi

case "$TEMPLATE_MIRROR" in
    /opt/templates/*.git) ;;
    *) echo "템플릿 미러 경로가 올바르지 않습니다." >&2; exit 1 ;;
esac

case "$JAVA_TEMPLATE_SOURCE" in
    /var/www/html/*) ;;
    *) echo "공통모듈 원본 경로가 올바르지 않습니다." >&2; exit 1 ;;
esac

case "$JAVA_ADM_JAR" in
    /opt/hk-java/adm/*.jar) ;;
    *) echo "관리자 jar 경로가 올바르지 않습니다." >&2; exit 1 ;;
esac

case "$JAVA_USER_JAR" in
    /opt/hk-java/user/*.jar) ;;
    *) echo "사용자 jar 경로가 올바르지 않습니다." >&2; exit 1 ;;
esac

PROVISION_NGINX_PORT="${PROVISION_NGINX_PORT:-800}"
PROVISION_URL_SCHEME="${PROVISION_URL_SCHEME:-https}"

PROJECT_DIR="${BASE_PATH}/${PROJECT_NAME}"
case "$PROJECT_DIR" in
    /var/www/html/*) ;;
    *) echo "프로젝트 경로가 올바르지 않습니다." >&2; exit 1 ;;
esac
APP_URL="${PROVISION_URL_SCHEME}://${PROJECT_NAME}.${DOMAIN_SUFFIX}"
ADMIN_URL="${PROVISION_URL_SCHEME}://${PROJECT_NAME}-adm.${DOMAIN_SUFFIX}"
ADM_ARTIFACT=$(basename "$JAVA_ADM_JAR")
USER_ARTIFACT=$(basename "$JAVA_USER_JAR")
export GIT_TERMINAL_PROMPT=0

step mysql
echo "데이터베이스 생성을 시작합니다."

if [ -e "$PROJECT_DIR" ]; then
    echo "프로젝트 디렉터리가 이미 있습니다: ${PROJECT_DIR}" >&2
    exit 1
fi

if id "$FTP_USERNAME" >/dev/null 2>&1; then
    echo "FTP 계정이 이미 있습니다: ${FTP_USERNAME}" >&2
    exit 1
fi

if [ ! -d "$JAVA_TEMPLATE_SOURCE/.git" ]; then
    echo "자바 공통모듈 원본이 없습니다." >&2
    exit 1
fi

if [ ! -f "$JAVA_ADM_JAR" ] || [ ! -f "$JAVA_USER_JAR" ]; then
    echo "공통모듈 jar가 없습니다." >&2
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
CREATE USER '{$user}'@'127.0.0.1' IDENTIFIED WITH mysql_native_password BY '{$pass}';
CREATE USER '{$user}'@'localhost' IDENTIFIED WITH mysql_native_password BY '{$pass}';
CREATE USER '{$user}'@'%' IDENTIFIED WITH mysql_native_password BY '{$pass}';
GRANT ALL PRIVILEGES ON `{$db}`.* TO '{$user}'@'127.0.0.1';
GRANT ALL PRIVILEGES ON `{$db}`.* TO '{$user}'@'localhost';
GRANT ALL PRIVILEGES ON `{$db}`.* TO '{$user}'@'%';
FLUSH PRIVILEGES;
SQL;
file_put_contents(getenv('SQL_FILE'), $sql);
PHP

if ! mysql --defaults-extra-file="$MYSQL_CNF" < "$SQL_FILE"; then
    echo "데이터베이스 사용자 생성에 실패했습니다. 계정이 이미 있거나 권한이 없습니다." >&2
    exit 1
fi

echo "공통모듈 테이블을 넣습니다."
if ! mysqldump --defaults-extra-file="$MYSQL_CNF" --single-transaction --skip-add-locks --no-data --skip-comments --default-character-set=utf8mb4 "$JAVA_TEMPLATE_DATABASE" \
    | mysql --defaults-extra-file="$MYSQL_CNF" --database="${DB_DATABASE}"; then
    echo "공통모듈 테이블을 만들지 못했습니다." >&2
    exit 1
fi

if ! mysqldump --defaults-extra-file="$MYSQL_CNF" --single-transaction --skip-add-locks --no-create-info --skip-comments --default-character-set=utf8mb4 \
    "$JAVA_TEMPLATE_DATABASE" CODE_MA CODE_DT AUTH_GROUP AUTH_INFO ADMIN_INFO BBS_MASTER \
    | mysql --defaults-extra-file="$MYSQL_CNF" --database="${DB_DATABASE}"; then
    echo "공통모듈 기본 데이터를 넣지 못했습니다." >&2
    exit 1
fi
echo "데이터베이스와 사용자를 만들었습니다."

step clone
echo "템플릿 복제를 시작합니다."

if [ ! -d "$TEMPLATE_MIRROR" ]; then
    echo "자바 공통모듈 미러를 만듭니다."
    as_root mkdir -p "$(dirname "$TEMPLATE_MIRROR")"
    as_root git -c "safe.directory=${JAVA_TEMPLATE_SOURCE}" clone --bare "$JAVA_TEMPLATE_SOURCE" "$TEMPLATE_MIRROR"
fi

MIRROR_PUBLIC_URL="https://github.com/${GITHUB_ORG}/hk-java.git"
echo "템플릿 미러를 갱신합니다."
MIRROR_FETCH_LOG=$(mktemp)
as_root git --git-dir="$TEMPLATE_MIRROR" remote set-url origin "https://x-access-token:${GITHUB_TOKEN}@${MIRROR_PUBLIC_URL#https://}"
if ! as_root git --git-dir="$TEMPLATE_MIRROR" fetch origin '+refs/heads/*:refs/heads/*' --prune >"$MIRROR_FETCH_LOG" 2>&1; then
    echo "템플릿 미러 갱신을 건너뜁니다. 서버에 있는 공통모듈을 사용합니다."
else
    echo "템플릿 미러를 갱신했습니다."
fi
as_root git --git-dir="$TEMPLATE_MIRROR" remote set-url origin "$MIRROR_PUBLIC_URL"
rm -f "$MIRROR_FETCH_LOG"
MIRROR_FETCH_LOG=""

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
GH_HOOK_BODY=$(mktemp)
CURL_CFG=$(mktemp)
GIT_ASKPASS_FILE=$(mktemp)
chmod 600 "$CURL_CFG" "$GIT_ASKPASS_FILE" "$GH_HOOK_BODY"
export CURL_CFG GIT_ASKPASS_FILE GH_HOOK_BODY
php <<'PHP'
<?php
$token = (string) getenv('GITHUB_TOKEN');
$quoted = "'".str_replace("'", "'\\''", $token)."'";
$header = 'header = "Authorization: Bearer '.str_replace(['\\', '"'], ['\\\\', '\\"'], $token).'"'."\n";
file_put_contents(getenv('CURL_CFG'), $header);
file_put_contents(getenv('GIT_ASKPASS_FILE'), "#!/bin/sh\nprintf 'username=x-access-token\\npassword=%s\\n' {$quoted}\n");
$hook = [
    'name' => 'web',
    'active' => true,
    'events' => ['push'],
    'config' => [
        'url' => 'https://homepagekorea.com/backoffice/github/webhook',
        'content_type' => 'json',
        'secret' => (string) getenv('GITHUB_WEBHOOK_SECRET'),
        'insecure_ssl' => '0',
    ],
];
file_put_contents(getenv('GH_HOOK_BODY'), json_encode($hook, JSON_UNESCAPED_SLASHES));
PHP
chmod 700 "$GIT_ASKPASS_FILE"

HTTP_CODE=$(curl -sS --config "$CURL_CFG" -o "$GH_BODY" -w "%{http_code}" -X POST \
    -H "Accept: application/vnd.github+json" \
    -H "X-GitHub-Api-Version: 2022-11-28" \
    "https://api.github.com/orgs/${GITHUB_ORG}/repos" \
    -d "{\"name\":\"${PROJECT_NAME}\",\"private\":true}")

REPO_OWNER="$GITHUB_ORG"
if [ "$HTTP_CODE" != "201" ] && [ "$HTTP_CODE" != "422" ]; then
    echo "조직 저장소를 만들지 못했습니다. HTTP ${HTTP_CODE}. 계정 저장소로 생성합니다."
    HTTP_CODE=$(curl -sS --config "$CURL_CFG" -o "$GH_BODY" -w "%{http_code}" -X POST \
        -H "Accept: application/vnd.github+json" \
        -H "X-GitHub-Api-Version: 2022-11-28" \
        "https://api.github.com/user/repos" \
        -d "{\"name\":\"${PROJECT_NAME}\",\"private\":true}")
    REPO_OWNER=$(curl -sS --config "$CURL_CFG" -H "Accept: application/vnd.github+json" https://api.github.com/user | php -r 'echo json_decode(stream_get_contents(STDIN), true)["login"] ?? "";')
fi

if [ "$HTTP_CODE" != "201" ] && [ "$HTTP_CODE" != "422" ]; then
    echo "깃허브 저장소 생성에 실패했습니다. HTTP ${HTTP_CODE}" >&2
    exit 1
fi

cd "$PROJECT_DIR"
git remote remove origin 2>/dev/null || true
git remote add origin "https://github.com/${REPO_OWNER}/${PROJECT_NAME}.git"

if [ "$HTTP_CODE" = "422" ]; then
    EXISTING_CODE=$(curl -sS --config "$CURL_CFG" -o /dev/null -w "%{http_code}" \
        -H "Accept: application/vnd.github+json" \
        -H "X-GitHub-Api-Version: 2022-11-28" \
        "https://api.github.com/repos/${REPO_OWNER}/${PROJECT_NAME}")
    if [ "$EXISTING_CODE" != "200" ]; then
        echo "깃허브 저장소를 만들지 못했습니다. HTTP ${HTTP_CODE}" >&2
        exit 1
    fi
    echo "이미 있는 깃 저장소를 사용합니다."
else
    if [ -d .github/workflows ]; then
        echo "액션 워크플로는 토큰으로 푸시할 수 없어 저장소에서 제외합니다."
        SOURCE_BRANCH=$(git rev-parse --abbrev-ref HEAD)
        git checkout --orphan provision-export
        git reset -q
        git checkout "$SOURCE_BRANCH" -- .
        rm -rf .github/workflows
        rmdir .github 2>/dev/null || true
        git add -A
        git -c user.name='provision' -c user.email='provision@hk-test.co.kr' commit -q -m "템플릿 초기 소스"
        git branch -D "$SOURCE_BRANCH"
        git branch -m "$SOURCE_BRANCH"
    fi
    PUSH_BRANCH=$(git rev-parse --abbrev-ref HEAD)
    GIT_ASKPASS="$GIT_ASKPASS_FILE" git -c "credential.helper=!${GIT_ASKPASS_FILE}" push -u origin "$PUSH_BRANCH"
    git remote set-url origin "https://github.com/${REPO_OWNER}/${PROJECT_NAME}.git"
    echo "깃 저장소를 만들었습니다."
fi

HOOK_CODE=$(curl -sS --config "$CURL_CFG" -o "$GH_BODY" -w "%{http_code}" -X POST \
    -H "Accept: application/vnd.github+json" \
    -H "Content-Type: application/json" \
    -H "X-GitHub-Api-Version: 2022-11-28" \
    "https://api.github.com/repos/${REPO_OWNER}/${PROJECT_NAME}/hooks" \
    --data-binary @"$GH_HOOK_BODY")
rm -f "$GH_HOOK_BODY"
GH_HOOK_BODY=""
if [ "$HOOK_CODE" != "201" ] && [ "$HOOK_CODE" != "422" ]; then
    echo "자동 배포 웹훅 연결에 실패했습니다. HTTP ${HOOK_CODE}" >&2
    exit 1
fi
echo "자동 배포 웹훅을 연결했습니다."
echo "[[RESULT_GIT]] https://github.com/${REPO_OWNER}/${PROJECT_NAME}"

step permission
echo "권한과 FTP 계정을 설정합니다."

if [ ! -f "$PROJECT_DIR/www/index.html" ] && [ -d "$JAVA_TEMPLATE_SOURCE/www" ]; then
    rm -rf "$PROJECT_DIR/www"
    as_root cp -a "$JAVA_TEMPLATE_SOURCE/www" "$PROJECT_DIR/www"
fi
if [ ! -f "$PROJECT_DIR/www-adm/index.html" ] && [ -d "$JAVA_TEMPLATE_SOURCE/www-adm" ]; then
    rm -rf "$PROJECT_DIR/www-adm"
    as_root cp -a "$JAVA_TEMPLATE_SOURCE/www-adm" "$PROJECT_DIR/www-adm"
fi

as_root useradd -d "$PROJECT_DIR" -s /bin/bash -G "$WEB_USER" "$FTP_USERNAME"
printf '%s:%s\n' "$FTP_USERNAME" "$FTP_PASSWORD" | as_root chpasswd
if [ -x /usr/local/sbin/hk-sftp-deny.sh ]; then
    as_root /bin/bash /usr/local/sbin/hk-sftp-deny.sh deny "$FTP_USERNAME"
    echo "FTP 계정의 SSH/SFTP 로그인을 막았습니다."
fi

as_root chown -R "${FTP_USERNAME}:${WEB_USER}" "$PROJECT_DIR"
as_root find "$PROJECT_DIR" -type d -exec chmod 2775 {} \;
as_root find "$PROJECT_DIR" -type f -exec chmod 664 {} \;
echo "디렉터리 권한과 FTP 계정을 설정했습니다."

step java
echo "자바 서비스를 준비합니다."

ADM_PORT=$(pick_port 6100)
USER_PORT=$(pick_port $((ADM_PORT + 1)))
echo "관리자 포트 ${ADM_PORT}, 사용자 포트 ${USER_PORT}"

as_root mkdir -p "/opt/${PROJECT_NAME}/adm" "/opt/${PROJECT_NAME}/user" "$PROJECT_DIR/uploads" /etc/hk-java
as_root cp "$JAVA_ADM_JAR" "/opt/${PROJECT_NAME}/adm/${ADM_ARTIFACT}"
as_root cp "$JAVA_USER_JAR" "/opt/${PROJECT_NAME}/user/${USER_ARTIFACT}"
as_root chown -R "${FTP_USERNAME}:${WEB_USER}" "/opt/${PROJECT_NAME}" "$PROJECT_DIR/uploads"

write_runtime() {
    local port="$1"
    local work="$2"
    local jar="$3"
    local unit="$4"
    ENV_FILE=$(mktemp)
    UNIT_FILE=$(mktemp)
    chmod 600 "$ENV_FILE"
    export ENV_FILE UNIT_FILE HK_PORT="$port" HK_WORK="$work" HK_JAR="$jar" HK_UNIT="$unit" HK_USER="$FTP_USERNAME"
    php <<'PHP'
<?php
$quote = static function (string $value): string {
    return '"'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"';
};
$lines = [
    'SERVER_PORT='.$quote((string) getenv('HK_PORT')),
    'SPRING_DATASOURCE_URL='.$quote('jdbc:p6spy:mariadb://127.0.0.1:3306/'.getenv('DB_DATABASE')),
    'SPRING_DATASOURCE_USERNAME='.$quote((string) getenv('DB_USERNAME')),
    'SPRING_DATASOURCE_PASSWORD='.$quote((string) getenv('DB_PASSWORD')),
    'FILE_UPLOAD_PATH='.$quote((string) getenv('BASE_PATH').'/'.getenv('PROJECT_NAME').'/uploads'),
];
file_put_contents(getenv('ENV_FILE'), implode("\n", $lines)."\n");
$unit = "[Unit]\n"
    ."Description=".getenv('HK_UNIT')."\n"
    ."After=network.target mysql.service\n\n"
    ."[Service]\n"
    ."User=".getenv('HK_USER')."\n"
    ."WorkingDirectory=".getenv('HK_WORK')."\n"
    ."EnvironmentFile=/etc/hk-java/".getenv('HK_UNIT').".env\n"
    ."ExecStart=/usr/bin/java -jar ".getenv('HK_JAR')."\n"
    ."SuccessExitStatus=143\n"
    ."Restart=on-failure\n"
    ."RestartSec=5\n\n"
    ."[Install]\n"
    ."WantedBy=multi-user.target\n";
file_put_contents(getenv('UNIT_FILE'), $unit);
PHP
    as_root cp "$ENV_FILE" "/etc/hk-java/${unit}.env"
    as_root chmod 600 "/etc/hk-java/${unit}.env"
    as_root cp "$UNIT_FILE" "/etc/systemd/system/${unit}.service"
    rm -f "$ENV_FILE" "$UNIT_FILE"
    ENV_FILE=""
    UNIT_FILE=""
}

write_runtime "$ADM_PORT" "/opt/${PROJECT_NAME}/adm" "/opt/${PROJECT_NAME}/adm/${ADM_ARTIFACT}" "${PROJECT_NAME}-adm"
write_runtime "$USER_PORT" "/opt/${PROJECT_NAME}/user" "/opt/${PROJECT_NAME}/user/${USER_ARTIFACT}" "${PROJECT_NAME}-user"

if [ ! -x /usr/local/sbin/hk-java-activate.sh ]; then
    echo "자바 기동 스크립트가 없습니다." >&2
    exit 1
fi
as_root /bin/bash /usr/local/sbin/hk-java-activate.sh start "$PROJECT_NAME"
echo "자바 서비스를 켰습니다."

step nginx
echo "웹서버 설정을 반영합니다."

if [ -d /etc/nginx/sites-available ]; then
    NGINX_AVAILABLE=1
else
    NGINX_AVAILABLE=0
fi

install_site() {
    local site_name="$1"
    local server_name="$2"
    local root_dir="$3"
    local api_port="$4"
    local nginx_file nginx_enabled

    if [ "$NGINX_AVAILABLE" -eq 1 ]; then
        nginx_file="/etc/nginx/sites-available/${site_name}.conf"
        nginx_enabled="/etc/nginx/sites-enabled/${site_name}.conf"
    else
        nginx_file="/etc/nginx/conf.d/${site_name}.conf"
        nginx_enabled=""
    fi

    TMP_NGINX=$(mktemp)
    cat > "$TMP_NGINX" <<EOF
server {
    listen ${PROVISION_NGINX_PORT};
    listen [::]:${PROVISION_NGINX_PORT};
    server_name ${server_name};
    client_max_body_size 50m;
    root ${root_dir};
    index index.html;
    charset utf-8;

    location /api/ {
        proxy_pass http://127.0.0.1:${api_port};
        proxy_http_version 1.1;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
    }

    location /uploads/ {
        proxy_pass http://127.0.0.1:${api_port};
        proxy_http_version 1.1;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
    }

    location / {
        try_files \$uri \$uri/ /index.html;
    }

    location ~ /\\.(?!well-known).* {
        deny all;
    }
}
EOF
    as_root cp "$TMP_NGINX" "$nginx_file"
    rm -f "$TMP_NGINX"
    TMP_NGINX=""
    if [ -n "$nginx_enabled" ]; then
        as_root ln -sfn "$nginx_file" "$nginx_enabled"
    fi

    if [ -d /etc/apache2/sites-available ]; then
        local apache_file="/etc/apache2/sites-available/${site_name}.conf"
        local apache_enabled="/etc/apache2/sites-enabled/${site_name}.conf"
        TMP_APACHE=$(mktemp)
        cat > "$TMP_APACHE" <<EOF
<VirtualHost *:80>
    ServerName ${server_name}
    ServerAlias ${server_name}
    ProxyPreserveHost On
    ProxyPass / http://127.0.0.1:${PROVISION_NGINX_PORT}/
    ProxyPassReverse / http://127.0.0.1:${PROVISION_NGINX_PORT}/
</VirtualHost>
EOF
        as_root cp "$TMP_APACHE" "$apache_file"
        rm -f "$TMP_APACHE"
        TMP_APACHE=""
        as_root ln -sfn "$apache_file" "$apache_enabled"
    fi
}

install_site "$PROJECT_NAME" "${PROJECT_NAME}.${DOMAIN_SUFFIX}" "${PROJECT_DIR}/www" "$USER_PORT"
install_site "${PROJECT_NAME}-adm" "${PROJECT_NAME}-adm.${DOMAIN_SUFFIX}" "${PROJECT_DIR}/www-adm" "$ADM_PORT"
as_root nginx -t
as_root systemctl reload nginx
if [ -d /etc/apache2/sites-available ]; then
    as_root apache2ctl graceful
fi

echo "[[RESULT_URL]] ${APP_URL}"
echo "[[RESULT_PUB_URL]] ${ADMIN_URL}"
echo "웹서버 설정을 반영했습니다."
