# FACTORY PACKAGE 9.3
Uniformização visual total das telas remanescentes.

Aplicação:
```bash
cd ~/factory.vitrineiapro.com.br
unzip -o FACTORY_PACKAGE_9_3.zip -d /tmp/factory-package-9-3
cp -Rf /tmp/factory-package-9-3/FACTORY_PACKAGE_9_3/* .
php artisan optimize:clear
git add .
git commit -m "FACTORY PACKAGE 9.3 Uniformizacao Visual Total"
git push origin hostgator-baseline
```
