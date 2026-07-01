@extends('layouts.app')

@section('title', 'Livraisons')
@section('page-title', 'Suivi des Livraisons')

@section('content')

<div class="flex items-center justify-between flex-wrap gap-3 mb-5">
    <div>
        <h2 class="text-lg font-bold text-white m-0">
            <i class="fa-solid fa-motorcycle text-orange-600 mr-2"></i>
            Suivi des Livraisons
        </h2>
        <p class="text-xs text-neutral-500 mt-1">
            <span class="text-green-500" id="nb-livrees">{{ $livreesAujourdhui }}</span>
            livraison(s) terminée(s) aujourd'hui ·
            <span id="last-refresh">chargement...</span>
        </p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('livraisons.historique') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold
                  bg-neutral-900 border border-neutral-800 text-neutral-400 hover:text-neutral-200 hover:border-neutral-700 transition">
            <i class="fa-solid fa-clock-rotate-left"></i> Historique
        </a>
        <div class="flex items-center gap-2 text-[11px] text-neutral-500">
            <span class="w-2 h-2 rounded-full bg-green-500 inline-block animate-pulse"></span>
            Actualisation automatique
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

    {{-- ═══════════════════════════════
         COLONNE EN ATTENTE (info, géré par la cuisine)
    ═══════════════════════════════ --}}
    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl overflow-hidden">
        <div class="px-4 py-3.5 flex items-center justify-between border-b border-neutral-800">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-yellow-500">
                <i class="fa-solid fa-clock"></i> En attente
            </div>
            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-yellow-500/15 text-yellow-500" id="count-attente">
                {{ $enAttente->count() }}
            </span>
        </div>
        <div class="p-3.5 flex flex-col gap-2.5" id="col-attente">
            <div class="h-20 rounded-xl bg-neutral-950 animate-pulse"></div>
        </div>
    </div>

    {{-- ═══════════════════════════════
         COLONNE EN PRÉPARATION (info, géré par la cuisine)
    ═══════════════════════════════ --}}
    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl overflow-hidden">
        <div class="px-4 py-3.5 flex items-center justify-between border-b border-neutral-800">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-blue-400">
                <i class="fa-solid fa-fire-burner"></i> En préparation
            </div>
            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-blue-400/15 text-blue-400" id="count-preparation">
                {{ $enPreparation->count() }}
            </span>
        </div>
        <div class="p-3.5 flex flex-col gap-2.5" id="col-preparation">
            <div class="h-20 rounded-xl bg-neutral-950 animate-pulse"></div>
        </div>
    </div>

    {{-- ═══════════════════════════════
         COLONNE EN ROUTE (actionnable)
    ═══════════════════════════════ --}}
    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl overflow-hidden">
        <div class="px-4 py-3.5 flex items-center justify-between border-b border-neutral-800">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-orange-500">
                <i class="fa-solid fa-motorcycle"></i> En route
            </div>
            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-orange-500/15 text-orange-500" id="count-route">
                {{ $enRoute->count() }}
            </span>
        </div>
        <div class="p-3.5 flex flex-col gap-2.5" id="col-route">
            <div class="h-20 rounded-xl bg-neutral-950 animate-pulse"></div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════════════════════
// SUIVI LIVRAISONS — rendu et rafraîchissement 100% asynchrones.
// Le chargement initial ET les actualisations périodiques passent
// tous les deux par livraisons.statut-temps-reel (JSON) : une
// seule implémentation du rendu, aucun rechargement de page.
// ══════════════════════════════════════════════════════════════

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
const REFRESH_MS = 12000; // 12s

function empty(msg) {
    return `
        <div class="text-center py-8 text-neutral-800">
            <i class="fa-solid fa-circle-check text-2xl block mb-2 text-green-500"></i>
            <p class="text-sm">${msg}</p>
        </div>
    `;
}

function ligneCommande(cmd) {
    return cmd.lignes.map(l => `
        <div class="flex justify-between text-[11px] text-neutral-400 py-0.5">
            <span>${l.intitule}</span>
            <span class="font-bold text-orange-500">×${l.quantite}</span>
        </div>
    `).join('');
}

function clientBloc(cmd, taille = 'xs') {
    if (!cmd.client) return '';
    const tel = cmd.client.telephone
        ? `<div class="text-xs text-neutral-500 mb-1">
               <i class="fa-solid fa-phone text-[10px] mr-1"></i>
               <a href="tel:${cmd.client.telephone}" class="text-orange-500 hover:underline">${cmd.client.telephone}</a>
           </div>`
        : '';
    return `
        <div class="text-${taille} text-neutral-300 font-semibold mb-1">
            <i class="fa-solid fa-user text-[10px] mr-1"></i> ${cmd.client.nom}
        </div>
        ${tel}
    `;
}

function cardInfo(cmd, badgeLabel) {
    return `
        <div class="bg-black border border-neutral-800 border-l-2 border-l-neutral-700 rounded-xl p-3">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-sm font-bold text-neutral-200">${cmd.reference}</span>
                <span class="text-[11px] text-neutral-500">${cmd.heurecommande ?? ''}</span>
            </div>
            ${clientBloc(cmd)}
            <div class="text-[11px] text-neutral-600">
                ${cmd.lignes.reduce((n, l) => n + l.quantite, 0)} article(s) ·
                ${Math.round(cmd.montant).toLocaleString('fr-FR')} FCFA
            </div>
            <div class="text-[10px] text-neutral-600 mt-2 italic">
                <i class="fa-solid fa-fire-burner mr-1"></i> ${badgeLabel}
            </div>
        </div>
    `;
}

function minutesEcoulees(heureStr) {
    const [h, m] = heureStr.split(':').map(Number);
    const cmdDate = new Date();
    cmdDate.setHours(h, m, 0, 0);
    return Math.max(0, Math.round((Date.now() - cmdDate.getTime()) / 60000));
}

function cardRoute(cmd) {
    const minutes = cmd.heurecommande ? minutesEcoulees(cmd.heurecommande) : 0;
    const urgent  = minutes >= 30;
    const adresse = cmd.adresse
        ? `<div class="text-xs text-neutral-400 mb-2 flex items-start gap-1">
               <i class="fa-solid fa-location-dot text-[10px] mt-0.5 text-neutral-600"></i>
               <span>${cmd.adresse}</span>
           </div>`
        : '';

    return `
        <div class="bg-black border border-neutral-800 rounded-xl p-3 ${urgent ? 'border-l-2 border-l-red-500' : 'border-l-2 border-l-orange-500'}"
             data-id="${cmd.idcommande}">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-sm font-bold text-neutral-200">${cmd.reference}</span>
                <span class="text-[11px] ${urgent ? 'text-red-500 font-bold' : 'text-neutral-500'}">
                    <i class="fa-solid fa-stopwatch"></i> ${minutes} min
                </span>
            </div>
            ${clientBloc(cmd)}
            ${adresse}
            <div class="text-[11px] text-neutral-600 mb-3">
                ${cmd.lignes.reduce((n, l) => n + l.quantite, 0)} article(s) ·
                ${Math.round(cmd.montant).toLocaleString('fr-FR')} FCFA
            </div>
            <button class="w-full py-2.5 rounded-lg bg-green-600 hover:bg-green-500 text-white text-xs font-bold
                           flex items-center justify-center gap-2 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    data-id="${cmd.idcommande}" data-statut="Livrée" onclick="changerStatut(this)">
                <i class="fa-solid fa-check"></i> Marquer livrée
            </button>
            <button class="w-full mt-2 py-2 rounded-lg bg-transparent border border-red-500/30 text-red-500
                           hover:bg-red-500/10 text-[11px] font-semibold flex items-center justify-center gap-2 transition"
                    data-id="${cmd.idcommande}" onclick="annulerLivraison(this)">
                <i class="fa-solid fa-triangle-exclamation"></i> Signaler un problème
            </button>
        </div>
    `;
}

function renderColonne(id, countId, commandes, renderFn, emptyMsg) {
    document.getElementById(countId).textContent = commandes.length;
    document.getElementById(id).innerHTML = commandes.length
        ? commandes.map(renderFn).join('')
        : empty(emptyMsg);
}

// ── Chargement + actualisation (même fonction pour les deux) ───
async function actualiser() {
    try {
        const res  = await fetch('{{ route("livraisons.statut-temps-reel") }}', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (!data.success) return;

        renderColonne('col-attente', 'count-attente', data.en_attente,
            cmd => cardInfo(cmd, 'En attente côté cuisine'), 'Rien en attente');

        renderColonne('col-preparation', 'count-preparation', data.en_preparation,
            cmd => cardInfo(cmd, 'En cours de préparation'), 'Rien en préparation');

        renderColonne('col-route', 'count-route', data.en_route,
            cardRoute, 'Aucune livraison en route');

        document.getElementById('nb-livrees').textContent = data.livrees_aujourdhui;
        document.getElementById('last-refresh').textContent = 'actualisé à ' + data.timestamp;

    } catch (e) {
        console.error('Erreur actualisation livraisons :', e);
        document.getElementById('last-refresh').textContent = 'erreur de connexion';
    }
}

// ── Actions ──────────────────────────────────────────────────
async function envoyerStatut(idcommande, statut, description = null) {
    const res = await fetch(`/livraisons/${idcommande}/statut`, {
        method: 'PATCH',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        body: JSON.stringify({ statut, description }),
    });
    return res.json();
}

async function changerStatut(btn) {
    const idcommande = btn.dataset.id;
    const statut     = btn.dataset.statut;

    btn.disabled = true;
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> ...';

    try {
        const data = await envoyerStatut(idcommande, statut);

        if (!data.success) {
            Swal.fire({
                toast: true, position: 'bottom-end', icon: 'error',
                title: data.message, timer: 2500, showConfirmButton: false,
                background: '#141414', color: '#e5e5e5', iconColor: '#ef4444',
            });
            btn.disabled = false;
            btn.innerHTML = original;
            return;
        }

        // Re-synchronisation immédiate : la carte disparaît de "En route"
        // sans jamais recharger la page.
        actualiser();

    } catch (e) {
        console.error('Erreur changement de statut :', e);
        btn.disabled = false;
        btn.innerHTML = original;
    }
}

async function annulerLivraison(btn) {
    const idcommande = btn.dataset.id;

    const { value: motif } = await Swal.fire({
        title: 'Signaler un problème',
        input: 'textarea',
        inputLabel: 'Motif (obligatoire)',
        inputPlaceholder: 'Ex : client injoignable, adresse erronée, colis refusé...',
        showCancelButton: true,
        confirmButtonText: 'Confirmer l\'annulation',
        cancelButtonText: 'Retour',
        confirmButtonColor: '#ef4444',
        background: '#141414', color: '#e5e5e5',
        inputValidator: (value) => {
            if (!value || value.trim().length < 5) {
                return 'Le motif doit contenir au moins 5 caractères.';
            }
        },
    });

    if (!motif) return;

    btn.disabled = true;
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> ...';

    try {
        const data = await envoyerStatut(idcommande, 'Annulée', motif);

        if (!data.success) {
            Swal.fire({
                toast: true, position: 'bottom-end', icon: 'error',
                title: data.message, timer: 2500, showConfirmButton: false,
                background: '#141414', color: '#e5e5e5', iconColor: '#ef4444',
            });
            btn.disabled = false;
            btn.innerHTML = original;
            return;
        }

        actualiser();

    } catch (e) {
        console.error('Erreur annulation livraison :', e);
        btn.disabled = false;
        btn.innerHTML = original;
    }
}

// ── Démarrage ────────────────────────────────────────────────
actualiser();
setInterval(actualiser, REFRESH_MS);
</script>
@endpush