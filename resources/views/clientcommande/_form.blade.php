@php
    $mode = $mode ?? 'create';
    $typeInitial = old('typecommande', $commande->typecommande ?? 'A emporter');
    $adresseInitial = old('adresse', $commande->adresse ?? '');
    $consignesInitial = old('consignes', $commande->consignes ?? '');

    $panierInitialData = ($panierInitial ?? null)
        ?? (isset($commande)
            ? $commande->lignes->map(fn ($l) => [
                'idmenu'   => $l->menu->idmenu ?? $l->idmenu,
                'intitule' => $l->menu->intitule ?? 'Plat supprimé',
                'pu'       => $l->menu->pu ?? 0,
                'quantite' => $l->quantite,
            ])->values()
            : collect());
@endphp

<div x-data="commandeClientForm(@js($panierInitialData))"
     x-init="init()">

    <form method="POST"
          action="{{ $mode === 'edit' ? route('mes-commandes.update', $commande->idcommande) : route('mes-commandes.store') }}"
          @submit="if (!validerAvantEnvoi()) { $event.preventDefault(); }"
          id="formCommandeClient">
        @csrf
        @if($mode === 'edit')
        @method('PUT')
        @endif

        <div class="commande-grid">

            {{-- ══════════════════════════════════════════════
                 COLONNE GAUCHE — SÉLECTEUR DE PLATS
            ══════════════════════════════════════════════ --}}
            <div>
                {{-- Type de commande --}}
                <div class="card" style="margin-bottom:16px;">
                    <div class="card-body">
                        <label class="field-label">Type de commande</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                            <button type="button" @click="type = 'A emporter'"
                                    :class="type === 'A emporter' ? 'type-btn active' : 'type-btn'">
                                <i class="fa-solid fa-bag-shopping"></i> À emporter
                            </button>
                            <button type="button" @click="type = 'Livraison'"
                                    :class="type === 'Livraison' ? 'type-btn active' : 'type-btn'">
                                <i class="fa-solid fa-motorcycle"></i> Livraison
                            </button>
                        </div>
                        <input type="hidden" name="typecommande" x-model="type">

                        <div x-show="type === 'Livraison'" x-cloak>
                            <label class="field-label">Adresse de livraison <span style="color:#f87171;">*</span></label>
                            <textarea name="adresse" x-model="adresse" rows="2" maxlength="300"
                                      placeholder="Quartier, repère, numéro de téléphone si différent..."
                                      class="field-input" style="resize:none;"></textarea>
                            @error('adresse')
                            <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Recherche + catégories --}}
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;align-items:center;">
                    <input type="text" x-model="recherche" placeholder="Rechercher un plat..."
                           class="field-input" style="flex:1;min-width:180px;">
                </div>
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;">
                    <button type="button" @click="categorieActive = 'toutes'"
                            :class="categorieActive === 'toutes' ? 'cat-chip active' : 'cat-chip'">Toutes</button>
                    @foreach($categories as $cat)
                    <button type="button" @click="categorieActive = '{{ addslashes($cat->intitule) }}'"
                            :class="categorieActive === '{{ addslashes($cat->intitule) }}' ? 'cat-chip active' : 'cat-chip'">
                        {{ $cat->intitule }}
                    </button>
                    @endforeach
                </div>

                {{-- Grille des plats --}}
                <div x-show="chargement" style="text-align:center;padding:40px;color:#333;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size:24px;"></i>
                </div>
                <div x-show="!chargement && menusFiltres.length === 0" style="text-align:center;padding:40px;color:#2a2a2a;">
                    Aucun plat trouvé.
                </div>
                <div x-show="!chargement" class="menu-grid">
                    <template x-for="menu in menusFiltres" :key="menu.idmenu">
                        <div class="menu-card">
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12.5px;font-weight:700;color:#e5e5e5;" x-text="menu.intitule"></div>
                                <div style="font-size:11px;color:#555;margin-top:2px;" x-text="menu.categorie"></div>
                                <div style="font-size:13px;font-weight:700;color:var(--cc-orange2);margin-top:6px;"
                                     x-text="Math.round(menu.pu).toLocaleString('fr-FR') + ' FCFA'"></div>
                            </div>
                            <button type="button" @click="ajouterAuPanier(menu)" class="btn-add">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════
                 COLONNE DROITE — PANIER
            ══════════════════════════════════════════════ --}}
            <div>
                <div class="card panier-sticky">
                    <div class="card-header">
                        <div class="card-header-title">
                            <i class="fa-solid fa-cart-shopping" style="color:var(--cc-orange);"></i>
                            Mon panier
                            <span x-text="'(' + panier.length + ')'" style="font-weight:400;color:#444;font-size:11px;"></span>
                        </div>
                    </div>
                    <div class="card-body" style="max-height:340px;overflow-y:auto;">
                        <template x-if="panier.length === 0">
                            <p style="text-align:center;color:#2a2a2a;font-size:12px;padding:20px 0;">
                                Votre panier est vide
                            </p>
                        </template>
                        <template x-for="item in panier" :key="item.idmenu">
                            <div class="panier-item">
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:12px;font-weight:600;color:#e5e5e5;" x-text="item.intitule"></div>
                                    <div style="font-size:11px;color:#555;"
                                         x-text="Math.round(item.pu * item.quantite).toLocaleString('fr-FR') + ' FCFA'"></div>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <button type="button" @click="changerQuantite(item.idmenu, -1)" class="qte-btn">−</button>
                                    <span style="font-size:12px;font-weight:700;color:#e5e5e5;min-width:16px;text-align:center;" x-text="item.quantite"></span>
                                    <button type="button" @click="changerQuantite(item.idmenu, 1)" class="qte-btn">+</button>
                                </div>
                            </div>
                        </template>

                        {{-- Inputs cachés générés depuis le panier --}}
                        <template x-for="(item, index) in panier" :key="'input-' + item.idmenu">
                            <div>
                                <input type="hidden" :name="'items[' + index + '][idmenu]'" :value="item.idmenu">
                                <input type="hidden" :name="'items[' + index + '][quantite]'" :value="item.quantite">
                            </div>
                        </template>
                    </div>

                    <div style="padding:16px 20px;border-top:1px solid #1a1a1a;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:14px;">
                            <span style="font-size:13px;color:#888;">Total</span>
                            <span style="font-size:18px;font-weight:700;color:#fff;" x-text="Math.round(total).toLocaleString('fr-FR') + ' FCFA'"></span>
                        </div>

                        <label class="field-label">Consignes <span style="color:#333;">(optionnel)</span></label>
                        <textarea name="consignes" x-model="consignes" rows="2" maxlength="500"
                                  placeholder="Sans piment, couverts en plus..."
                                  class="field-input" style="resize:none;margin-bottom:14px;"></textarea>

                        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"
                                :disabled="panier.length === 0">
                            <i class="fa-solid fa-check"></i>
                            {{ $mode === 'edit' ? 'Enregistrer les modifications' : 'Passer la commande' }}
                        </button>
                        <a href="{{ $mode === 'edit' ? route('mes-commandes.show', $commande->idcommande) : route('mes-commandes.index') }}"
                           class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:8px;">
                            Annuler
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>