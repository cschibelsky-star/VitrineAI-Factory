#!/usr/bin/env bash
set -e
cd ~/factory.vitrineiapro.com.br
php artisan migrate --force
php artisan db:seed --class=EnhancedFactoryTemplateSeeder
php artisan optimize:clear
git add .
git commit -m "FACTORY PACKAGE 8.0 Enterprise Factory Intelligence" || true
git push origin hostgator-baseline
