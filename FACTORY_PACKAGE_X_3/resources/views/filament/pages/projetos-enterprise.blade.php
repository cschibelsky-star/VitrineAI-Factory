<x-filament-panels::page>
    @php
        $stats = $this->stats;
        $statusColor = function ($status) {
            return match ($status) {
                'completed', 'active', 'provisioned' => 'background:#dcfce7;color:#166534;',
                'failed' => 'background:#fee2e2;color:#991b1b;',
                'running' => 'background:#fef3c7;color:#92400e;',
                default => 'background:#e5e7eb;color:#374151;',
            };
        };
    @endphp

    <div style="display:grid;gap:24px;">
        <section style="border-radius:28px;padding:32px;background:linear-gradient(135deg,#020617,#0f172a 55%,#1d4ed8);color:white;box-shadow:0 24px 60px rgba(15,23,42,.30);">
            <div style="display:flex;justify-content:space-between;gap:20px;align-items:flex-start;flex-wrap:wrap;">
                <div>
                    <div style="font-size:12px;letter-spacing:.22em;text-transform:uppercase;color:#93c5fd;font-weight:800;">VitrineAI Factory</div>
                    <h1 style="font-size:34px;line-height:1.1;margin:10px 0 8px;font-weight:900;">Projetos Enterprise</h1>
                    <p style="color:#cbd5e1;max-width:720px;font-size:15px;">Workspace operacional dos ambientes provisionados, com status, domínio, deploy, health check, GitHub e ações rápidas por projeto.</p>
                </div>
                <div style="padding:12px 16px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);font-size:13px;font-weight:800;">
                    Enterprise Workspace
                </div>
            </div>
        </section>

        <section style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;">
            <div style="border-radius:20px;background:white;border:1px solid #e5e7eb;padding:20px;box-shadow:0 12px 28px rgba(15,23,42,.08);"><div style="font-size:12px;color:#64748b;font-weight:800;text-transform:uppercase;">Projetos</div><div style="font-size:34px;font-weight:900;">{{ $stats['total'] }}</div></div>
            <div style="border-radius:20px;background:white;border:1px solid #e5e7eb;padding:20px;box-shadow:0 12px 28px rgba(15,23,42,.08);"><div style="font-size:12px;color:#64748b;font-weight:800;text-transform:uppercase;">Concluídos</div><div style="font-size:34px;font-weight:900;color:#16a34a;">{{ $stats['completed'] }}</div></div>
            <div style="border-radius:20px;background:white;border:1px solid #e5e7eb;padding:20px;box-shadow:0 12px 28px rgba(15,23,42,.08);"><div style="font-size:12px;color:#64748b;font-weight:800;text-transform:uppercase;">Falhas</div><div style="font-size:34px;font-weight:900;color:#dc2626;">{{ $stats['failed'] }}</div></div>
            <div style="border-radius:20px;background:white;border:1px solid #e5e7eb;padding:20px;box-shadow:0 12px 28px rgba(15,23,42,.08);"><div style="font-size:12px;color:#64748b;font-weight:800;text-transform:uppercase;">Online</div><div style="font-size:34px;font-weight:900;color:#2563eb;">{{ $stats['online'] }}</div></div>
        </section>

        <section style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;">
            @foreach ($this->projects as $project)
                <article style="border-radius:24px;background:white;border:1px solid #e5e7eb;box-shadow:0 16px 36px rgba(15,23,42,.10);overflow:hidden;">
                    <div style="padding:22px 24px;background:linear-gradient(135deg,#0f172a,#1e40af);color:white;">
                        <div style="display:flex;justify-content:space-between;gap:14px;align-items:flex-start;">
                            <div>
                                <div style="font-size:12px;text-transform:uppercase;letter-spacing:.16em;color:#bfdbfe;font-weight:800;">{{ $project->product ?: 'Produto' }}</div>
                                <h2 style="font-size:24px;font-weight:900;margin:6px 0;">{{ $project->name }}</h2>
                                <div style="font-size:13px;color:#dbeafe;">{{ $project->client_name ?: 'Cliente não informado' }}</div>
                            </div>
                            <span style="border-radius:999px;padding:7px 12px;font-size:12px;font-weight:900;{{ $statusColor($project->provisioning_status) }}">
                                {{ strtoupper($project->provisioning_status ?: 'PENDING') }}
                            </span>
                        </div>
                    </div>

                    <div style="padding:22px 24px;display:grid;gap:18px;">
                        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;font-size:13px;">
                            <div style="padding:14px;border-radius:16px;background:#f8fafc;"><strong>Domínio</strong><br><span style="color:#475569;">{{ $project->domain ?: '-' }}</span></div>
                            <div style="padding:14px;border-radius:16px;background:#f8fafc;"><strong>Ambiente</strong><br><span style="color:#475569;">{{ $project->environment ?: '-' }}</span></div>
                            <div style="padding:14px;border-radius:16px;background:#f8fafc;"><strong>Branch</strong><br><span style="color:#475569;">{{ $project->branch ?: '-' }}</span></div>
                            <div style="padding:14px;border-radius:16px;background:#f8fafc;"><strong>Health</strong><br><span style="color:#475569;">{{ $project->health_status ?: 'unknown' }}</span></div>
                        </div>

                        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                            @if ($project->domain)
                                <a target="_blank" href="https://{{ $project->domain }}" style="padding:10px 14px;border-radius:12px;background:#0f172a;color:white;font-weight:800;font-size:13px;text-decoration:none;">Abrir</a>
                            @endif
                            @if ($project->github_repository)
                                <a target="_blank" href="https://github.com/{{ $project->github_repository }}" style="padding:10px 14px;border-radius:12px;background:#eff6ff;color:#1d4ed8;font-weight:800;font-size:13px;text-decoration:none;">GitHub</a>
                            @endif
                            <a href="/admin/factory-projects/{{ $project->id }}/edit" style="padding:10px 14px;border-radius:12px;background:#f8fafc;color:#334155;border:1px solid #e2e8f0;font-weight:800;font-size:13px;text-decoration:none;">Logs / Editar</a>
                        </div>

                        <div style="border-top:1px solid #e5e7eb;padding-top:14px;font-size:12px;color:#64748b;">
                            Última atualização: {{ optional($project->updated_at)->format('d/m/Y H:i') ?: '-' }}
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    </div>
</x-filament-panels::page>
