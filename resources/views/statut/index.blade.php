@extends('layouts.app')

@section('title', 'Statuts')
@section('page-title', 'Gestion des Statuts')

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
    }

    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 16px; border-radius: 10px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        transition: all .18s; border: none; font-family: inherit;
        text-decoration: none;
    }
    .btn-primary { background: var(--cc-orange); color: #fff; }
    .btn-primary:hover { background: #c2410c; }
    .btn-ghost {
        background: var(--cc-dark3); border: 1px solid var(--cc-border); color: #555;
    }
    .btn-ghost:hover { color: #ccc; border-color: #333; }
    .btn-sm { padding: 6px 10px; font-size: 11px; border-radius: 8px; }

    .badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
    }
    .badge-neutre { background: #1a1a1a; color: #666; }
    .badge-systeme { background: #1a1a1a; color: #555; font-size: 9px; text-transform: uppercase; letter-spacing: .5px; }

    .statut-row {
        display: flex; align-items: center; gap: 14px;
        padding: 14px 20px;
        border-bottom: 1px solid #1a1a1a;
        transition: background .15s;
        cursor: grab;
    }
    .statut-row:last-child { border-bottom: none; }
    .statut-row:hover { background: #181818; }
    .statut-row:active { cursor: grabbing; }
    .statut-row.dragging { opacity: .35; }

    .statut-icon {
        width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
    }

    .statut-link {
        font-size: 13px; font-weight: 700; color: #e5e5e5;
        text-decoration: none; transition: color .15s;
    }
    .statut-link:hover { color: var(--cc-orange2); }

    .icon-btn {
        width: 32px; height: 32px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        background: #1a1a1a; border: 1px solid var(--cc-border); color: #555;
        transition: all .15s; cursor: pointer;
    }
    .icon-btn:hover { color: #ccc; border-color: #333; }
    .icon-btn.info:hover {
        color: #60a5fa; border-color: rgba(96,165,250,.3); background: rgba(96,165,250,.08);
    }
    .icon-btn.danger:hover {
        color: #f87171; border-color: rgba(239,68,68,.3); background: rgba(239,68,68,.08);
    }
    .icon-btn.disabled {
        color: #2a2a2a; cursor: not-allowed; background: #0d0d0d;
    }
    .icon-btn.disabled:hover { color: #2a2a2a; border-color: var(--cc-border); background: #0d0d0d; }

    @media (max-width: 700px) {
        .statut-row { flex-wrap: wrap; }
        .statut-row .statut-meta { order: 3; width: 100%; margin-top: 8px; }
    }
</style>
@endpush

@section('content')

@php
    // [NOTE] même liste que celle codée en dur dans
    // StatutController::edit()/destroy() — utilisée ici uniquement
    // pour l'affichage (griser le bouton supprimer). Idéalement à
    // centraliser côté contrôleur/modèle plutôt que dupliquée.
    $statutsSysteme = ['En attente', 'En préparation', 'Expédiée', 'Livrée', 'Servie', 'Annulée'];
@endphp

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;justify-content:space-between;
            flex-wrap:wrap;gap:12px;margin-bottom:18px;">
    <div>
        <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">
            <i class="fa-solid fa-list-check" style="color:var(--cc-orange);margin-right:8px;"></i>
            Statuts de commande
        </h2>
        <p style="font-size:12px;color:#444;margin:4px 0 0;">
            Glissez-déposez pour réordonner le workflow · {{ $statuts->count() }} statut(s)
        </p>
    </div>
    <a href="{{ route('admin.statuts.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Nouveau statut
    </a>
</div>

{{-- ══════════════════════════════════════════════════════════
     MESSAGES FLASH
══════════════════════════════════════════════════════════ --}}
@if(session('success'))
<div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:12px;
            background:rgba(34,197,94,.07);border:1px solid rgba(34,197,94,.2);color:#22c55e;">
    <i class="fa-solid fa-circle-check" style="margin-right:6px;"></i>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:12px;
            background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#f87171;">
    <i class="fa-solid fa-circle-exclamation" style="margin-right:6px;"></i>
    {{ session('error') }}
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     LISTE RÉORDONNABLE
══════════════════════════════════════════════════════════ --}}
<div class="card">
    <div id="liste-statuts">
        @forelse($statuts as $statut)
        @php
            $cfg = $config[$statut->intitule] ?? ['text' => '#9ca3af', 'bg' => 'rgba(156,163,175,.12)', 'icone' => 'fa-circle'];
            $estSysteme = in_array($statut->intitule, $statutsSysteme);
        @endphp
        <div class="statut-row" draggable="true" data-id="{{ $statut->idstatut }}">

            <i class="fa-solid fa-grip-vertical" style="color:#333;font-size:12px;"></i>

            <div class="statut-icon" style="background:{{ $cfg['bg'] }};">
                <i class="fa-solid {{ $cfg['icone'] }}" style="color:{{ $cfg['text'] }};font-size:15px;"></i>
            </div>

            <div style="flex:1;min-width:0;">
                <a href="{{ route('admin.statuts.show', $statut->idstatut) }}" class="statut-link">
                    {{ $statut->intitule }}
                </a>
                @if($estSysteme)
                <span class="badge badge-systeme" style="margin-left:6px;">
                    <i class="fa-solid fa-lock" style="font-size:8px;"></i> Système
                </span>
                @endif
                @if($statut->description)
                <div style="font-size:11px;color:#555;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:420px;">
                    {{ $statut->description }}
                </div>
                @endif
            </div>

            <div class="statut-meta" style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                <span class="badge badge-neutre" title="Commandes actuellement à ce statut">
                    <i class="fa-solid fa-receipt" style="font-size:10px;"></i>{{ $statut->nb_commandes_actives }}
                </span>
                <span class="badge badge-neutre">
                    Priorité {{ $statut->priorite }}
                </span>

                <a href="{{ route('admin.statuts.show', $statut->idstatut) }}" class="icon-btn info" title="Voir le détail">
                    <i class="fa-solid fa-eye" style="font-size:11px;"></i>
                </a>

                <a href="{{ route('admin.statuts.edit', $statut->idstatut) }}" class="icon-btn info" title="Modifier">
                    <i class="fa-solid fa-pen" style="font-size:11px;"></i>
                </a>

                @if($estSysteme)
                <span class="icon-btn disabled" title="Statut système — suppression impossible">
                    <i class="fa-solid fa-trash" style="font-size:11px;"></i>
                </span>
                @else
                <button onclick="confirmerSuppression({{ $statut->idstatut }}, '{{ addslashes($statut->intitule) }}')"
                        class="icon-btn danger" title="Supprimer">
                    <i class="fa-solid fa-trash" style="font-size:11px;"></i>
                </button>
                @endif
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:60px 16px;color:#2a2a2a;">
            <i class="fa-solid fa-list-check" style="font-size:30px;display:block;margin-bottom:10px;"></i>
            <p style="font-size:13px;">Aucun statut configuré</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Formulaire caché pour la suppression --}}
<form method="POST" id="deleteForm" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
const liste = document.getElementById('liste-statuts');
let elementGlisse = null;

liste.querySelectorAll('.statut-row').forEach(row => {
    row.addEventListener('dragstart', () => {
        elementGlisse = row;
        row.classList.add('dragging');
    });

    row.addEventListener('dragend', () => {
        row.classList.remove('dragging');
        enregistrerOrdre();
    });

    row.addEventListener('dragover', (e) => {
        e.preventDefault();
        if (!elementGlisse || elementGlisse === row) return;

        const rect  = row.getBoundingClientRect();
        const apres = (e.clientY - rect.top) > rect.height / 2;

        row.parentNode.insertBefore(elementGlisse, apres ? row.nextSibling : row);
    });
});

async function enregistrerOrdre() {
    const ordre = [...liste.querySelectorAll('.statut-row')].map(row => row.dataset.id);

    try {
        const res  = await fetch('{{ route("admin.statuts.reordonner") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({ ordre }),
        });
        const data = await res.json();

        if (data.success) {
            liste.querySelectorAll('.statut-row').forEach((row, index) => {
                const badges = row.querySelectorAll('.badge-neutre');
                const badgePriorite = [...badges].find(b => b.textContent.includes('Priorité'));
                if (badgePriorite) badgePriorite.textContent = 'Priorité ' + (index + 1);
            });

            Swal.fire({
                toast: true, position: 'bottom-end', icon: 'success',
                title: 'Ordre mis à jour', timer: 1500, showConfirmButton: false,
                background: '#141414', color: '#e5e5e5', iconColor: '#22c55e',
            });
        }
    } catch (e) {
        console.error('Erreur réordonnancement :', e);
    }
}

function confirmerSuppression(id, intitule) {
    Swal.fire({
        title: `Supprimer « ${intitule} » ?`,
        html: '<div style="color:#666;font-size:13px;">Impossible si des commandes ont actuellement ce statut.</div>',
        icon: 'warning', iconColor: '#ef4444',
        background: '#141414', color: '#e5e5e5',
        confirmButtonColor: '#ef4444', confirmButtonText: 'Oui, supprimer',
        showCancelButton: true, cancelButtonText: 'Annuler', cancelButtonColor: '#1f1f1f',
    }).then(r => {
        if (r.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = `/admin/statuts/${id}`;
            form.submit();
        }
    });
}
</script>
@endpush