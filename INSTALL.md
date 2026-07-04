# Como aplicar

No servidor HostGator:

```bash
cd ~/factory.vitrineiapro.com.br
unzip -o FACTORY_PACKAGE_8_0.zip -d /tmp/factory-package-8
cp -R /tmp/factory-package-8/FACTORY_PACKAGE_8_0/* .
php artisan migrate --force
php artisan db:seed --class=EnhancedFactoryTemplateSeeder
php artisan optimize:clear
git add .
git commit -m "FACTORY PACKAGE 8.0 Enterprise Factory Intelligence"
git push origin hostgator-baseline
```

Depois testar:

- `/admin/dev-ops-center`
- `/admin/factory-marketplace`
- `/admin/pipeline-visual`
- `/admin/deploy-center`
- `/admin/provisionador-factory`
- `/admin/cpanel-assistido`
