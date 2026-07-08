@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@push('styles')
<style>
/* ── Onglets sidebar dashboard ── */
.dash-tab {
    display: flex; align-items: center; gap: 9px;
    padding: 9px 13px; border-radius: 9px;
    font-size: 12.5px; font-weight: 500;
    cursor: pointer; transition: all .18s;
    color: #555; border: none; background: none;
    width: 100%; text-align: left; white-space: nowrap;
}
.dash-tab:hover { background: #1a1a1a; color: #ccc; }
.dash-tab.active { background: rgba(234,88,12,.13); color: #f97316; }
.dash-tab i { font-size: 14px; min-width: 17px; text-align: center; flex-shrink:0; }

/* ── Panes ── */
.dash-pane { display: none; animation: fadeIn .25s ease; }
.dash-pane.active { display: block; }
@keyframes fadeIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }

/* ── KPI ── */
.kpi {
    background: #141414;
    border: 1px solid #1f1f1f;
    border-radius: 13px; padding: 1.1rem;
    transition: border-color .2s, transform .2s;
    position: relative; overflow: hidden;
}
.kpi::after {
    content:''; position:absolute; top:0; left:0; right:0; height:2px;
    background: linear-gradient(90deg,#ea580c,transparent);
    opacity:0; transition: opacity .3s;
}
.kpi:hover { border-color:#2a2a2a; transform: translateY(-2px); }
.kpi:hover::after { opacity:1; }

/* ── Commande card ── */
.cmd-row {
    display:flex; align-items:center; gap:12px;
    background:#141414; border:1px solid #1f1f1f;
    border-radius:11px; padding:12px 14px;
    transition: border-color .18s;
}
.cmd-row:hover { border-color: #2a2a2a; }

/* ── Table cell plan ── */
.tcell {
    aspect-ratio:1; border-radius:12px;
    border:1.5px solid #1f1f1f;
    display:flex; flex-direction:column;
    align-items:center; justify-content:center;
    cursor:pointer; transition:all .2s;
    background:#141414; gap:3px; min-height:68px;
}
.tcell.libre:hover { border-color:#22c55e; background:rgba(34,197,94,.06); }
.tcell.occupee { border-color:#ea580c; background:rgba(234,88,12,.07); }

/* ── Bon cuisine ── */
.bon {
    background:#0d0d0d; border:1px solid #1f1f1f;
    border-left:3px solid #ea580c; border-radius:11px;
    padding:.9rem; transition:all .2s;
}
.bon:hover { background:#111; }
.bon-blue { border-left-color:#3b82f6; }

/* ── Livraison column ── */
.lv-col { display:flex; flex-direction:column; gap:10px; }

/* ── Refresh spin ── */
@keyframes spin { to{transform:rotate(360deg)} }
.spin { animation: spin .6s linear infinite; }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- LAYOUT INTÉRIEUR : nav onglets à gauche + pane à droite   --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div style="display:flex; gap:16px; height:calc(100vh - 100px);">

    {{-- ── Onglets verticaux ── --}}
    <aside style="width:186px; flex-shrink:0; background:#0d0d0d;
                  border:1px solid #1a1a1a; border-radius:14px;
                  padding:12px; display:flex; flex-direction:column; gap:2px;
                  overflow-y:auto;">

        {{-- Accueil (tous) --}}
        <button class="dash-tab active" onclick="showPane('home',this)">
            <i class="fa-solid fa-house"></i>Accueil
        </button>

        @if(in_array(auth()->user()->role, ['Administrateur','Caissier','Serveur']))
        <button class="dash-tab" onclick="showPane('commandes',this)">
            <i class="fa-solid fa-receipt"></i>Commandes
            <span id="badge-commandes"
                  style="margin-left:auto;background:rgba(234,88,12,.2);color:#f97316;
                         font-size:10px;padding:1px 6px;border-radius:8px;font-weight:700;
                         display:{{ ($commandesEnAttente??0) > 0 ? 'inline-block' : 'none' }};">
                {{ $commandesEnAttente ?? 0 }}
            </span>
        </button>
        @endif

        @if(in_array(auth()->user()->role, ['Administrateur','Caissier']))
        <button class="dash-tab" onclick="showPane('caisse',this)">
            <i class="fa-solid fa-cash-register"></i>Caisse
        </button>
        @endif

        @if(in_array(auth()->user()->role, ['Administrateur','Cuisinier']))
        <button class="dash-tab" onclick="showPane('cuisine',this)">
            <i class="fa-solid fa-fire-burner"></i>Cuisine
            @php
              $nbPrep = is_countable($enPreparation??[]) ? count($enPreparation??[]) : 0;
              $nbAtt  = is_integer($commandesEnAttente??null) ? ($commandesEnAttente??0) : (is_countable($commandesEnAttente??[]) ? count($commandesEnAttente??[]) : 0);
            @endphp
            <span id="badge-cuisine"
                  style="margin-left:auto;background:rgba(59,130,246,.2);color:#60a5fa;
                         font-size:10px;padding:1px 6px;border-radius:8px;font-weight:700;
                         display:{{ ($nbPrep + $nbAtt) > 0 ? 'inline-block' : 'none' }};">
                {{ $nbPrep + $nbAtt }}
            </span>
        </button>
        @endif

        @if(in_array(auth()->user()->role, ['Administrateur','Livreur']))
        <button class="dash-tab" onclick="showPane('livraisons',this)">
            <i class="fa-solid fa-motorcycle"></i>Livraisons
            <span id="badge-livraisons"
                  style="margin-left:auto;background:rgba(234,88,12,.2);color:#f97316;
                         font-size:10px;padding:1px 6px;border-radius:8px;font-weight:700;
                         display:{{ ($livraisonsEnCours??0) > 0 ? 'inline-block' : 'none' }};">
                {{ $livraisonsEnCours ?? 0 }}
            </span>
        </button>
        @endif

        @if(in_array(auth()->user()->role, ['Administrateur','Serveur']))
        <button class="dash-tab" onclick="showPane('tables',this)">
            <i class="fa-solid fa-chair"></i>Tables
        </button>
        @endif

        @if(auth()->user()->role === 'Administrateur')
        <div style="height:1px;background:#1a1a1a;margin:6px 0;"></div>
        <button class="dash-tab" onclick="showPane('stats',this)">
            <i class="fa-solid fa-chart-line"></i>Statistiques
        </button>
        <button class="dash-tab" onclick="showPane('menus',this)">
            <i class="fa-solid fa-book-open"></i>Menus
        </button>
        <button class="dash-tab" onclick="showPane('users',this)">
            <i class="fa-solid fa-users"></i>Utilisateurs
        </button>
        @endif

        {{-- Spacer --}}
        <div style="flex:1;"></div>

        {{-- Refresh --}}
        <button onclick="refreshDash()"
                style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:9px;
                       border:none;background:none;color:#333;cursor:pointer;font-size:12px;width:100%;
                       transition:all .18s;"
                onmouseover="this.style.background='#1a1a1a';this.style.color='#f97316'"
                onmouseout="this.style.background='none';this.style.color='#333'">
            <i class="fa-solid fa-rotate-right" id="refresh-ico" style="font-size:13px;"></i>
            <span>Actualiser</span>
        </button>
    </aside>

    {{-- ── Contenu des panes ── --}}
    <div style="flex:1; overflow-y:auto; min-width:0;">

        {{-- ════════════════════════════════════ --}}
        {{-- PANE : ACCUEIL                       --}}
        {{-- ════════════════════════════════════ --}}
        <div id="pane-home" class="dash-pane active">

            {{-- Bienvenue --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                <div>
                    <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">
                        Bonjour, <span style="color:#f97316;">{{ auth()->user()->prenom }}</span> 👋
                    </h2>
                    <p style="font-size:12px;color:#444;margin:3px 0 0;">
                        Résumé de la journée en cours
                    </p>
                </div>
                <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:#333;">
                    <span style="width:7px;height:7px;border-radius:50%;background:#22c55e;
                                 box-shadow:0 0 6px rgba(34,197,94,.5);display:inline-block;"></span>
                    Système actif
                </div>
            </div>

            {{-- KPIs --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px;">

                @if(in_array(auth()->user()->role, ['Administrateur','Caissier']))
                <div class="kpi">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <div style="width:36px;height:36px;border-radius:10px;
                                    background:rgba(34,197,94,.12);display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-money-bill-wave" style="color:#22c55e;font-size:14px;"></i>
                        </div>
                        <span id="evo-ca" style="font-size:10px;font-weight:600;color:{{ ($evolutionCA??0)>=0 ? '#22c55e' : '#ef4444' }};">
                            {{ ($evolutionCA??0)>=0 ? '+' : '' }}{{ $evolutionCA ?? 0 }}%
                        </span>
                    </div>
                    <div style="font-size:22px;font-weight:700;color:#fff;" id="kpi-ca">
                        {{ number_format($caJour??0,0,',',' ') }}
                    </div>
                    <div style="font-size:11px;color:#444;margin-top:2px;">CA du jour (FCFA)</div>
                </div>
                @endif

                <div class="kpi">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <div style="width:36px;height:36px;border-radius:10px;
                                    background:rgba(59,130,246,.12);display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-receipt" style="color:#60a5fa;font-size:14px;"></i>
                        </div>
                        <span style="font-size:10px;font-weight:600;color:{{ ($evolutionCommandes??0)>=0 ? '#22c55e' : '#ef4444' }};">
                            {{ ($evolutionCommandes??0)>=0 ? '+' : '' }}{{ $evolutionCommandes ?? 0 }}%
                        </span>
                    </div>
                    <div style="font-size:22px;font-weight:700;color:#fff;" id="kpi-cmd">
                        {{ $commandesDuJour ?? 0 }}
                    </div>
                    <div style="font-size:11px;color:#444;margin-top:2px;">Commandes du jour</div>
                </div>

                <div class="kpi">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <div style="width:36px;height:36px;border-radius:10px;
                                    background:rgba(234,179,8,.12);display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-clock" style="color:#eab308;font-size:14px;"></i>
                        </div>
                    </div>
                    <div style="font-size:22px;font-weight:700;color:#fff;" id="kpi-att">
                        {{ is_integer($commandesEnAttente??null) ? ($commandesEnAttente??0) : (is_countable($commandesEnAttente??[]) ? count($commandesEnAttente??[]) : 0) }}
                    </div>
                    <div style="font-size:11px;color:#444;margin-top:2px;">En attente</div>
                </div>

                @if(in_array(auth()->user()->role, ['Administrateur','Serveur']))
                <div class="kpi">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <div style="width:36px;height:36px;border-radius:10px;
                                    background:rgba(168,85,247,.12);display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-chair" style="color:#a855f7;font-size:14px;"></i>
                        </div>
                    </div>
                    <div style="font-size:22px;font-weight:700;color:#fff;" id="kpi-tables">
                        {{ $tablesOccupees ?? 0 }}/{{ $totalTables ?? 0 }}
                    </div>
                    <div style="font-size:11px;color:#444;margin-top:2px;">Tables occupées</div>
                </div>
                @endif

                @if(in_array(auth()->user()->role, ['Administrateur','Livreur']))
                <div class="kpi">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <div style="width:36px;height:36px;border-radius:10px;
                                    background:rgba(234,88,12,.12);display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-motorcycle" style="color:#f97316;font-size:14px;"></i>
                        </div>
                    </div>
                    <div style="font-size:22px;font-weight:700;color:#fff;" id="kpi-livraisons">{{ $livraisonsEnCours ?? 0 }}</div>
                    <div style="font-size:11px;color:#444;margin-top:2px;">Livraisons en cours</div>
                </div>
                @endif

            </div>

            {{-- Graphique + top plats --}}
            @if(in_array(auth()->user()->role, ['Administrateur','Caissier']))
            <div style="display:grid;grid-template-columns:1fr 280px;gap:12px;margin-bottom:16px;">

                <div class="kpi">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                        <h3 style="font-size:13px;font-weight:600;color:#e5e5e5;margin:0;">
                            <i class="fa-solid fa-chart-area" style="color:#ea580c;margin-right:6px;"></i>
                            Ventes — 7 derniers jours
                        </h3>
                        <span style="font-size:10px;padding:3px 8px;border-radius:6px;background:#1a1a1a;color:#444;">FCFA</span>
                    </div>
                    <div class="chart-wrap"><canvas id="c-ventes"></canvas></div>
                </div>

                <div class="kpi">
                    <h3 style="font-size:13px;font-weight:600;color:#e5e5e5;margin:0 0 14px;">
                        <i class="fa-solid fa-fire" style="color:#ea580c;margin-right:6px;"></i>
                        Top plats
                    </h3>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @forelse($topPlats ?? [] as $i => $plat)
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="width:18px;height:18px;border-radius:50%;flex-shrink:0;
                                         background:rgba(234,88,12,.15);color:#f97316;
                                         font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;">
                                {{ $i+1 }}
                            </span>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12px;color:#e5e5e5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $plat->intitule }}
                                </div>
                                <div class="prog" style="margin-top:3px;">
                                    <div class="prog-bar" style="width:{{ $i===0?100:max(15,100-$i*18) }}%;"></div>
                                </div>
                            </div>
                            <span style="font-size:11px;font-weight:600;color:#555;flex-shrink:0;">{{ $plat->total_vendu }}</span>
                        </div>
                        @empty
                        <p style="font-size:12px;color:#333;text-align:center;padding:12px 0;">Aucune donnée</p>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            {{-- Activité récente --}}
            <div class="kpi">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                    <h3 style="font-size:13px;font-weight:600;color:#e5e5e5;margin:0;">
                        <i class="fa-solid fa-list-check" style="color:#ea580c;margin-right:6px;"></i>
                        Activité récente
                    </h3>
                    <a href="{{ route('commandes.index') }}"
                       style="font-size:11px;color:#444;text-decoration:none;transition:color .18s;"
                       onmouseover="this.style.color='#f97316'" onmouseout="this.style.color='#444'">
                        Voir tout <i class="fa-solid fa-arrow-right" style="margin-left:2px;"></i>
                    </a>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;" id="recent-list">
                    @forelse($dernieresCommandes ?? [] as $cmd)
                    <div class="cmd-row">
                        <div style="width:36px;height:36px;border-radius:10px;flex-shrink:0;
                                    background:#1a1a1a;display:flex;align-items:center;justify-content:center;">
                            @php
                                $iconRecent = match($cmd->typecommande) {
                                    'Livraison'  => 'fa-motorcycle',
                                    'A emporter' => 'fa-bag-shopping',
                                    default      => 'fa-chair',
                                };
                            @endphp
                            <i class="fa-solid {{ $iconRecent }}"
                               style="color:#333;font-size:13px;"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="font-size:12px;font-weight:700;color:#e5e5e5;">{{ $cmd->reference }}</span>
                                @if($cmd->table)
                                <span style="font-size:10px;color:#444;">· {{ $cmd->table->intitule }}</span>
                                @endif
                            </div>
                            <div style="font-size:11px;color:#444;margin-top:1px;">
                                {{ $cmd->heurecommande }} · {{ $cmd->lignes->count() }} article(s)
                            </div>
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <div style="font-size:12px;font-weight:700;color:#fff;margin-bottom:3px;">
                                {{ number_format($cmd->montant,0,',',' ') }} F
                            </div>
                            @php
                                $slug = match($cmd->statut_courant) {
                                    'En attente'     => 'attente',
                                    'En préparation' => 'prep',
                                    'Expédiée'       => 'expediee',
                                    'Servie'         => 'servie',
                                    'Livrée'         => 'livree',
                                    'Annulée'        => 'annulee',
                                    default          => 'attente'
                                };
                            @endphp
                            <span class="badge badge-{{ $slug }}">{{ $cmd->statut_courant }}</span>
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center;padding:28px 0;color:#2a2a2a;">
                        <i class="fa-solid fa-receipt" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                        <p style="font-size:13px;">Aucune commande aujourd'hui</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════ --}}
        {{-- PANE : COMMANDES                     --}}
        {{-- ════════════════════════════════════ --}}
        @if(in_array(auth()->user()->role, ['Administrateur','Caissier','Serveur']))
        <div id="pane-commandes" class="dash-pane">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0;">
                    <i class="fa-solid fa-receipt" style="color:#ea580c;margin-right:8px;"></i>Commandes
                    <br />
                    <span style="font-size:12px;color:#444;margin:3px 0 0;">Les 08 dernières commandes</span>
                </h2>
                <a href="{{ route('commandes.create') }}" class="btn-cc btn-cc-primary">
                    <i class="fa-solid fa-plus"></i>Nouvelle commande
                </a>
            </div>

            {{-- Filtres --}}
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:16px;">
                @foreach(['Toutes','En attente','En préparation','Expédiée','Servie','Livrée','Annulée'] as $s)
                <button onclick="filterCmd('{{ $s }}')"
                        data-s="{{ $s }}"
                        class="filter-cmd"
                        style="padding:6px 14px;border-radius:20px;font-size:11px;font-weight:500;
                               cursor:pointer;transition:all .18s;border:1px solid #1f1f1f;
                               background:{{ $s==='Toutes'?'#ea580c':'#141414' }};
                               color:{{ $s==='Toutes'?'#fff':'#555' }};">
                    {{ $s }}
                </button>
                @endforeach
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;" id="cmd-list">
                @forelse($dernieresCommandes ?? [] as $cmd)
                @php
                    $slug = match($cmd->statut_courant) {
                        'En attente'=>'attente','En préparation'=>'prep',
                        'Expédiée'=>'expediee','Servie'=>'servie',
                        'Livrée'=>'livree','Annulée'=>'annulee',default=>'attente'
                    };
                    
                    $iconCmd = match($cmd->typecommande) {
                        'Livraison'  => 'fa-motorcycle',
                        'A emporter' => 'fa-bag-shopping',
                        default      => 'fa-chair',
                    };
                    $couleurCmd = match($cmd->typecommande) {
                        'Livraison'  => '#f97316',
                        'A emporter' => '#22c55e',
                        default      => '#60a5fa',
                    };
                @endphp
                <div class="cmd-row cmd-item" data-s="{{ $cmd->statut_courant }}">
                    <div style="width:38px;height:38px;border-radius:10px;flex-shrink:0;
                                background:#1a1a1a;display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid {{ $iconCmd }}"
                           style="color:{{ $couleurCmd }};font-size:14px;"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                            <span style="font-size:13px;font-weight:700;color:#e5e5e5;">{{ $cmd->reference }}</span>
                            @if($cmd->table)
                            <span style="font-size:10px;padding:2px 7px;border-radius:5px;background:#1a1a1a;color:#444;">
                                {{ $cmd->table->intitule }}
                            </span>
                            @endif
                            <span style="font-size:10px;color:#333;">{{ $cmd->typecommande === 'A emporter' ? 'À emporter' : $cmd->typecommande }}</span>
                        </div>
                        <div style="font-size:11px;color:#444;margin-top:2px;">
                            <i class="fa-regular fa-clock" style="margin-right:3px;"></i>{{ $cmd->heurecommande }}
                            · {{ $cmd->lignes->count() }} article(s)
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:13px;font-weight:700;color:#fff;margin-bottom:4px;">
                            {{ number_format($cmd->montant,0,',',' ') }} FCFA
                        </div>
                        <span class="badge badge-{{ $slug }}">{{ $cmd->statut_courant }}</span>
                    </div>
                    <a href="{{ route('commandes.show', $cmd->idcommande) }}"
                       style="width:32px;height:32px;border-radius:8px;background:#1a1a1a;
                              border:1px solid #252525;display:flex;align-items:center;justify-content:center;
                              color:#444;transition:all .18s;flex-shrink:0;text-decoration:none;"
                       onmouseover="this.style.color='#f97316';this.style.borderColor='rgba(234,88,12,.3)'"
                       onmouseout="this.style.color='#444';this.style.borderColor='#252525'">
                        <i class="fa-solid fa-eye" style="font-size:12px;"></i>
                    </a>
                </div>
                @empty
                <div style="text-align:center;padding:40px;color:#2a2a2a;">
                    <i class="fa-solid fa-receipt" style="font-size:32px;display:block;margin-bottom:10px;"></i>
                    <p style="font-size:13px;">Aucune commande</p>
                </div>
                @endforelse
            </div>
        </div>
        @endif

        {{-- ════════════════════════════════════ --}}
        {{-- PANE : CAISSE                        --}}
        {{-- ════════════════════════════════════ --}}
        @if(in_array(auth()->user()->role, ['Administrateur','Caissier']))
        <div id="pane-caisse" class="dash-pane">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0;">
                    <i class="fa-solid fa-cash-register" style="color:#ea580c;margin-right:8px;"></i>Caisse du jour
                </h2>
                <div style="display:flex;gap:8px;">
                    <a target="_blank" href="{{ route('caisse.rapport') }}" class="btn-cc btn-cc-ghost">
                        <i class="fa-solid fa-file-pdf"></i>Rapport PDF
                    </a>
                    <button onclick="confirmerCloture()" class="btn-cc btn-cc-primary">
                        <i class="fa-solid fa-lock"></i>Clôturer
                    </button>
                </div>
            </div>

            {{-- KPIs caisse --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
                <div class="kpi" style="text-align:center;">
                    <div style="font-size:26px;font-weight:700;color:#22c55e;margin-bottom:4px;" id="kpi-caisse-total">
                        {{ number_format($totalCaisse??0,0,',',' ') }}
                    </div>
                    <div style="font-size:11px;color:#444;">Total encaissé (FCFA)</div>
                </div>
                <div class="kpi" style="text-align:center;">
                    <div style="font-size:26px;font-weight:700;color:#fff;margin-bottom:4px;" id="kpi-caisse-nb">{{ $nbEncaissees ?? 0 }}</div>
                    <div style="font-size:11px;color:#444;">Commandes encaissées</div>
                </div>
                <div class="kpi" style="text-align:center;">
                    <div style="font-size:26px;font-weight:700;color:#f97316;margin-bottom:4px;" id="kpi-caisse-panier">
                        {{ number_format($panierMoyen??0,0,',',' ') }}
                    </div>
                    <div style="font-size:11px;color:#444;">Panier moyen (FCFA)</div>
                </div>
            </div>

            {{-- À encaisser --}}
            @if(isset($aEncaisser) && is_iterable($aEncaisser) && count($aEncaisser) > 0)
            <div class="kpi" style="margin-bottom:14px;">
                <h3 style="font-size:13px;font-weight:600;color:#eab308;margin:0 0 12px;">
                    <i class="fa-solid fa-hourglass-half" style="margin-right:6px;"></i>
                    À encaisser ({{ count($aEncaisser) }})
                </h3>
                <div style="display:flex;flex-direction:column;gap:7px;">
                    @foreach($aEncaisser as $cmd)
                    @php
                        $couleurStatut = match($cmd->statut_courant) {
                            'En préparation' => '#60a5fa',
                            'Expédiée'       => '#f97316',
                            default          => '#eab308', // En attente
                        };
                    @endphp
                    <div style="display:flex;align-items:center;justify-content:space-between;
                                padding:10px 14px;border-radius:9px;background:#0d0d0d;border:1px solid #1a1a1a;">
                        <div>
                            <span style="font-size:12px;font-weight:700;color:#e5e5e5;">{{ $cmd->reference }}</span>
                            @if($cmd->table)
                            <span style="font-size:11px;color:#444;margin-left:6px;">{{ $cmd->table->intitule }}</span>
                            @endif
                            <span style="font-size:10px;font-weight:600;margin-left:6px;color:{{ $couleurStatut }};">
                                {{ $cmd->statut_courant }}
                            </span>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:13px;font-weight:700;color:#fff;">
                                {{ number_format($cmd->montant,0,',',' ') }} F
                            </span>
                            <a href="{{ route('caisse.recu', $cmd->idcommande) }}" class="btn-cc btn-cc-primary" style="padding:6px 12px;">
                                <i class="fa-solid fa-print"></i>Reçu
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Graphique répartition --}}
            <div class="kpi">
                <h3 style="font-size:13px;font-weight:600;color:#e5e5e5;margin:0 0 14px;">
                    <i class="fa-solid fa-chart-pie" style="color:#ea580c;margin-right:6px;"></i>
                    Répartition Standard / À emporter / Livraison
                </h3>
                <div class="chart-wrap" style="height:180px;"><canvas id="c-caisse"></canvas></div>
            </div>
        </div>
        @endif

        {{-- ════════════════════════════════════ --}}
        {{-- PANE : CUISINE                       --}}
        {{-- ════════════════════════════════════ --}}
        @if(in_array(auth()->user()->role, ['Administrateur','Cuisinier']))
        <div id="pane-cuisine" class="dash-pane">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0;">
                    <i class="fa-solid fa-fire-burner" style="color:#ea580c;margin-right:8px;"></i>Écran Cuisine
                </h2>
                <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:#444;">
                    <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;
                                 box-shadow:0 0 6px rgba(34,197,94,.5);"></span>
                    Mis à jour automatiquement
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

                {{-- En attente --}}
                @php
                    // [AJOUT] $cuisineEnAttente est la collection dédiée fournie
                    // à l'Administrateur. $commandesEnAttente reste utilisé tel
                    // quel pour le rôle Cuisinier (déjà une collection dans ce cas).
                    $listeCuisineAttente = $cuisineEnAttente
                        ?? (is_iterable($commandesEnAttente ?? []) && !is_integer($commandesEnAttente ?? null)
                            ? ($commandesEnAttente ?? [])
                            : []);
                @endphp
                <div>
                    <div style="font-size:10px;font-weight:600;letter-spacing:2px;color:#eab308;
                                text-transform:uppercase;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                        <i class="fa-solid fa-clock"></i>En attente
                        <span style="background:rgba(234,179,8,.15);color:#eab308;font-size:10px;
                                     padding:1px 6px;border-radius:6px;">
                            {{ is_countable($listeCuisineAttente) ? count($listeCuisineAttente) : 0 }}
                        </span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @forelse($listeCuisineAttente as $cmd)
                        @if(is_object($cmd))
                        <div class="bon">
                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:12px;font-weight:700;color:#e5e5e5;">{{ $cmd->reference }}</span>
                                @if($cmd->table)
                                <span style="font-size:10px;color:#444;background:#1a1a1a;
                                             padding:2px 7px;border-radius:5px;">{{ $cmd->table->intitule }}</span>
                                @endif
                            </div>
                            <div style="display:flex;flex-direction:column;gap:4px;margin-bottom:10px;">
                                @foreach($cmd->lignes as $l)
                                <div style="display:flex;justify-content:space-between;font-size:12px;">
                                    <span style="color:#ccc;">{{ $l->menu->intitule ?? 'N/A' }}</span>
                                    <span style="font-weight:700;color:#f97316;">×{{ $l->quantite }}</span>
                                </div>
                                @endforeach
                            </div>
                            @if($cmd->consignes)
                            <div style="font-size:11px;padding:6px 10px;border-radius:7px;margin-bottom:8px;
                                        background:rgba(234,179,8,.07);color:#eab308;border:1px solid rgba(234,179,8,.15);">
                                <i class="fa-solid fa-note-sticky" style="margin-right:4px;"></i>{{ $cmd->consignes }}
                            </div>
                            @endif
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <span style="font-size:10px;color:#444;">
                                    <i class="fa-regular fa-clock" style="margin-right:3px;"></i>{{ $cmd->heurecommande }}
                                </span>
                                <form method="POST" action="{{ route('cuisine.prendre-en-charge', $cmd->idcommande) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn-cc"
                                            style="padding:5px 12px;background:rgba(59,130,246,.12);
                                                   color:#60a5fa;border:1px solid rgba(59,130,246,.2);border-radius:8px;
                                                   font-size:11px;font-weight:600;cursor:pointer;transition:all .18s;"
                                            onmouseover="this.style.background='#3b82f6';this.style.color='#fff'"
                                            onmouseout="this.style.background='rgba(59,130,246,.12)';this.style.color='#60a5fa'">
                                        <i class="fa-solid fa-play" style="margin-right:4px;"></i>Prendre en charge
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endif
                        @empty
                        <div style="text-align:center;padding:24px;border:1px dashed #1a1a1a;border-radius:11px;">
                            <i class="fa-solid fa-check" style="color:#22c55e;font-size:22px;display:block;margin-bottom:6px;"></i>
                            <p style="font-size:12px;color:#333;">Aucune commande en attente</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- En préparation --}}
                <div>
                    <div style="font-size:10px;font-weight:600;letter-spacing:2px;color:#60a5fa;
                                text-transform:uppercase;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                        <i class="fa-solid fa-fire-burner"></i>En préparation
                        <span style="background:rgba(59,130,246,.15);color:#60a5fa;font-size:10px;
                                     padding:1px 6px;border-radius:6px;">
                            {{ is_countable($enPreparation??[]) ? count($enPreparation??[]) : 0 }}
                        </span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @forelse($enPreparation ?? [] as $cmd)
                        @if(is_object($cmd))
                        <div class="bon bon-blue">
                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:12px;font-weight:700;color:#e5e5e5;">{{ $cmd->reference }}</span>
                                @if($cmd->table)
                                <span style="font-size:10px;color:#444;background:#1a1a1a;
                                             padding:2px 7px;border-radius:5px;">{{ $cmd->table->intitule }}</span>
                                @endif
                            </div>
                            <div style="display:flex;flex-direction:column;gap:4px;margin-bottom:10px;">
                                @foreach($cmd->lignes as $l)
                                <div style="display:flex;justify-content:space-between;font-size:12px;">
                                    <span style="color:#ccc;">{{ $l->menu->intitule ?? 'N/A' }}</span>
                                    <span style="font-weight:700;color:#60a5fa;">×{{ $l->quantite }}</span>
                                </div>
                                @endforeach
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <span style="font-size:10px;color:#444;">
                                    <i class="fa-regular fa-clock" style="margin-right:3px;"></i>{{ $cmd->heurecommande }}
                                </span>
                                <form method="POST" action="{{ route('cuisine.prete', $cmd->idcommande) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn-cc"
                                            style="padding:5px 12px;background:rgba(34,197,94,.12);
                                                   color:#22c55e;border:1px solid rgba(34,197,94,.2);border-radius:8px;
                                                   font-size:11px;font-weight:600;cursor:pointer;transition:all .18s;"
                                            onmouseover="this.style.background='#22c55e';this.style.color='#fff'"
                                            onmouseout="this.style.background='rgba(34,197,94,.12)';this.style.color='#22c55e'">
                                        <i class="fa-solid fa-check" style="margin-right:4px;"></i>Marquer prête
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endif
                        @empty
                        <div style="text-align:center;padding:24px;border:1px dashed #1a1a1a;border-radius:11px;">
                            <i class="fa-solid fa-utensils" style="color:#333;font-size:22px;display:block;margin-bottom:6px;"></i>
                            <p style="font-size:12px;color:#333;">Rien en préparation</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ════════════════════════════════════ --}}
        {{-- PANE : LIVRAISONS                    --}}
        {{-- ════════════════════════════════════ --}}
        @if(in_array(auth()->user()->role, ['Administrateur','Livreur']))
        <div id="pane-livraisons" class="dash-pane">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0;">
                    <i class="fa-solid fa-motorcycle" style="color:#ea580c;margin-right:8px;"></i>Livraisons
                </h2>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                @foreach([
                    ['label'=>'En attente','color'=>'#eab308','data'=>$livraisonsAttente??[],'statut'=>null],
                    ['label'=>'En préparation','color'=>'#60a5fa','data'=>$livraisonsPrepa??[],'statut'=>null],
                    ['label'=>'En route','color'=>'#f97316','data'=>$livraisonsEnRoute??[],'statut'=>'Livrée'],
                ] as $col)
                <div>
                    <div style="font-size:10px;font-weight:600;letter-spacing:2px;color:{{ $col['color'] }};
                                text-transform:uppercase;margin-bottom:10px;">
                        <i class="fa-solid fa-circle" style="font-size:6px;margin-right:5px;"></i>{{ $col['label'] }}
                        ({{ is_countable($col['data']) ? count($col['data']) : 0 }})
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @forelse(is_iterable($col['data'])?$col['data']:[] as $cmd)
                        @if(is_object($cmd))
                        <div class="bon" style="border-left-color:{{ $col['color'] }};">
                            <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                                <span style="font-size:12px;font-weight:700;color:#e5e5e5;">{{ $cmd->reference }}</span>
                                <span style="font-size:10px;color:#444;">{{ $cmd->heurecommande }}</span>
                            </div>
                            @if($cmd->client)
                            <div style="font-size:11px;color:#888;margin-bottom:3px;">
                                <i class="fa-solid fa-user" style="margin-right:4px;color:#444;"></i>
                                {{ $cmd->client->prenom }} {{ $cmd->client->nom }}
                            </div>
                            @endif
                            @if($cmd->adresse)
                            <div style="font-size:11px;color:#555;margin-bottom:8px;">
                                <i class="fa-solid fa-location-dot" style="margin-right:4px;"></i>
                                {{ Str::limit($cmd->adresse, 38) }}
                            </div>
                            @endif
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <span style="font-size:12px;font-weight:700;color:#fff;">
                                    {{ number_format($cmd->montant,0,',',' ') }} F
                                </span>
                                @if($col['statut'])
                                <form method="POST" action="{{ route('livraisons.statut', $cmd->idcommande) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="statut" value="{{ $col['statut'] }}">
                                    <button type="submit"
                                            style="padding:5px 10px;border-radius:7px;border:1px solid rgba(34,197,94,.2);
                                                   background:rgba(34,197,94,.1);color:#22c55e;font-size:11px;
                                                   font-weight:600;cursor:pointer;transition:all .18s;"
                                            onmouseover="this.style.background='#22c55e';this.style.color='#fff'"
                                            onmouseout="this.style.background='rgba(34,197,94,.1)';this.style.color='#22c55e'">
                                        <i class="fa-solid fa-check" style="margin-right:3px;"></i>Livrée
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @endif
                        @empty
                        <div style="text-align:center;padding:20px;border:1px dashed #1a1a1a;border-radius:10px;">
                            <p style="font-size:12px;color:#2a2a2a;">Aucune</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ════════════════════════════════════ --}}
        {{-- PANE : TABLES                        --}}
        {{-- ════════════════════════════════════ --}}
        @if(in_array(auth()->user()->role, ['Administrateur','Serveur']))
        <div id="pane-tables" class="dash-pane">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0;">
                    <i class="fa-solid fa-chair" style="color:#ea580c;margin-right:8px;"></i>Plan de salle
                </h2>
                <div style="display:flex;gap:14px;font-size:11px;color:#444;">
                    <span style="display:flex;align-items:center;gap:5px;">
                        <span style="width:10px;height:10px;border-radius:3px;border:1px solid #22c55e;
                                     background:rgba(34,197,94,.1);"></span>
                        Libre ({{ $tablesLibres ?? 0 }})
                    </span>
                    <span style="display:flex;align-items:center;gap:5px;">
                        <span style="width:10px;height:10px;border-radius:3px;border:1px solid #ea580c;
                                     background:rgba(234,88,12,.1);"></span>
                        Occupée ({{ $tablesOccupees ?? 0 }})
                    </span>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:10px;">
                @forelse($tables ?? [] as $table)
                <div class="tcell {{ $table->occupee ? 'occupee' : 'libre' }}"
                     onclick="tableClick({{ $table->idtable }},'{{ $table->intitule }}',{{ $table->occupee?'true':'false' }},{{ $table->montant_total??0 }},{{ $table->nb_commandes_actives??0 }})">
                    <i class="fa-solid fa-chair" style="font-size:20px;color:{{ $table->occupee?'#f97316':'#2a2a2a' }};"></i>
                    <span style="font-size:11px;font-weight:600;color:{{ $table->occupee?'#e5e5e5':'#444' }};">
                        {{ $table->intitule }}
                    </span>
                    @if($table->occupee && ($table->montant_total??0)>0)
                    <span style="font-size:10px;font-weight:700;color:#f97316;">
                        {{ number_format($table->montant_total,0,',',' ') }}F
                    </span>
                    <span style="font-size:9px;color:#888;">
                        {{ $table->nb_commandes_actives }} commande{{ $table->nb_commandes_actives > 1 ? 's' : '' }}
                    </span>
                    @else
                    <span style="font-size:10px;color:#2a2a2a;">Libre</span>
                    @endif
                </div>
                @empty
                <div style="grid-column:1/-1;text-align:center;padding:32px;color:#2a2a2a;">
                    <i class="fa-solid fa-chair" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                    <p style="font-size:13px;">Aucune table configurée</p>
                </div>
                @endforelse
            </div>
        </div>
        @endif

        {{-- ════════════════════════════════════ --}}
        {{-- PANE : STATISTIQUES (Admin)           --}}
        {{-- ════════════════════════════════════ --}}
        @if(auth()->user()->role === 'Administrateur')
        <div id="pane-stats" class="dash-pane">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0;">
                    <i class="fa-solid fa-chart-line" style="color:#ea580c;margin-right:8px;"></i>Statistiques
                </h2>
                <div style="display:flex;gap:6px;">
                    @foreach(['jour','semaine','mois'] as $p)
                    <a href="?periode={{ $p }}"
                       style="padding:6px 14px;border-radius:8px;font-size:11px;font-weight:500;
                              text-decoration:none;transition:all .18s;
                              background:{{ ($periode??'semaine')===$p?'#ea580c':'#141414' }};
                              color:{{ ($periode??'semaine')===$p?'#fff':'#555' }};
                              border:1px solid {{ ($periode??'semaine')===$p?'#ea580c':'#1f1f1f' }};">
                        {{ ucfirst($p) }}
                    </a>
                    @endforeach
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:16px;">
                @foreach([
                    ['label'=>'CA Total','val'=>number_format($stats['ca_total']??0,0,',',' ').' F','color'=>'#22c55e'],
                    ['label'=>'Commandes','val'=>$stats['nb_commandes']??0,'color'=>'#60a5fa'],
                    ['label'=>'Livraisons','val'=>$stats['nb_livraisons']??0,'color'=>'#f97316'],
                    ['label'=>'Panier moyen','val'=>number_format($stats['panier_moyen']??0,0,',',' ').' F','color'=>'#a855f7'],
                ] as $k)
                <div class="kpi" style="text-align:center;">
                    <div style="font-size:20px;font-weight:700;color:{{ $k['color'] }};margin-bottom:4px;">{{ $k['val'] }}</div>
                    <div style="font-size:11px;color:#444;">{{ $k['label'] }}</div>
                </div>
                @endforeach
            </div>

            <div style="display:grid;grid-template-columns:1fr 260px;gap:12px;">
                <div class="kpi">
                    <h3 style="font-size:13px;font-weight:600;color:#e5e5e5;margin:0 0 14px;">
                        <i class="fa-solid fa-chart-bar" style="color:#ea580c;margin-right:6px;"></i>Évolution des ventes
                    </h3>
                    <div class="chart-wrap"><canvas id="c-stats"></canvas></div>
                </div>
                <div class="kpi">
                    <h3 style="font-size:13px;font-weight:600;color:#e5e5e5;margin:0 0 14px;">
                        <i class="fa-solid fa-ranking-star" style="color:#ea580c;margin-right:6px;"></i>Top 10 plats
                    </h3>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @forelse($topPlats ?? [] as $i => $plat)
                        <div style="display:flex;align-items:center;gap:7px;">
                            <span style="font-size:10px;color:#444;width:14px;flex-shrink:0;">{{ $i+1 }}</span>
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                                    <span style="font-size:11px;color:#e5e5e5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:140px;">{{ $plat->intitule }}</span>
                                    <span style="font-size:11px;color:#f97316;font-weight:600;">{{ $plat->total_vendu }}</span>
                                </div>
                                <div class="prog">
                                    <div class="prog-bar" style="width:{{ $i===0?100:max(10,100-$i*10) }}%;"></div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p style="font-size:12px;color:#333;text-align:center;padding:10px;">Aucune donnée</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════ --}}
        {{-- PANE : MENUS                         --}}
        {{-- ════════════════════════════════════ --}}
        <div id="pane-menus" class="dash-pane">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0;">
                    <i class="fa-solid fa-book-open" style="color:#ea580c;margin-right:8px;"></i>Menus & Plats
                </h2>
                <a href="{{ route('admin.menus.create') }}" class="btn-cc btn-cc-primary">
                    <i class="fa-solid fa-plus"></i>Nouveau plat
                </a>
            </div>
            <div class="kpi" style="text-align:center;padding:28px;">
                <i class="fa-solid fa-book-open" style="font-size:28px;color:#2a2a2a;display:block;margin-bottom:10px;"></i>
                <a href="{{ route('admin.menus.index') }}" style="color:#f97316;font-size:13px;text-decoration:none;">
                    <i class="fa-solid fa-arrow-right" style="margin-right:6px;"></i>
                    Gérer tous les menus et plats
                </a>
            </div>
        </div>

        {{-- ════════════════════════════════════ --}}
        {{-- PANE : UTILISATEURS                  --}}
        {{-- ════════════════════════════════════ --}}
        <div id="pane-users" class="dash-pane">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:16px;font-weight:700;color:#fff;margin:0;">
                    <i class="fa-solid fa-users" style="color:#ea580c;margin-right:8px;"></i>
                    Utilisateurs
                    <span style="font-size:13px;font-weight:400;color:#444;margin-left:6px;">
                        ({{ $totalUsers??0 }} actifs · {{ $usersConnectes??0 }} connectés)
                    </span>
                </h2>
                <a href="{{ route('admin.utilisateurs.create') }}" class="btn-cc btn-cc-primary">
                    <i class="fa-solid fa-user-plus"></i>Nouvel utilisateur
                </a>
            </div>
            <div class="kpi" style="text-align:center;padding:28px;">
                <i class="fa-solid fa-users" style="font-size:28px;color:#2a2a2a;display:block;margin-bottom:10px;"></i>
                <a href="{{ route('admin.utilisateurs.index') }}" style="color:#f97316;font-size:13px;text-decoration:none;">
                    <i class="fa-solid fa-arrow-right" style="margin-right:6px;"></i>
                    Gérer tous les utilisateurs
                </a>
            </div>
        </div>
        @endif

    </div>{{-- fin panes --}}
</div>{{-- fin layout intérieur --}}

@endsection

@push('scripts')
<script>
// ── Navigation onglets ──────────────────────────────────────
function showPane(name, btn) {
    document.querySelectorAll('.dash-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.dash-tab').forEach(b => b.classList.remove('active'));
    const pane = document.getElementById('pane-' + name);
    if (pane) pane.classList.add('active');
    if (btn) btn.classList.add('active');
}

// ── Filtre commandes ─────────────────────────────────────────
function filterCmd(s) {
    document.querySelectorAll('.filter-cmd').forEach(b => {
        const active = b.dataset.s === s;
        b.style.background = active ? '#ea580c' : '#141414';
        b.style.color       = active ? '#fff'     : '#555';
        b.style.borderColor = active ? '#ea580c'  : '#1f1f1f';
    });
    document.querySelectorAll('.cmd-item').forEach(row => {
        row.style.display = (s === 'Toutes' || row.dataset.s === s) ? 'flex' : 'none';
    });
}

// ── Tables ───────────────────────────────────────────────────
function tableClick(id, nom, occupee, montant, nbCommandes) {
    if (occupee) {
        Swal.fire({
            title: nom,
            html: `<div style="color:#888;font-size:13px;margin-bottom:10px;">
                       Table occupée · ${nbCommandes} commande${nbCommandes > 1 ? 's' : ''} en cours
                   </div>
                   <div style="font-size:24px;font-weight:700;color:#f97316;">
                       ${new Intl.NumberFormat('fr-FR').format(montant)} FCFA
                   </div>
                   <div style="font-size:11px;color:#555;margin-top:4px;">Montant cumulé de toutes les commandes actives</div>`,
            background: '#141414', color: '#e5e5e5',
            confirmButtonColor: '#ea580c', confirmButtonText: 'Voir les commandes',
            showCancelButton: true, cancelButtonText: 'Fermer', cancelButtonColor: '#1f1f1f',
        }).then(r => { if (r.isConfirmed) window.location.href = '/commandes?table=' + id; });
    } else {
        Swal.fire({
            title: nom, text: 'Table libre — créer une commande ?',
            icon: 'question', iconColor: '#22c55e',
            background: '#141414', color: '#e5e5e5',
            confirmButtonColor: '#ea580c', confirmButtonText: 'Nouvelle commande',
            showCancelButton: true, cancelButtonText: 'Annuler', cancelButtonColor: '#1f1f1f',
        }).then(r => { if (r.isConfirmed) window.location.href = '/commandes/nouvelle?table=' + id; });
    }
}

// ── Clôture caisse ───────────────────────────────────────────
function confirmerCloture() {
    Swal.fire({
        title: 'Clôturer la caisse ?',
        html: '<div style="color:#666;font-size:13px;">Un rapport Z sera généré. Vérifiez que toutes les commandes sont encaissées.</div>',
        icon: 'warning', iconColor: '#ea580c',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: '#ea580c', confirmButtonText: 'Oui, clôturer',
        showCancelButton: true, cancelButtonText: 'Annuler', cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) {
            window.open('{{ route("caisse.cloturer") }}', '_blank');
        }
    });
}

// ── Refresh temps réel ───────────────────────────────────────
function refreshDash() {
    const ico = document.getElementById('refresh-ico');
    if (ico) ico.classList.add('spin');

    fetch('{{ route("dashboard.refresh") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(r => r.json())
    .then(({ data: d }) => {
        const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
        const setBadge = (id, v) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = v;
            el.style.display = v > 0 ? 'inline-block' : 'none';
        };

        // KPIs de l'onglet Accueil
        set('kpi-att', d.commandes_en_attente);
        set('kpi-cmd', d.nb_commandes_jour);
        set('kpi-ca',  new Intl.NumberFormat('fr-FR').format(d.ca_jour));
        set('kpi-livraisons', d.livraisons_en_cours);

        // [AJOUT] KPI Tables occupées (id existant, jamais branché jusqu'ici)
        const kpiTables = document.getElementById('kpi-tables');
        if (kpiTables && kpiTables.textContent.includes('/')) {
            const total = kpiTables.textContent.split('/')[1];
            kpiTables.textContent = d.tables_occupees + '/' + total;
        }

        // [AJOUT] KPIs de l'onglet Caisse
        set('kpi-caisse-total',  new Intl.NumberFormat('fr-FR').format(d.total_caisse));
        set('kpi-caisse-nb',     d.nb_encaissees);
        const panierMoyen = d.nb_encaissees > 0 ? Math.round(d.total_caisse / d.nb_encaissees) : 0;
        set('kpi-caisse-panier', new Intl.NumberFormat('fr-FR').format(panierMoyen));

        // [AJOUT] Badges des onglets sidebar
        setBadge('badge-commandes',  d.commandes_en_attente);
        setBadge('badge-cuisine',    (d.commandes_en_attente ?? 0) + (d.commandes_en_preparation ?? 0));
        setBadge('badge-livraisons', d.livraisons_en_cours);

        Swal.fire({
            toast: true, position: 'bottom-end',
            icon: 'success', title: 'Actualisé',
            timer: 1800, showConfirmButton: false,
            background: '#141414', color: '#e5e5e5', iconColor: '#22c55e',
        });
    })
    .catch(() => {
        Swal.fire({
            toast: true, position: 'bottom-end',
            icon: 'error', title: 'Erreur réseau',
            timer: 2000, showConfirmButton: false,
            background: '#141414', color: '#e5e5e5',
        });
    })
    .finally(() => { if (ico) ico.classList.remove('spin'); });
}

// Auto-refresh 30s
setInterval(() => refreshDash(), 30000);

// ── Chart : Ventes ───────────────────────────────────────────
(function() {
    const el = document.getElementById('c-ventes');
    if (!el) return;
    new Chart(el, {
        type: 'line',
        data: {
            labels: {!! json_encode($labelsVentes ?? []) !!},
            datasets: [{
                label: 'CA (FCFA)',
                data: {!! json_encode($dataVentes ?? []) !!},
                borderColor: '#ea580c',
                backgroundColor: 'rgba(234,88,12,0.06)',
                fill: true, tension: 0.4,
                pointRadius: 4, pointBackgroundColor: '#ea580c',
                pointBorderColor: '#0d0d0d', pointBorderWidth: 2,
            },{
                label: 'Nb commandes',
                data: {!! json_encode($dataNb ?? []) !!},
                borderColor: '#60a5fa', backgroundColor: 'transparent',
                tension: 0.4, pointRadius: 3, yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { labels: { color: '#444', font: { size: 10 }, boxWidth: 10 } },
                tooltip: {
                    background: '#141414', borderColor: '#1f1f1f', borderWidth: 1,
                    titleColor: '#e5e5e5', bodyColor: '#888',
                }
            },
            scales: {
                x: { grid: { color: '#141414' }, ticks: { color: '#333', font: { size: 10 } } },
                y: { grid: { color: '#141414' }, ticks: { color: '#333', font: { size: 10 }, callback: v => new Intl.NumberFormat('fr-FR').format(v) } },
                y1: { position: 'right', grid: { display: false }, ticks: { color: '#333', font: { size: 10 } } }
            }
        }
    });
})();

// ── Chart : Caisse ───────────────────────────────────────────
(function() {
    const el = document.getElementById('c-caisse');
    if (!el) return;
    const d = {!! json_encode($dataRepartition ?? ['Standard'=>0,'A emporter'=>0,'Livraison'=>0]) !!};
    new Chart(el, {
        type: 'doughnut',
        data: {
            labels: Object.keys(d),
            datasets: [{
                data: Object.values(d),
                backgroundColor: ['rgba(234,88,12,0.8)','rgba(34,197,94,0.8)','rgba(96,165,250,0.8)'],
                borderColor: '#0d0d0d', borderWidth: 3, hoverOffset: 5,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '68%',
            plugins: { legend: { position: 'bottom', labels: { color: '#444', font: { size: 11 }, padding: 14, boxWidth: 10 } } }
        }
    });
})();

// ── Chart : Statistiques ─────────────────────────────────────
(function() {
    const el = document.getElementById('c-stats');
    if (!el) return;
    new Chart(el, {
        type: 'bar',
        data: {
            labels: {!! json_encode($labelsVentes ?? []) !!},
            datasets: [{
                label: 'CA (FCFA)',
                data: {!! json_encode($dataVentes ?? []) !!},
                backgroundColor: 'rgba(234,88,12,0.75)',
                borderRadius: 5, borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#141414', borderColor: '#1f1f1f', borderWidth: 1,
                    titleColor: '#e5e5e5', bodyColor: '#888',
                    callbacks: { label: ctx => ' ' + new Intl.NumberFormat('fr-FR').format(ctx.parsed.y) + ' FCFA' }
                }
            },
            scales: {
                x: { grid: { color: '#141414' }, ticks: { color: '#333', font: { size: 10 } } },
                y: { grid: { color: '#141414' }, ticks: { color: '#333', font: { size: 10 }, callback: v => new Intl.NumberFormat('fr-FR').format(v) } }
            }
        }
    });
})();
</script>
@endpush