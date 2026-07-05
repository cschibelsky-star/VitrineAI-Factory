# FACTORY PACKAGE 9.0 — Enterprise Experience

Aplicar:

```bash
cd ~/factory.vitrineiapro.com.br
unzip -o FACTORY_PACKAGE_9_0.zip -d /tmp/factory-package-9
cp -Rf /tmp/factory-package-9/FACTORY_PACKAGE_9_0/* .
php artisan optimize:clear
git add .
git commit -m "FACTORY PACKAGE 9.0 Enterprise Experience"
git push origin hostgator-baseline
```

Testar:
- /admin/enterprise-dashboard
- /admin/dev-ops-center
