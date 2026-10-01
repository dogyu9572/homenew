<?php

return [
    'db_admin_user' => env('PROVISION_DB_ADMIN_USER', 'provision'),
    'db_admin_password' => env('PROVISION_DB_ADMIN_PASSWORD'),
    'github_token' => env('GITHUB_TOKEN'),
    'github_org' => env('GITHUB_ORG', 'HOMEPAGE-KOREA'),
    'template_mirror' => env('PROVISION_TEMPLATE_MIRROR', '/opt/templates/hk-laravel.git'),
    'base_path' => env('PROVISION_BASE_PATH', '/home/ubuntu/www'),
    'domain_suffix' => env('PROVISION_DOMAIN_SUFFIX', 'hk-test.co.kr'),
    'web_user' => env('PROVISION_WEB_USER', 'www-data'),
    'php_socket' => env('PROVISION_PHP_SOCKET', 'unix:/run/php/php8.4-fpm.sock'),
    'ssl_fullchain' => env('PROVISION_SSL_FULLCHAIN'),
    'ssl_privkey' => env('PROVISION_SSL_PRIVKEY'),
    'certbot_email' => env('PROVISION_CERTBOT_EMAIL', 'admin@homepagekorea.com'),
    'script' => base_path('scripts/provision-laravel.sh'),
];
