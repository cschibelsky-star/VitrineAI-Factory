# FACTORY BLUEPRINT 10.0 - INVENTARIO REAL

## Branch atual
hostgator-baseline

## Último commit
17227e9 FACTORY X.7 Enterprise Integration Foundation

## Models
app/Models/FactoryEvent.php
app/Models/FactoryProject.php
app/Models/FactoryProvisioningLog.php
app/Models/FactoryTemplate.php
app/Models/Followup.php
app/Models/Lead.php
app/Models/Proposta.php
app/Models/User.php

## Factory Services
app/Factory/Console/Commands/FactoryMakeRealProjectCommand.php
app/Factory/Engine/CloneRepository.php
app/Factory/Engine/ConfigurePermissions.php
app/Factory/Engine/CreateAdminUser.php
app/Factory/Engine/CreateEnvironment.php
app/Factory/Engine/FinalizeInstallation.php
app/Factory/Engine/InstallComposer.php
app/Factory/Engine/PublishAssets.php
app/Factory/Engine/RunMigrations.php
app/Factory/Providers/FactoryServiceProvider.php
app/Factory/RealBuilder/Services/BlueprintAutoFactory.php
app/Factory/RealBuilder/Services/RealBuilderNameService.php
app/Factory/RealBuilder/Services/RealCodeGenerator.php
app/Factory/RealBuilder/Services/RealProjectScaffolder.php
app/Factory/Services/BackupService.php
app/Factory/Services/DeploymentService.php
app/Factory/Services/EnterpriseEventBus.php
app/Factory/Services/FactoryBrainService.php
app/Factory/Services/FactoryIntelligenceService.php
app/Factory/Services/HealthCheckService.php
app/Factory/Services/ProvisioningService.php
app/Factory/Services/RollbackService.php

## Jobs
app/Jobs/ProvisionProjectJob.php

## Filament Pages
app/Filament/Pages/CpanelAssistido.php
app/Filament/Pages/DeployCenter.php
app/Filament/Pages/DevOpsCenter.php
app/Filament/Pages/EnterpriseDashboard.php
app/Filament/Pages/EnterpriseWorkspace.php
app/Filament/Pages/EventsCenter.php
app/Filament/Pages/ExecutarFila.php
app/Filament/Pages/FactoryBrain.php
app/Filament/Pages/FactoryMarketplace.php
app/Filament/Pages/OperationsCenter.php
app/Filament/Pages/PipelineVisual.php
app/Filament/Pages/ProjetosEnterprise.php
app/Filament/Pages/ProjetoWorkspace.php
app/Filament/Pages/ProvisionadorFactory.php

## Filament Resources
app/Filament/Resources/FactoryProjectResource/Pages/CreateFactoryProject.php
app/Filament/Resources/FactoryProjectResource/Pages/EditFactoryProject.php
app/Filament/Resources/FactoryProjectResource/Pages/ListFactoryProjects.php
app/Filament/Resources/FactoryProjectResource.php
app/Filament/Resources/FactoryProjectResource/RelationManagers/ProvisioningLogsRelationManager.php
app/Filament/Resources/FactoryTemplateResource/Pages/CreateFactoryTemplate.php
app/Filament/Resources/FactoryTemplateResource/Pages/EditFactoryTemplate.php
app/Filament/Resources/FactoryTemplateResource/Pages/ListFactoryTemplates.php
app/Filament/Resources/FactoryTemplateResource.php
app/Filament/Resources/FollowupResource/Pages/CreateFollowup.php
app/Filament/Resources/FollowupResource/Pages/EditFollowup.php
app/Filament/Resources/FollowupResource/Pages/ListFollowups.php
app/Filament/Resources/FollowupResource/Pages/ViewFollowup.php
app/Filament/Resources/FollowupResource.php
app/Filament/Resources/LeadResource/Pages/CreateLead.php
app/Filament/Resources/LeadResource/Pages/EditLead.php
app/Filament/Resources/LeadResource/Pages/ListLeads.php
app/Filament/Resources/LeadResource/Pages/ViewLead.php
app/Filament/Resources/LeadResource.php
app/Filament/Resources/PropostaResource/Pages/CreateProposta.php
app/Filament/Resources/PropostaResource/Pages/EditProposta.php
app/Filament/Resources/PropostaResource/Pages/ListPropostas.php
app/Filament/Resources/PropostaResource/Pages/ViewProposta.php
app/Filament/Resources/PropostaResource.php

## Views Filament
resources/views/filament/pages/cpanel-assistido.blade.php
resources/views/filament/pages/deploy-center.blade.php
resources/views/filament/pages/devops-center.blade.php
resources/views/filament/pages/enterprise-dashboard.blade.php
resources/views/filament/pages/enterprise-workspace.blade.php
resources/views/filament/pages/events-center.blade.php
resources/views/filament/pages/executar-fila.blade.php
resources/views/filament/pages/factory-brain.blade.php
resources/views/filament/pages/factory-marketplace.blade.php
resources/views/filament/pages/operations-center.blade.php
resources/views/filament/pages/pipeline-visual.blade.php
resources/views/filament/pages/projetos-enterprise.blade.php
resources/views/filament/pages/projeto-workspace.blade.php
resources/views/filament/pages/provisionador-factory.blade.php

## Migrations
database/migrations/2026_07_01_151602_create_leads_table.php
database/migrations/2026_07_01_151603_create_propostas_table.php
database/migrations/2026_07_01_151604_create_followups_table.php
database/migrations/2026_07_03_233720_create_users_table.php
database/migrations/2026_07_03_234446_create_users_table.php.disabled
database/migrations/2026_07_04_091659_create_factory_projects_table.php
database/migrations/2026_07_04_092802_create_factory_templates_table.php
database/migrations/2026_07_04_100016_add_provisioning_fields_to_factory_projects_table.php
database/migrations/2026_07_04_115628_create_factory_provisioning_logs_table.php
database/migrations/2026_07_04_190831_add_health_fields_to_factory_projects_table.php
database/migrations/2026_07_04_195313_add_cpanel_fields_to_factory_projects_table.php
database/migrations/2026_07_04_200000_add_enterprise_fields_to_factory_templates_table.php
database/migrations/2026_07_04_200001_add_enterprise_fields_to_factory_projects_table.php
database/migrations/2026_07_05_000000_create_factory_events_table.php

## Seeders
database/seeders/DatabaseSeeder.php
database/seeders/EnhancedFactoryTemplateSeeder.php
database/seeders/FactoryTemplateSeeder.php
database/seeders/FollowupSeeder.php
database/seeders/LeadSeeder.php
database/seeders/PropostaSeeder.php

## Routes admin
  GET|HEAD  admin ................................................................... filament.admin.pages.dashboard › Filament\Pages › Dashboard
  GET|HEAD  admin/cpanel-assistido ................................... filament.admin.pages.cpanel-assistido › App\Filament\Pages\CpanelAssistido
  GET|HEAD  admin/deploy-center ............................................ filament.admin.pages.deploy-center › App\Filament\Pages\DeployCenter
  GET|HEAD  admin/dev-ops-center .......................................... filament.admin.pages.dev-ops-center › App\Filament\Pages\DevOpsCenter
  GET|HEAD  admin/enterprise-dashboard ....................... filament.admin.pages.enterprise-dashboard › App\Filament\Pages\EnterpriseDashboard
  GET|HEAD  admin/enterprise-workspace ....................... filament.admin.pages.enterprise-workspace › App\Filament\Pages\EnterpriseWorkspace
  GET|HEAD  admin/events-center ............................................ filament.admin.pages.events-center › App\Filament\Pages\EventsCenter
  GET|HEAD  admin/executar-fila ............................................ filament.admin.pages.executar-fila › App\Filament\Pages\ExecutarFila
  GET|HEAD  admin/factory-brain ............................................ filament.admin.pages.factory-brain › App\Filament\Pages\FactoryBrain
  GET|HEAD  admin/factory-marketplace .......................... filament.admin.pages.factory-marketplace › App\Filament\Pages\FactoryMarketplace
  GET|HEAD  admin/factory-projects filament.admin.resources.factory-projects.index › App\Filament\Resources\FactoryProjectResource\Pages\ListFac…
  GET|HEAD  admin/factory-projects/create filament.admin.resources.factory-projects.create › App\Filament\Resources\FactoryProjectResource\Pages…
  GET|HEAD  admin/factory-projects/{record}/edit filament.admin.resources.factory-projects.edit › App\Filament\Resources\FactoryProjectResource\…
  GET|HEAD  admin/factory-templates filament.admin.resources.factory-templates.index › App\Filament\Resources\FactoryTemplateResource\Pages\List…
  GET|HEAD  admin/factory-templates/create filament.admin.resources.factory-templates.create › App\Filament\Resources\FactoryTemplateResource\Pa…
  GET|HEAD  admin/factory-templates/{record}/edit filament.admin.resources.factory-templates.edit › App\Filament\Resources\FactoryTemplateResour…
  GET|HEAD  admin/followups .............. filament.admin.resources.followups.index › App\Filament\Resources\FollowupResource\Pages\ListFollowups
  GET|HEAD  admin/followups/create ..... filament.admin.resources.followups.create › App\Filament\Resources\FollowupResource\Pages\CreateFollowup
  GET|HEAD  admin/followups/{record} ....... filament.admin.resources.followups.view › App\Filament\Resources\FollowupResource\Pages\ViewFollowup
  GET|HEAD  admin/followups/{record}/edit .. filament.admin.resources.followups.edit › App\Filament\Resources\FollowupResource\Pages\EditFollowup
  GET|HEAD  admin/leads .............................. filament.admin.resources.leads.index › App\Filament\Resources\LeadResource\Pages\ListLeads
  GET|HEAD  admin/leads/create ..................... filament.admin.resources.leads.create › App\Filament\Resources\LeadResource\Pages\CreateLead
  GET|HEAD  admin/leads/{record} ....................... filament.admin.resources.leads.view › App\Filament\Resources\LeadResource\Pages\ViewLead
  GET|HEAD  admin/leads/{record}/edit .................. filament.admin.resources.leads.edit › App\Filament\Resources\LeadResource\Pages\EditLead
  GET|HEAD  admin/login ...................................................................... filament.admin.auth.login › Filament\Pages › Login
  POST      admin/logout .......................................................... filament.admin.auth.logout › Filament\Http › LogoutController
  GET|HEAD  admin/operations-center ................................ filament.admin.pages.operations-center › App\Filament\Pages\OperationsCenter
  GET|HEAD  admin/pipeline-visual ...................................... filament.admin.pages.pipeline-visual › App\Filament\Pages\PipelineVisual
  GET|HEAD  admin/projeto-workspace ................................ filament.admin.pages.projeto-workspace › App\Filament\Pages\ProjetoWorkspace
  GET|HEAD  admin/projetos-enterprise .......................... filament.admin.pages.projetos-enterprise › App\Filament\Pages\ProjetosEnterprise
  GET|HEAD  admin/propostas .............. filament.admin.resources.propostas.index › App\Filament\Resources\PropostaResource\Pages\ListPropostas
  GET|HEAD  admin/propostas/create ..... filament.admin.resources.propostas.create › App\Filament\Resources\PropostaResource\Pages\CreateProposta
  GET|HEAD  admin/propostas/{record} ....... filament.admin.resources.propostas.view › App\Filament\Resources\PropostaResource\Pages\ViewProposta
  GET|HEAD  admin/propostas/{record}/edit .. filament.admin.resources.propostas.edit › App\Filament\Resources\PropostaResource\Pages\EditProposta
  GET|HEAD  admin/provisionador-factory .................... filament.admin.pages.provisionador-factory › App\Filament\Pages\ProvisionadorFactory
