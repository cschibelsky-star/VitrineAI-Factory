# FACTORY_PACKAGE_X_2

Adiciona a tela Projetos Enterprise em formato de cards operacionais, reduzindo dependência visual do CRUD padrão do Filament.

Aplicação:

```bash
cd ~/factory.vitrineiapro.com.br
unzip -o FACTORY_PACKAGE_X_2.zip -d /tmp/factory-x-2
cp -Rf /tmp/factory-x-2/FACTORY_PACKAGE_X_2/* .
php artisan optimize:clear
git add .
git commit -m "FACTORY X.2 Projetos Enterprise Workspace"
git push origin hostgator-baseline
```

Teste:

/admin/projetos-enterprise
