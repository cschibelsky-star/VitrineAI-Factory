<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Vitrine IA Pro · Factory')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(
                'panels::head.end',
                fn (): string => <<<'HTML'
<style>
:root{
    --factory-bg:#0d0904;
    --factory-panel:#171006;
    --factory-panel-2:#211708;
    --factory-border:rgba(251,191,36,.22);
    --factory-amber:#f59e0b;
    --factory-orange:#f97316;
    --factory-text:#fff7ed;
}
.fi-body{
    background:radial-gradient(circle at 15% 0,#3a2005 0,#160d04 34%,#080604 72%,#050403 100%)!important;
    color:var(--factory-text)!important;
    font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif!important;
}
.fi-body::after{
    content:"FACTORY · BUILD & RELEASE";
    position:fixed;top:10px;right:78px;z-index:10000;
    padding:.38rem .68rem;border:1px solid rgba(251,191,36,.42);border-radius:999px;
    background:rgba(146,64,14,.28);color:#fcd34d;font:900 .64rem/1 ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono",monospace;
    letter-spacing:.12em;pointer-events:none;backdrop-filter:blur(12px)
}
.fi-sidebar{background:linear-gradient(180deg,#130c04 0,#1d1205 100%)!important;border-right:1px solid var(--factory-border)!important;box-shadow:inset -2px 0 0 rgba(245,158,11,.11)}
.fi-sidebar-header{background:transparent!important;border-bottom:1px solid var(--factory-border)!important}
.fi-sidebar-header::before{content:"⚙";display:grid;place-items:center;width:32px;height:32px;margin-right:.55rem;border-radius:9px;background:linear-gradient(145deg,#f59e0b,#ea580c);color:#1c0d02;font-size:1rem;box-shadow:0 0 22px rgba(245,158,11,.3)}
.fi-sidebar-header span,.fi-sidebar-group-label,.fi-sidebar-item-label{color:#ffedd5!important}
.fi-sidebar-item.fi-active a,.fi-sidebar-item a:hover{background:linear-gradient(90deg,rgba(245,158,11,.30),rgba(249,115,22,.10))!important;color:#fff7ed!important}
.fi-topbar{background:rgba(19,12,4,.90)!important;border-bottom:1px solid var(--factory-border)!important;box-shadow:inset 0 -2px 0 rgba(245,158,11,.08);backdrop-filter:blur(18px)}
.fi-main,.fi-page{background:transparent!important}
.fi-header-heading,.fi-section-header-heading,.fi-ta-header-heading{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono",monospace!important;letter-spacing:-.025em}
.fi-section,.fi-ta-ctn,.fi-wi-widget{background:linear-gradient(180deg,rgba(33,23,8,.95),rgba(18,12,4,.96))!important;border-color:var(--factory-border)!important}
.fi-btn-color-primary{box-shadow:0 8px 24px rgba(245,158,11,.18)}
@media(max-width:760px){.fi-body::after{right:58px;font-size:.54rem;padding:.32rem .48rem;letter-spacing:.07em}}
</style>
HTML
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
