@extends('layouts.app')

@section('title', 'Nouveau statut')
@section('page-title', 'Nouveau statut')

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
        padding: 24px;
    }

    .field-label {
        display: block; font-size: 11px; font-weight: 600;
        color: #666; margin-bottom: 6px;
    }
    .field-input {
        width: 100%;
        background: var(--cc-dark2); border: 1px solid var(--cc-border);
        border-radius: 10px; padding: 10px 14px;
        color: #e5e5e5; font-size: 13px; outline: none;
        font-family: inherit; transition: border-color .18s;
    }
    .field-input:focus { border-color: var(--cc-orange); }
    .field-input.error { border-color: rgba(239,68,68,.5); }
    .field-input:disabled { color: #444; cursor: not-allowed; background: #0a0a0a; }
    .field-error { font-size: 11px; color: #f87171; margin-top: 6px; }
    .field-hint { font-size: 11px; color: #444; margin-top: 6px; }

    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 18px; border-radius: 10px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        transition: all .18s; border: none; font-family: inherit;
        text-decoration: none;
    }
    .btn-primary { background: var(--cc-orange); color: #fff; }
    .btn-primary:hover { background: #c2410c; }
    .btn-ghost { background: var(--cc-dark3); border: 1px solid var(--cc-border); color: #555; }
    .btn-ghost:hover { color: #ccc; border-color: #333; }

    .back-btn {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: var(--cc-dark3); border: 1px solid var(--cc-border); color: #555;
        text-decoration: none; transition: all .18s;
    }
    .back-btn:hover { color: #ccc; border-color: #333; }

    @media (max-width: 640px) {
        .card { padding: 18px; }
    }
</style>
@endpush

@section('content')

<div style="max-width:560px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
        <a href="{{ route('admin.statuts.index') }}" class="back-btn">
            <i class="fa-solid fa-arrow-left" style="font-size:13px;"></i>
        </a>
        <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">
            <i class="fa-solid fa-plus" style="color:var(--cc-orange);margin-right:8px;"></i>
            Nouveau statut
        </h2>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.statuts.store') }}">
            @csrf

            {{-- Intitulé --}}
            <div style="margin-bottom:20px;">
                <label class="field-label">
                    Intitulé du statut <span style="color:#f87171;">*</span>
                </label>
                <input type="text" name="intitule" value="{{ old('intitule') }}"
                       placeholder="Ex : En route vers l'entrepôt"
                       maxlength="128"
                       class="field-input @error('intitule') error @enderror">
                @error('intitule')
                <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Priorité --}}
            <div style="margin-bottom:20px;">
                <label class="field-label">
                    Priorité (ordre du workflow) <span style="color:#f87171;">*</span>
                </label>
                <input type="number" name="priorite" min="1" max="99"
                       value="{{ old('priorite', $prochainePriorite) }}"
                       class="field-input @error('priorite') error @enderror">
                @error('priorite')
                <p class="field-error">{{ $message }}</p>
                @enderror
                <p class="field-hint">
                    Peut aussi être réorganisée par glisser-déposer depuis la liste, après création.
                </p>
            </div>

            {{-- Description --}}
            <div style="margin-bottom:22px;">
                <label class="field-label">
                    Description <span style="color:#333;">(optionnel)</span>
                </label>
                <textarea name="description" rows="3" maxlength="500"
                          placeholder="Note interne sur ce statut..."
                          class="field-input @error('description') error @enderror"
                          style="resize:none;"
                >{{ old('description') }}</textarea>
                @error('description')
                <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Info couleur/icône auto-inférées --}}
            <div style="margin-bottom:22px;padding:12px 14px;border-radius:10px;
                        background:rgba(96,165,250,.06);border:1px solid rgba(96,165,250,.15);
                        font-size:11px;color:#8fb3e0;display:flex;gap:9px;">
                <i class="fa-solid fa-circle-info" style="margin-top:1px;flex-shrink:0;color:#60a5fa;"></i>
                <span>
                    L'apparence (couleur, icône) des 6 statuts système est prédéfinie. Un statut personnalisé
                    comme celui-ci s'affichera par défaut en <span style="color:#ccc;">gris</span> avec une icône neutre
                    tant qu'il n'a pas été ajouté à la configuration d'affichage.
                </span>
            </div>

            <div style="display:flex;align-items:center;gap:10px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i> Créer le statut
                </button>
                <a href="{{ route('admin.statuts.index') }}" class="btn btn-ghost">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@endsection