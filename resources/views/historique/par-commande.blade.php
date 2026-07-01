@extends('layouts.app')

@section('title', 'Historique — ' . $commande->reference)
@section('page-title', 'Historique de la commande')

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
        padding: 14px 20px;
        border-bottom: 1px solid #1a1a1a;
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
    }
    .card-header-title {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; font-weight: 700; color: #e5e5e5;
    }
    .card-body { padding: 20px; }

    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 16px; border-radius: 10px;
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
        text-decoration: none; transition: all .18s; flex-shrink: 0;
    }
    .back-btn:hover { color: #ccc; border-color: #333; }

    .field-input {
        width: 100%;
        background: var(--cc-dark2); border: 1px solid var(--cc-border);
        border-radius: 10px; padding: 10px 14px;
        color: #e5e5e5; font-size: 13px; outline: none;
        font-family: inherit; transition: border-color .18s; resize: none;
    }
    .field-input:focus { border-color: var(--cc-orange); }
    .field-error { font-size: 11px; color: #f87171; margin-top: 6px; }

    .timeline-item { display: flex; gap: 14px; padding-bottom: 18px; position: relative; }
    .timeline-item:not(:last-child)::before {
        content: ''; position: absolute; left: 5px; top: 22px; bottom: -6px;
        width: 1px; background: #1a1a1a;
    }
    .timeline-dot {
        width: 11px; height: 11px; border-radius: 50%;
        flex-shrink: 0; margin-top: 4px; box-shadow: 0 0 0 1px #262626;
    }
    .timeline-content { flex: 1; min-width: 0; }
    .timeline-time { font-size: 10px; color: #444; margin-bottom: 2px; }
    .timeline-statut {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 12px; font-weight: 700;
    }
    .timeline-desc { font-size: 12px; color: #888; margin-top: 4px; }
    .timeline-note {
        margin-top: 6px; padding: 8px 10px; border-radius: 8px;
        background: rgba(167,139,250,.06); border: 1px solid rgba(167,139,250,.15);
        font-size: 11px; color: #c4b5fd;
    }
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
    <a href="{{ route('commandes.show', $commande->idcommande) }}" class="back-btn">
        <i class="fa-solid fa-arrow-left" style="font-size:13px;"></i>
    </a>
    <div style="flex:1;">
        <h2 style="font-size:18px;font-weight:700;color:#fff;margin:0;">
            <i class="fa-solid fa-clock-rotate-left" style="color:var(--cc-orange);margin-right:8px;"></i>
            Historique — {{ $commande->reference }}
        </h2>
        <p style="font-size:12px;color:#444;margin:4px 0 0;">
            Statut actuel : <span style="color:#ccc;font-weight:600;">{{ $commande->statut_courant }}</span>
        </p>
    </div>
    <a href="{{ route('commandes.show', $commande->idcommande) }}" class="btn btn-ghost">
        Voir la commande
    </a>
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

{{-- Ajouter une note --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fa-solid fa-note-sticky" style="color:#a78bfa;"></i>
            Ajouter une note
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('commandes.historique.store', $commande->idcommande) }}"
              style="display:flex;gap:10px;align-items:flex-start;">
            @csrf
            <textarea name="description" rows="1" maxlength="500" required
                      placeholder="Ex : Client rappelé pour confirmer l'adresse..."
                      class="field-input" style="flex:1;"
            >{{ old('description') }}</textarea>
            <button type="submit" class="btn btn-primary" style="flex-shrink:0;">
                <i class="fa-solid fa-plus"></i> Ajouter
            </button>
        </form>
        @error('description')
        <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Timeline --}}
<div class="card">
    <div class="card-header">
        <div class="card-header-title">
            <i class="fa-solid fa-timeline" style="color:var(--cc-orange);"></i>
            Chronologie ({{ $historiques->count() }})
        </div>
    </div>
    <div class="card-body">
        @forelse($historiques as $h)
        @php $estNote = str_starts_with($h->description ?? '', '[NOTE]'); @endphp
        <div class="timeline-item">
            <div class="timeline-dot" style="background:{{ $h->text_color }};"></div>
            <div class="timeline-content">
                <div class="timeline-time">{{ $h->date_fmt }}</div>
                <div class="timeline-statut" style="color:{{ $h->text_color }};">
                    <i class="fa-solid {{ $h->icone }}" style="font-size:11px;"></i>
                    {{ $h->statut->intitule ?? 'N/A' }}
                </div>
                @if($estNote)
                <div class="timeline-note">
                    <i class="fa-solid fa-note-sticky" style="margin-right:5px;"></i>
                    {{ str_replace('[NOTE] ', '', $h->description) }}
                </div>
                @elseif($h->description)
                <div class="timeline-desc">{{ $h->description }}</div>
                @endif
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:30px;color:#2a2a2a;">
            <p style="font-size:13px;">Aucun historique pour cette commande</p>
        </div>
        @endforelse
    </div>
</div>

@endsection