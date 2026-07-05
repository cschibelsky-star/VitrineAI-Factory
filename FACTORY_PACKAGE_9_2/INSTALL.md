# FACTORY PACKAGE 9.2 — Enterprise Theme

1. Upload do ZIP na raiz do projeto.
2. unzip -o FACTORY_PACKAGE_9_2.zip -d /tmp/factory-package-9-2
3. cp -Rf /tmp/factory-package-9-2/FACTORY_PACKAGE_9_2/* .
4. php artisan optimize:clear
5. git add . && git commit -m "FACTORY PACKAGE 9.2 Enterprise Theme" && git push origin hostgator-baseline
