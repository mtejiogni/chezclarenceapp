@extends('layouts.app')

@section('title', 'Modifier ' . $commande->reference)
@section('page-title', 'Modifier ma commande')

@push('styles')
<style>
    :root {
        --cc-orange:  #ea580c;
        --cc-orange2: #f97316;
        --cc-dark2:   #0d0d0d;
        --cc-dark3:   #141414;
        --cc-border:  #1f1f1f;
    }
    [x-cloak] { display: none !important; }

    .card {
        background: var(--cc-dark3);
        border: 1px solid var(--cc-border);
        border-radius: 14px;
        overflow: hidden;
    }
    .card-header {
        padding: 14px 20px; border-bottom: 1px solid #1a1a1a;
        display: flex; align-items: center; justify-content: space-between;
    }
    .card-header-title {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; font-weight: 700; color: #e5e5e5;
    }
    .card-body { padding: 18px 20px; }

    .field-label {
        display: block; font-size: 11px; font-weight: 600; color: #666; margin-bottom: 8px;
    }
    .field-input {
        width: 100%; background: var(--cc-dark2); border: 1px solid var(--cc-border);
        border-radius: 10px; padding: 10px 13px; color: #e5e5e5; font-size: 13px;
        outline: none; font-family: inherit; transition: border-color .18s;
    }
    .field-input:focus { border-color: var(--cc-orange); }
    .field-error { font-size: 11px; color: #f87171; margin-top: 6px; }

    .type-btn {
        padding: 12px; border-radius: 10px; border: 1px solid var(--cc-border);
        background: var(--cc-dark2); color: #666; font-size: 12.5px; font-weight: 600;
        cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: all .18s; font-family: inherit;
    }
    .type-btn.active { background: rgba(234,88,12,.1); border-color: var(--cc-orange); color: var(--cc-orange2); }

    .cat-chip {
        padding: 6px 14px; border-radius: 20px; border: 1px solid var(--cc-border);
        background: var(--cc-dark3); color: #666; font-size: 11px; font-weight: 500;
        cursor: pointer; transition: all .18s; font-family: inherit;
    }
    .cat-chip.active { background: var(--cc-orange); border-color: var(--cc-orange); color: #fff; }

    .menu-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(190px,1fr)); gap: 10px;
    }
    .menu-card {
        background: var(--cc-dark3); border: 1px solid var(--cc-border); border-radius: 12px;
        padding: 14px; display: flex; align-items: flex-start; gap: 10px;
    }
    .btn-add {
        width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
        background: var(--cc-orange); border: none; color: #fff; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: background .18s;
    }
    .btn-add:hover { background: #c2410c; }

    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 16px; border-radius: 10px;
        font-size: 12.5px; font-weight: 700; cursor: pointer;
        transition: all .18s; border: none; font-family: inherit;
        text-decoration: none;
    }
    .btn-primary { background: var(--cc-orange); color: #fff; }
    .btn-primary:hover { background: #c2410c; }
    .btn-primary:disabled { opacity: .5; cursor: not-allowed; }
    .btn-ghost { background: var(--cc-dark3); border: 1px solid var(--cc-border); color: #666; }
    .btn-ghost:hover { color: #ccc; border-color: #333; }

    .panier-item {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 10px 0; border-bottom: 1px solid #1a1a1a;
    }
    .panier-item:last-child { border-bottom: none; }
    .qte-btn {
        width: 24px; height: 24px; border-radius: 6px; border: 1px solid var(--cc-border);
        background: var(--cc-dark2); color: #ccc; cursor: pointer; font-weight: 700;
        display: flex; align-items: center; justify-content: center; font-family: inherit;
    }
    .qte-btn:hover { border-color: var(--cc-orange); color: var(--cc-orange2); }

    .commande-grid { display: grid; grid-template-columns: 1fr 340px; gap: 18px; align-items: start; }
    .panier-sticky { position: sticky; top: 16px; }

    @media (max-width: 900px) {
        .commande-grid { grid-template-columns: 1fr; }
        .panier-sticky { position: static; }
    }
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
    <a href="{{ route('mes-commandes.show', $commande->idcommande) }}"
       style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;
              background:var(--cc-dark3);border:1px solid var(--cc-border);color:#555;text-decoration:none;">
        <i class="fa-solid fa-arrow-left" style="font-size:13px;"></i>
    </a>
    <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">
        <i class="fa-solid fa-pen" style="color:var(--cc-orange);margin-right:8px;"></i>
        Modifier {{ $commande->reference }}
    </h2>
</div>

<div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:11.5px;
            background:rgba(234,179,8,.06);border:1px solid rgba(234,179,8,.18);color:#eab308;">
    <i class="fa-solid fa-circle-info" style="margin-right:6px;"></i>
    Modifiable tant que votre commande est « En attente ». Toute modification remplace entièrement votre panier précédent.
</div>

@if ($errors->any())
<div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:12px;
            background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#f87171;">
    <ul style="margin:0;padding-left:18px;">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@include('clientcommande._form', ['mode' => 'edit'])

@endsection

@push('scripts')
<script>
function commandeClientForm(panierInitial) {
    return {
        type: '{{ $typeInitial ?? "A emporter" }}',
        adresse: @js($adresseInitial ?? ''),
        consignes: @js($consignesInitial ?? ''),
        categorieActive: 'toutes',
        recherche: '',
        menus: [],
        panier: panierInitial ?? [],
        chargement: true,

        init() {
            this.chargerMenus();
        },

        async chargerMenus() {
            this.chargement = true;
            try {
                const res  = await fetch('{{ route("mes-commandes.menu.liste") }}', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.menus = data.success ? data.data : [];
            } catch (e) {
                console.error('Erreur chargement des plats :', e);
            }
            this.chargement = false;
        },

        get menusFiltres() {
            return this.menus.filter(m => {
                const matchCat = this.categorieActive === 'toutes' || m.categorie === this.categorieActive;
                const matchQ = !this.recherche || m.intitule.toLowerCase().includes(this.recherche.toLowerCase());
                return matchCat && matchQ;
            });
        },

        ajouterAuPanier(menu) {
            const existant = this.panier.find(p => p.idmenu === menu.idmenu);
            if (existant) {
                existant.quantite++;
            } else {
                this.panier.push({ idmenu: menu.idmenu, intitule: menu.intitule, pu: menu.pu, quantite: 1 });
            }
        },

        retirerDuPanier(idmenu) {
            this.panier = this.panier.filter(p => p.idmenu !== idmenu);
        },

        changerQuantite(idmenu, delta) {
            const item = this.panier.find(p => p.idmenu === idmenu);
            if (!item) return;
            item.quantite += delta;
            if (item.quantite <= 0) this.retirerDuPanier(idmenu);
        },

        get total() {
            return this.panier.reduce((sum, p) => sum + (p.pu * p.quantite), 0);
        },

        validerAvantEnvoi() {
            if (this.panier.length === 0) {
                Swal.fire({
                    icon: 'warning', title: 'Panier vide',
                    text: 'Ajoutez au moins un plat avant de valider.',
                    background: '#141414', color: '#e5e5e5', confirmButtonColor: '#ea580c',
                });
                return false;
            }
            if (this.type === 'Livraison' && !this.adresse.trim()) {
                Swal.fire({
                    icon: 'warning', title: 'Adresse manquante',
                    text: 'Indiquez votre adresse de livraison.',
                    background: '#141414', color: '#e5e5e5', confirmButtonColor: '#ea580c',
                });
                return false;
            }
            return true;
        },
    };
}
</script>
@endpush