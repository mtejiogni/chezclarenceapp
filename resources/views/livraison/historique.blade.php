@extends('layouts.app')

@section('title', 'Historique des Livraisons')
@section('page-title', 'Historique des Livraisons')

@section('content')

<div class="flex items-center justify-between flex-wrap gap-3 mb-5">
    <div>
        <h2 class="text-lg font-bold text-white m-0">
            <i class="fa-solid fa-clock-rotate-left text-orange-600 mr-2"></i>
            Historique des Livraisons
        </h2>
        <p class="text-xs text-neutral-500 mt-1">{{ $date->isoFormat('dddd DD MMMM YYYY') }}</p>
    </div>

    <div class="flex items-center gap-2">
        <form method="GET" action="{{ route('livraisons.historique') }}">
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
                   onchange="this.form.submit()"
                   class="bg-black border border-neutral-800 rounded-lg px-3 py-2 text-xs text-neutral-200 outline-none focus:border-orange-600">
        </form>
        <a href="{{ route('livraisons.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold
                  bg-neutral-900 border border-neutral-800 text-neutral-400 hover:text-neutral-200 hover:border-neutral-700 transition">
            <i class="fa-solid fa-motorcycle"></i> Écran live
        </a>
    </div>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-5">
    <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-4 text-center">
        <div class="text-2xl font-bold text-green-500">{{ $nbLivrees }}</div>
        <div class="text-[11px] text-neutral-500 mt-1">Livrées</div>
    </div>
    <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-4 text-center">
        <div class="text-2xl font-bold text-red-500">{{ $nbAnnulees }}</div>
        <div class="text-[11px] text-neutral-500 mt-1">Annulées</div>
    </div>
    <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-4 text-center col-span-2 md:col-span-1">
        <div class="text-2xl font-bold text-white">{{ number_format($totalMontant, 0, ',', ' ') }}</div>
        <div class="text-[11px] text-neutral-500 mt-1">Montant livré (FCFA)</div>
    </div>
</div>

{{-- Tableau --}}
<div class="bg-neutral-900 border border-neutral-800 rounded-2xl overflow-hidden">
    <table class="w-full border-collapse">
        <thead>
            <tr>
                <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-neutral-600 px-4 py-3 border-b border-neutral-800">Référence</th>
                <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-neutral-600 px-4 py-3 border-b border-neutral-800">Client</th>
                <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-neutral-600 px-4 py-3 border-b border-neutral-800">Adresse</th>
                <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-neutral-600 px-4 py-3 border-b border-neutral-800">Heure</th>
                <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-neutral-600 px-4 py-3 border-b border-neutral-800">Statut</th>
                <th class="text-right text-[10px] font-semibold uppercase tracking-wide text-neutral-600 px-4 py-3 border-b border-neutral-800">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($livraisons as $cmd)
            <tr>
                <td class="px-4 py-3 text-xs text-neutral-200 font-semibold border-b border-neutral-900">{{ $cmd->reference }}</td>
                <td class="px-4 py-3 text-xs text-neutral-400 border-b border-neutral-900">
                    {{ $cmd->client ? $cmd->client->prenom . ' ' . $cmd->client->nom : '—' }}
                </td>
                <td class="px-4 py-3 text-xs text-neutral-500 border-b border-neutral-900">
                    {{ $cmd->adresse ?? '—' }}
                </td>
                <td class="px-4 py-3 text-xs text-neutral-500 border-b border-neutral-900">{{ $cmd->heurecommande }}</td>
                <td class="px-4 py-3 border-b border-neutral-900">
                    @if($cmd->statut_courant === 'Livrée')
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-full bg-green-500/15 text-green-500">
                        <i class="fa-solid fa-circle-check"></i> Livrée
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-full bg-red-500/15 text-red-500">
                        <i class="fa-solid fa-circle-xmark"></i> Annulée
                    </span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-white font-bold text-right border-b border-neutral-900">
                    {{ number_format($cmd->montant, 0, ',', ' ') }} F
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-10 text-neutral-800 text-sm">
                    Aucune livraison enregistrée pour cette date
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection