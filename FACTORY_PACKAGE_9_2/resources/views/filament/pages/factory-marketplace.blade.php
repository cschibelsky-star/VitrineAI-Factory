<link rel="stylesheet" href="/css/factory-enterprise.css">
<x-filament-panels::page>
    <div class="vf-shell space-y-6">
        <section class="vf-hero">
            <div class="vf-eyebrow">Marketplace</div>
            <div class="vf-title">Catálogo Enterprise de Templates</div>
            <div class="vf-subtitle">Produtos instaláveis da Vitrine AI Pro prontos para provisionamento pela Factory.</div>
        </section>
        <div class="vf-grid-3">
            @foreach ($this->templates as $template)
                <div class="vf-product-card">
                    <div class="vf-product-top">
                        <div style="font-size:12px;font-weight:800;text-transform:uppercase;opacity:.85">{{ $template->category ?? $template->product_type }}</div>
                        <div style="font-size:20px;font-weight:900;margin-top:6px">{{ $template->name }}</div>
                    </div>
                    <div class="vf-product-body">
                        <div class="vf-pill green">{{ $template->status ?? 'active' }}</div>
                        <div style="margin-top:12px;color:#334155;line-height:1.7">
                            <strong>Produto:</strong> {{ $template->product_type }}<br>
                            <strong>Versão:</strong> {{ $template->version ?? '1.0.0' }}<br>
                            <strong>Branch:</strong> {{ $template->default_branch }}<br>
                            <strong>Banco:</strong> {{ $template->database_type ?? 'sqlite' }}<br>
                            <strong>Repo:</strong> {{ $template->base_repository }}
                        </div>
                        <div class="vf-actions">
                            <a class="vf-action primary" href="/admin/provisionador-factory">Instalar</a>
                            <span class="vf-action">Changelog</span>
                            <span class="vf-action">Docs</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
