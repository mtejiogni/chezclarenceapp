@extends('layouts.app')

@section('title', 'Sauvegarde & Restauration')
@section('page-title', 'Sauvegarde & Restauration')

@push('styles')
<style>
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }

    .card {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 16px;
    }
    .card-header {
        padding: 14px 20px; border-bottom: 1px solid #1a1a1a;
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
    }
    .card-header-title {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; font-weight: 700; color: #e5e5e5;
    }
    .card-body { padding: 20px; }

    .kpi {
        background: var(--cc-dark3); border: 1px solid var(--cc-border);
        border-radius: 13px; padding: 1.1rem; text-align: center;
    }
    .kpi-val   { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
    .kpi-label { font-size: 11px; color: #444; }

    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 16px; border-radius: 10px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        transition: all .18s; border: none; font-family: inherit;
        text-decoration: none;
    }
    .btn-primary { background: var(--cc-orange); color: #fff; }
    .btn-primary:hover { background: #c2410c; }
    .btn-primary:disabled { opacity: .5; cursor: not-allowed; }
    .btn-ghost { background: var(--cc-dark3); border: 1px solid var(--cc-border); color: #666; }
    .btn-ghost:hover { color: #ccc; border-color: #333; }
    .btn-sm { padding: 6px 12px; font-size: 11px; border-radius: 8px; }
    .btn-success { background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.25); color: #22c55e; }
    .btn-success:hover { background: #22c55e; color: #fff; }
    .btn-danger { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.25); color: #f87171; }
    .btn-danger:hover { background: #ef4444; color: #fff; }

    /* ── Onglets ── */
    .sv-tabs { display: flex; gap: 8px; margin-bottom: 18px; border-bottom: 1px solid var(--cc-border); overflow-x: auto; }
    .sv-tab {
        padding: 12px 18px; font-size: 13px; font-weight: 600; color: #555;
        background: none; border: none; cursor: pointer; font-family: inherit;
        border-bottom: 2px solid transparent; margin-bottom: -1px; transition: all .18s;
        white-space: nowrap;
    }
    .sv-tab.active { color: var(--cc-orange2); border-bottom-color: var(--cc-orange); }
    .sv-pane { display: none; }
    .sv-pane.active { display: block; }

    /* ── Sélecteurs de scope/format ── */
    .choix-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(150px,1fr)); gap: 10px; }
    .choix-btn {
        padding: 14px; border-radius: 11px; border: 1.5px solid var(--cc-border);
        background: var(--cc-dark2); color: #666; cursor: pointer; font-family: inherit;
        display: flex; flex-direction: column; align-items: center; gap: 8px;
        font-size: 12px; font-weight: 600; transition: all .18s;
    }
    .choix-btn i { font-size: 20px; }
    .choix-btn.active { border-color: var(--cc-orange); background: rgba(234,88,12,.08); color: var(--cc-orange2); }

    .table-check {
        display: flex; align-items: center; gap: 8px; padding: 10px 12px;
        background: var(--cc-dark2); border: 1px solid var(--cc-border); border-radius: 9px;
        font-size: 12px; color: #ccc; cursor: pointer;
    }
    .table-check input { accent-color: var(--cc-orange); }

    .field-label { display: block; font-size: 11px; font-weight: 600; color: #666; margin-bottom: 8px; }

    /* ── Corbeille ── */
    .sel {
        background: var(--cc-dark2); border: 1px solid var(--cc-border);
        border-radius: 10px; padding: 8px 12px; color: #e5e5e5;
        font-size: 12px; outline: none; font-family: inherit;
    }
    .sel:focus { border-color: var(--cc-orange); }

    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th {
        text-align: left; font-size: 10px; font-weight: 600; color: #444;
        text-transform: uppercase; letter-spacing: .5px;
        padding: 10px 14px; border-bottom: 1px solid #1a1a1a;
        white-space: nowrap;
    }
    .data-table td {
        padding: 10px 14px; font-size: 12px; color: #888;
        border-bottom: 1px solid #141414;
    }
    .data-table tr:hover td { background: #171717; }

    /* ── Zone dangereuse ── */
    .danger-zone {
        border: 1.5px solid rgba(239,68,68,.3); background: rgba(239,68,68,.04);
        border-radius: 14px; padding: 20px; margin-top: 24px;
    }
    .danger-zone h3 { color: #f87171; font-size: 14px; margin-bottom: 6px; }
    .danger-zone p { font-size: 12px; color: #999; margin-bottom: 16px; }

    .field-input {
        width: 100%; background: var(--cc-dark2); border: 1px solid var(--cc-border);
        border-radius: 10px; padding: 10px 13px; color: #e5e5e5; font-size: 13px;
        outline: none; font-family: inherit;
    }
    .field-input:focus { border-color: #ef4444; }

    .loading-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,.7); z-index: 9999;
        display: none; align-items: center; justify-content: center; flex-direction: column; gap: 14px;
    }
    .loading-overlay.show { display: flex; }

    @media (max-width: 700px) {
        .kpi-row { grid-template-columns: 1fr 1fr !important; }
        .choix-grid { grid-template-columns: 1fr 1fr; }
        .danger-zone form > div { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE
══════════════════════════════════════════════════════════ --}}
<div style="margin-bottom:18px;">
    <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">
        <i class="fa-solid fa-database" style="color:var(--cc-orange);margin-right:8px;"></i>
        Sauvegarde & Restauration
    </h2>
    <p style="font-size:12px;color:#444;margin:4px 0 0;">
        Export des données, restauration et purge de la corbeille
    </p>
</div>

@if(session('success'))
<div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:12px;
            background:rgba(34,197,94,.07);border:1px solid rgba(34,197,94,.2);color:#22c55e;">
    <i class="fa-solid fa-circle-check" style="margin-right:6px;"></i>{{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:12px;
            background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#f87171;">
    <i class="fa-solid fa-circle-exclamation" style="margin-right:6px;"></i>{{ session('error') }}
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     KPI
══════════════════════════════════════════════════════════ --}}
<div class="kpi-row" style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
    <div class="kpi">
        <div class="kpi-val" style="color:#fff;">{{ $totalActifs }}</div>
        <div class="kpi-label">Enregistrements actifs</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:#eab308;">{{ $totalCorbeille }}</div>
        <div class="kpi-label">Dans la corbeille</div>
    </div>
    <div class="kpi">
        <div class="kpi-val" style="color:var(--cc-orange2);">{{ count($stats) }}</div>
        <div class="kpi-label">Tables gérées</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ONGLETS
══════════════════════════════════════════════════════════ --}}
<div class="sv-tabs">
    <button class="sv-tab active" onclick="changerOnglet('sauvegarde', this)">
        <i class="fa-solid fa-download"></i> Sauvegarde
    </button>
    <button class="sv-tab" onclick="changerOnglet('corbeille', this)">
        <i class="fa-solid fa-trash-can"></i> Corbeille & Restauration
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════
     ONGLET SAUVEGARDE
══════════════════════════════════════════════════════════ --}}
<div id="pane-sauvegarde" class="sv-pane active">
    <form method="POST" action="{{ route('admin.sauvegarde.exporter') }}" id="formExport" target="_blank" onsubmit="return validerExport()">
        @csrf

        {{-- Portée --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fa-solid fa-layer-group" style="color:var(--cc-orange);"></i> 1. Portée de la sauvegarde</div>
            </div>
            <div class="card-body">
                <div class="choix-grid" style="margin-bottom:16px;">
                    <button type="button" class="choix-btn active" data-scope="base_entiere" onclick="choisirScope('base_entiere', this)">
                        <i class="fa-solid fa-database"></i> Toute la base
                    </button>
                    <button type="button" class="choix-btn" data-scope="tables" onclick="choisirScope('tables', this)">
                        <i class="fa-solid fa-table"></i> Tables spécifiques
                    </button>
                </div>
                <input type="hidden" name="scope" id="inputScope" value="base_entiere">

                <div id="selectionTables" style="display:none;">
                    <label class="field-label">Choisissez une ou plusieurs tables</label>
                    <div class="choix-grid">
                        @foreach($stats as $cle => $t)
                        <label class="table-check">
                            <input type="checkbox" name="tables[]" value="{{ $cle }}">
                            {{ $t['label'] }} ({{ $t['actifs'] }})
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtre données --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fa-solid fa-filter" style="color:var(--cc-orange);"></i> 2. Quelles données ?</div>
            </div>
            <div class="card-body">
                <div class="choix-grid">
                    <button type="button" class="choix-btn active" data-actifs="0" onclick="choisirFiltre(false, this)">
                        <i class="fa-solid fa-globe"></i> Toutes les données
                    </button>
                    <button type="button" class="choix-btn" data-actifs="1" onclick="choisirFiltre(true, this)">
                        <i class="fa-solid fa-circle-check"></i> Actives uniquement
                    </button>
                </div>
                <input type="hidden" name="actifs_seuls" id="inputActifsSeuls" value="0">
                <p style="font-size:11px;color:#444;margin-top:10px;">
                    « Actives uniquement » exclut tout ce qui est déjà dans la corbeille (champ <code>void</code> renseigné).
                </p>
            </div>
        </div>

        {{-- Format --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fa-solid fa-file-export" style="color:var(--cc-orange);"></i> 3. Format d'export</div>
            </div>
            <div class="card-body">
                <div class="choix-grid" style="margin-bottom:16px;">
                    <button type="button" class="choix-btn active" data-format="sql" onclick="choisirFormat('sql', this)">
                        <i class="fa-solid fa-server"></i> SQL
                    </button>
                    <button type="button" class="choix-btn" data-format="csv" onclick="choisirFormat('csv', this)">
                        <i class="fa-solid fa-file-csv"></i> CSV
                    </button>
                    <button type="button" class="choix-btn" data-format="pdf" onclick="choisirFormat('pdf', this)">
                        <i class="fa-solid fa-file-pdf"></i> PDF
                    </button>
                </div>
                <input type="hidden" name="format" id="inputFormat" value="sql">

                <label class="table-check" id="optionImages">
                    <input type="checkbox" name="inclure_images" value="1" checked>
                    <i class="fa-solid fa-image" style="color:#60a5fa;"></i>
                    Inclure les images liées (photos plats, logos...) — regroupées dans le fichier ZIP
                </label>
                <p style="font-size:10.5px;color:#333;margin-top:8px;display:none;" id="noteImagesPdf">
                    Non disponible pour le format PDF (rapport texte uniquement).
                </p>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;">
            <i class="fa-solid fa-download"></i> Générer et télécharger la sauvegarde
        </button>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════
     ONGLET CORBEILLE & RESTAURATION
══════════════════════════════════════════════════════════ --}}
<div id="pane-corbeille" class="sv-pane">

    <div class="card">
        <div class="card-header">
            <div class="card-header-title"><i class="fa-solid fa-trash-can" style="color:#eab308;"></i> Éléments supprimés</div>
            <select id="selectTableCorbeille" class="sel" onchange="chargerCorbeille(1)">
                @foreach($stats as $cle => $t)
                @if($t['restaurable'])
                <option value="{{ $cle }}">{{ $t['label'] }} ({{ $t['corbeille'] }})</option>
                @endif
                @endforeach
            </select>
        </div>

        <div style="padding:14px 20px;border-bottom:1px solid #1a1a1a;display:flex;gap:8px;flex-wrap:wrap;">
            <button class="btn btn-success btn-sm" onclick="actionGroupee('restaurer')">
                <i class="fa-solid fa-clock-rotate-left"></i> Restaurer la sélection
            </button>
            <button class="btn btn-danger btn-sm" onclick="actionGroupee('supprimer')">
                <i class="fa-solid fa-trash"></i> Supprimer définitivement la sélection
            </button>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table" id="tableCorbeille">
                <thead><tr><th style="text-align:center;">Chargement...</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>

        <div id="paginationCorbeille" style="padding:14px 20px;border-top:1px solid #1a1a1a;display:flex;justify-content:center;gap:6px;flex-wrap:wrap;"></div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         ZONE DANGEREUSE
    ══════════════════════════════════════════════════════════ --}}
    <div class="danger-zone">
        <h3><i class="fa-solid fa-triangle-exclamation"></i> Vider toute la corbeille</h3>
        <p>
            Supprime <strong>définitivement et irréversiblement</strong> tous les éléments actuellement dans la
            corbeille, toutes tables confondues ({{ $totalCorbeille }} élément(s) au total), ainsi que leurs
            images liées. Cette action ne peut pas être annulée.
        </p>

        <form method="POST" action="{{ route('admin.sauvegarde.vider-corbeille') }}"
              onsubmit="return confirm('Dernière confirmation : vider DÉFINITIVEMENT toute la corbeille ?')">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div>
                    <label class="field-label">Votre mot de passe</label>
                    <input type="password" name="password" required class="field-input" placeholder="••••••••">
                </div>
                <div>
                    <label class="field-label">Tapez « SUPPRIMER » pour confirmer</label>
                    <input type="text" name="confirmation" required class="field-input" placeholder="SUPPRIMER" autocomplete="off">
                </div>
            </div>
            <button type="submit" class="btn btn-danger">
                <i class="fa-solid fa-bomb"></i> Vider définitivement la corbeille
            </button>
        </form>
    </div>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <i class="fa-solid fa-spinner fa-spin" style="font-size:32px;color:var(--cc-orange);"></i>
    <span style="color:#ccc;font-size:13px;">Génération de la sauvegarde en cours...</span>
</div>

@endsection

@push('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

// ══════════════════════════════════════════════════════════════
// ONGLETS
// ══════════════════════════════════════════════════════════════
function changerOnglet(nom, btn) {
    document.querySelectorAll('.sv-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.sv-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('pane-' + nom).classList.add('active');

    if (nom === 'corbeille' && !corbeilleChargeeUneFois) {
        chargerCorbeille(1);
    }
}

// ══════════════════════════════════════════════════════════════
// FORMULAIRE D'EXPORT
// ══════════════════════════════════════════════════════════════
function choisirScope(valeur, btn) {
    document.querySelectorAll('[data-scope]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('inputScope').value = valeur;
    document.getElementById('selectionTables').style.display = valeur === 'tables' ? 'block' : 'none';
}

function choisirFiltre(actifsSeuls, btn) {
    document.querySelectorAll('[data-actifs]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('inputActifsSeuls').value = actifsSeuls ? '1' : '0';
}

function choisirFormat(valeur, btn) {
    document.querySelectorAll('[data-format]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('inputFormat').value = valeur;

    const optionImages  = document.getElementById('optionImages');
    const noteImagesPdf = document.getElementById('noteImagesPdf');
    const estPdf = valeur === 'pdf';

    optionImages.style.display  = estPdf ? 'none' : 'flex';
    noteImagesPdf.style.display = estPdf ? 'block' : 'none';
}

function validerExport() {
    const scope = document.getElementById('inputScope').value;
    if (scope === 'tables') {
        const coche = document.querySelectorAll('input[name="tables[]"]:checked');
        if (coche.length === 0) {
            Swal.fire({
                icon: 'warning', title: 'Aucune table sélectionnée',
                text: 'Choisissez au moins une table à exporter.',
                background: '#141414', color: '#e5e5e5', confirmButtonColor: '#ea580c',
            });
            return false;
        }
    }

    document.getElementById('loadingOverlay').classList.add('show');
    setTimeout(() => document.getElementById('loadingOverlay').classList.remove('show'), 4000);
    return true;
}

// ══════════════════════════════════════════════════════════════
// CORBEILLE
// ══════════════════════════════════════════════════════════════
let corbeilleChargeeUneFois = false;
let corbeilleColonnes = [];
let corbeillePk = 'id';

async function chargerCorbeille(page) {
    corbeilleChargeeUneFois = true;
    const table = document.getElementById('selectTableCorbeille').value;

    const tbody = document.querySelector('#tableCorbeille tbody');
    const thead = document.querySelector('#tableCorbeille thead');
    thead.innerHTML = '<tr><th style="text-align:center;">Chargement...</th></tr>';
    tbody.innerHTML = '';

    try {
        const res  = await fetch(`{{ route('admin.sauvegarde.corbeille') }}?table=${table}&page=${page}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (!data.success) {
            thead.innerHTML = `<tr><th style="text-align:center;color:#f87171;">${data.message}</th></tr>`;
            return;
        }

        corbeilleColonnes = data.affichage;
        corbeillePk       = data.pk;

        thead.innerHTML = `
            <tr>
                <th><input type="checkbox" onclick="toutSelectionner(this)"></th>
                ${corbeilleColonnes.map(c => `<th>${c}</th>`).join('')}
                <th></th>
            </tr>`;

        if (data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${corbeilleColonnes.length + 2}" style="text-align:center;padding:30px;color:#2a2a2a;">
                <i class="fa-solid fa-circle-check" style="color:#22c55e;font-size:22px;display:block;margin-bottom:8px;"></i>
                Corbeille vide pour cette table
            </td></tr>`;
        } else {
            tbody.innerHTML = data.data.map(ligne => `
                <tr>
                    <td><input type="checkbox" class="chk-ligne" value="${ligne[corbeillePk]}"></td>
                    ${corbeilleColonnes.map(c => `<td>${ligne[c] ?? '—'}</td>`).join('')}
                    <td style="white-space:nowrap;">
                        <button class="btn btn-success btn-sm" onclick="restaurerUn(${ligne[corbeillePk]})" title="Restaurer">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="supprimerUn(${ligne[corbeillePk]})" title="Supprimer définitivement">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        renderPagination(data.pagination, page);

    } catch (e) {
        thead.innerHTML = '<tr><th style="text-align:center;color:#f87171;">Erreur de chargement</th></tr>';
        console.error(e);
    }
}

function renderPagination(p, pageActuelle) {
    const el = document.getElementById('paginationCorbeille');
    if (p.last_page <= 1) { el.innerHTML = ''; return; }

    let html = '';
    for (let i = 1; i <= p.last_page; i++) {
        html += `<button class="btn btn-sm ${i === pageActuelle ? 'btn-primary' : 'btn-ghost'}" onclick="chargerCorbeille(${i})">${i}</button>`;
    }
    el.innerHTML = html;
}

function toutSelectionner(source) {
    document.querySelectorAll('.chk-ligne').forEach(chk => chk.checked = source.checked);
}

function idsSelectionnes() {
    return [...document.querySelectorAll('.chk-ligne:checked')].map(chk => chk.value);
}

async function envoyerAction(url, method, table, ids) {
    const res = await fetch(url, {
        method: method,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        body: JSON.stringify({ table, ids }),
    });
    return res.json();
}

async function actionGroupee(type) {
    const ids = idsSelectionnes();
    if (ids.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Aucune sélection', background: '#141414', color: '#e5e5e5', confirmButtonColor: '#ea580c' });
        return;
    }

    const table = document.getElementById('selectTableCorbeille').value;
    const estSuppression = type === 'supprimer';

    const confirmation = await Swal.fire({
        title: estSuppression ? `Supprimer ${ids.length} élément(s) définitivement ?` : `Restaurer ${ids.length} élément(s) ?`,
        html: estSuppression ? '<div style="color:#666;font-size:13px;">Action irréversible, y compris les images liées.</div>' : '',
        icon: 'warning', iconColor: estSuppression ? '#ef4444' : '#22c55e',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: estSuppression ? '#ef4444' : '#22c55e',
        confirmButtonText: 'Confirmer', showCancelButton: true, cancelButtonText: 'Annuler', cancelButtonColor: '#1f1f1f',
    });

    if (!confirmation.isConfirmed) return;

    const url = estSuppression
        ? '{{ route("admin.sauvegarde.supprimer") }}'
        : '{{ route("admin.sauvegarde.restaurer") }}';
    const method = estSuppression ? 'DELETE' : 'PATCH';

    const data = await envoyerAction(url, method, table, ids);

    Swal.fire({
        toast: true, position: 'bottom-end',
        icon: data.success ? 'success' : 'error', title: data.message,
        timer: 2500, showConfirmButton: false,
        background: '#141414', color: '#e5e5e5',
    });

    if (data.success) chargerCorbeille(1);
}

function restaurerUn(id) { executerUnitaire('restaurer', id); }
function supprimerUn(id) { executerUnitaire('supprimer', id); }

async function executerUnitaire(type, id) {
    const table = document.getElementById('selectTableCorbeille').value;
    const estSuppression = type === 'supprimer';

    if (estSuppression) {
        const confirmation = await Swal.fire({
            title: 'Supprimer définitivement ?',
            html: '<div style="color:#666;font-size:13px;">Action irréversible.</div>',
            icon: 'warning', iconColor: '#ef4444',
            background: '#141414', color: '#e5e5e5',
            confirmButtonColor: '#ef4444', confirmButtonText: 'Supprimer', showCancelButton: true, cancelButtonColor: '#1f1f1f',
        });
        if (!confirmation.isConfirmed) return;
    }

    const url = estSuppression
        ? '{{ route("admin.sauvegarde.supprimer") }}'
        : '{{ route("admin.sauvegarde.restaurer") }}';
    const method = estSuppression ? 'DELETE' : 'PATCH';

    const data = await envoyerAction(url, method, table, [id]);

    Swal.fire({
        toast: true, position: 'bottom-end',
        icon: data.success ? 'success' : 'error', title: data.message,
        timer: 2000, showConfirmButton: false,
        background: '#141414', color: '#e5e5e5',
    });

    if (data.success) chargerCorbeille(1);
}
</script>
@endpush