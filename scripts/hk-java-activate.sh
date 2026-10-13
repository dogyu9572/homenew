#!/bin/bash
# 자바 프로젝트 systemd 기동/정리. 루트로만 실행한다.
set -euo pipefail

mode=${1:-}
name=${2:-}

if [ "$#" -ne 2 ]; then
    echo "인자가 올바르지 않습니다." >&2
    exit 1
fi

if ! [[ "$name" =~ ^[a-z][a-z0-9-]{1,30}$ ]]; then
    echo "프로젝트명이 올바르지 않습니다." >&2
    exit 1
fi

if [ "$name" = "hk-java" ]; then
    echo "공통모듈 원본은 이 스크립트로 다루지 않습니다." >&2
    exit 1
fi

adm="${name}-adm"
user_unit="${name}-user"

case "$mode" in
    start)
        systemctl daemon-reload
        systemctl enable --now "$adm" "$user_unit"
        sleep 8
        if ! systemctl is-active --quiet "$adm" || ! systemctl is-active --quiet "$user_unit"; then
            journalctl -u "$adm" -u "$user_unit" -n 40 --no-pager | sed -E 's/(PASSWORD|password)=.*/\1=***/' >&2 || true
            echo "자바 서비스가 켜지지 않았습니다." >&2
            exit 1
        fi
        ;;
    stop)
        systemctl disable --now "$adm" "$user_unit" || true
        rm -f \
            "/etc/systemd/system/${adm}.service" \
            "/etc/systemd/system/${user_unit}.service" \
            "/etc/hk-java/${adm}.env" \
            "/etc/hk-java/${user_unit}.env" \
            "/etc/nginx/sites-available/${name}.conf" \
            "/etc/nginx/sites-enabled/${name}.conf" \
            "/etc/nginx/sites-available/${adm}.conf" \
            "/etc/nginx/sites-enabled/${adm}.conf" \
            "/etc/nginx/conf.d/${name}.conf" \
            "/etc/nginx/conf.d/${adm}.conf" \
            "/etc/apache2/sites-available/${name}.conf" \
            "/etc/apache2/sites-enabled/${name}.conf" \
            "/etc/apache2/sites-available/${adm}.conf" \
            "/etc/apache2/sites-enabled/${adm}.conf"
        rm -rf "/opt/${name}"
        systemctl daemon-reload
        nginx -t
        systemctl reload nginx
        if [ -d /etc/apache2/sites-available ]; then
            apache2ctl graceful || true
        fi
        ;;
    *)
        echo "동작이 올바르지 않습니다." >&2
        exit 1
        ;;
esac
