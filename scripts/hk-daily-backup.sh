#!/bin/bash
# 사이트 일일 백업
#
# 대상 목록: /etc/hk-backup/sites.list
# 형식: 이름|사이트경로|데이터베이스
# 데이터베이스가 없으면 하이픈(-)을 적는다.
# 원클릭으로 사이트를 추가할 때는 이 목록에 한 줄만 넣으면 다음 실행부터 백업된다.
#
# 결과: /backup/이름/YYYY-MM-DD/
# 사이트마다 최근 5일만 남기고 그보다 오래된 날짜 폴더는 삭제한다.

set -euo pipefail

export TZ=Asia/Seoul

LIST=/etc/hk-backup/sites.list
ROOT=/backup
KEEP_DAYS=5
LOG=/var/log/hk-daily-backup.log
LOCK=/var/lock/hk-daily-backup.lock

exec 9>"$LOCK"
if ! flock -n 9; then
    echo "이미 백업이 실행 중입니다." >&2
    exit 0
fi

log() {
    printf '%s %s\n' "$(date '+%F %T')" "$*" >> "$LOG"
}

read_env() {
    local key="$1"
    local file="$2"
    local line=""
    line=$(grep -E "^${key}=" "$file" | tail -1 | cut -d= -f2- || true)
    line=${line%$'\r'}
    line=${line#\"}
    line=${line%\"}
    line=${line#\'}
    line=${line%\'}
    printf '%s' "$line"
}

backup_database() {
    local site_dir="$1"
    local database="$2"
    local output="$3"
    local env_file="${site_dir}/.env"
    local cnf user host port status=0

    if [ ! -f "$env_file" ]; then
        echo "데이터베이스 접속 파일을 찾을 수 없습니다." >&2
        return 1
    fi

    user=$(read_env DB_USERNAME "$env_file")
    host=$(read_env DB_HOST "$env_file")
    port=$(read_env DB_PORT "$env_file")
    if [ -z "$user" ] || [ -z "$host" ]; then
        echo "데이터베이스 접속 정보가 없습니다." >&2
        return 1
    fi
    if [ -z "$port" ]; then
        port=3306
    fi

    cnf=$(mktemp)
    chmod 600 "$cnf"
    {
        printf '[client]\nuser=%s\nhost=%s\nport=%s\npassword=' "$user" "$host" "$port"
        read_env DB_PASSWORD "$env_file"
        printf '\n'
    } > "$cnf"

    set +e
    mysqldump --defaults-extra-file="$cnf" --single-transaction --quick --no-tablespaces \
        --routines --triggers --default-character-set=utf8mb4 --databases "$database" \
        | gzip -c > "$output"
    status=${PIPESTATUS[0]}
    set -e
    rm -f "$cnf"
    if [ "$status" -ne 0 ]; then
        echo "데이터베이스 백업에 실패했습니다." >&2
        return 1
    fi
    gzip -t "$output"
}

backup_site() {
    local name="$1"
    local site_dir="$2"
    local database="$3"
    local today work parent parent_dir base vhost

    if ! [[ "$name" =~ ^[a-z][a-z0-9-]{1,30}$ ]]; then
        log "${name}: 사이트 이름 형식이 올바르지 않아 건너뜁니다."
        return 1
    fi
    if [ ! -d "$site_dir" ]; then
        log "${name}: 사이트 폴더가 없어 건너뜁니다."
        return 1
    fi

    today=$(date +%F)
    parent="${ROOT}/${name}"
    work="${parent}/.partial-${today}"
    mkdir -p "$parent"
    rm -rf "$work"
    mkdir -p "$work"

    parent_dir=$(dirname "$site_dir")
    base=$(basename "$site_dir")
    if ! tar -C "$parent_dir" -czf "${work}/files.tar.gz" \
        --exclude="${base}/storage/framework/cache" \
        --exclude="${base}/storage/framework/sessions" \
        --exclude="${base}/storage/framework/views" \
        --exclude="${base}/storage/logs" \
        "$base"; then
        rm -rf "$work"
        log "${name}: 파일 백업에 실패했습니다."
        return 1
    fi

    if [ "$database" != "-" ]; then
        if ! backup_database "$site_dir" "$database" "${work}/database.sql.gz"; then
            rm -rf "$work"
            log "${name}: 데이터베이스 백업에 실패했습니다."
            return 1
        fi
    fi

    for vhost in \
        "/etc/apache2/sites-available/${name}.conf" \
        "/etc/nginx/sites-available/${name}.conf" \
        "/etc/nginx/conf.d/${name}.conf"
    do
        if [ -f "$vhost" ]; then
            cp "$vhost" "${work}/web.conf"
            break
        fi
    done

    cat > "${work}/README.txt" <<EOF
사이트: ${name}
날짜: ${today}
사이트 폴더: ${site_dir}
데이터베이스: ${database}
파일: files.tar.gz
데이터베이스 파일: database.sql.gz
EOF

    rm -rf "${parent}/${today}"
    mv "$work" "${parent}/${today}"
    chmod 700 "${parent}/${today}"
    chmod 600 "${parent}/${today}"/*
    prune_site "$parent" "$today"
    log "${name}: ${today} 백업을 저장했습니다."
}

prune_site() {
    local parent="$1"
    local today="$2"
    local cutoff dir name
    cutoff=$(date -d "${today} -$((KEEP_DAYS - 1)) days" +%F)
    for dir in "$parent"/*; do
        [ -d "$dir" ] || continue
        name=$(basename "$dir")
        if [[ "$name" =~ ^[0-9]{4}-[0-9]{2}-[0-9]{2}$ ]] && [[ "$name" < "$cutoff" ]]; then
            rm -rf "$dir"
            log "$(basename "$parent"): ${name} 백업을 삭제했습니다."
        fi
    done
}

if [ ! -f "$LIST" ]; then
    log "백업 목록이 없습니다."
    exit 1
fi

mkdir -p "$ROOT"
chmod 755 "$ROOT"
if [ ! -f "${ROOT}/README.txt" ]; then
    cat > "${ROOT}/README.txt" <<EOF
사이트 백업은 사이트 이름 아래 날짜 폴더에 있습니다.
매일 1회 백업하고, 사이트마다 최근 5일만 보관합니다.
대상을 추가하려면 /etc/hk-backup/sites.list 에 한 줄을 넣습니다.
형식: 이름|사이트경로|데이터베이스
EOF
fi

failed=0
while IFS= read -r line || [ -n "$line" ]; do
    line=${line%%#*}
    line=$(printf '%s' "$line" | tr -d '[:space:]')
    [ -n "$line" ] || continue
    IFS='|' read -r name site_dir database <<< "$line"
    if [ -z "${name:-}" ] || [ -z "${site_dir:-}" ] || [ -z "${database:-}" ]; then
        log "목록 형식이 올바르지 않습니다."
        failed=1
        continue
    fi
    if ! backup_site "$name" "$site_dir" "$database"; then
        failed=1
    fi
done < "$LIST"

exit "$failed"
