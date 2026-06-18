{{-- On hérite du gabarit plein écran défini en 6.5 --}}
@extends('layouts.auth')

@section('content')
<button class=""></button>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md animate__animated animate__fadeInUp">
 
        {{-- Logo et titre --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-orange-500 rounded-2xl shadow-lg mb-4">
                <i class="fa-solid fa-utensils text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white">RestoGestion</h1>
            <p class="text-blue-300 mt-1">Plateforme de gestion restaurant</p>
        </div>
 
        {{-- Carte du formulaire --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Connexion</h2>
 
            {{-- Affichage des erreurs de validation, s'il y en a --}}
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-3 flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
                    <ul class="text-sm text-red-600">
                        {{-- On parcourt et affiche chaque message d'erreur --}}
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
 
            {{-- Le formulaire envoie les données vers la route 'login.post' --}}
            <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                @csrf  {{-- Jeton anti-CSRF obligatoire --}}
                <div class="space-y-4">
 
                    {{-- Champ email --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            {{-- old('email') ré-affiche la valeur saisie en cas d'erreur --}}
                            {{-- @error('email') ajoute une bordure rouge si ce champ est invalide --}}
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="input-field pl-10 @error('email') border-red-500 @enderror"
                                   placeholder="admin@restogestion.cm" required>
                        </div>
                    </div>
 
                    {{-- Champ mot de passe avec bouton afficher/masquer (Alpine.js) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                        {{-- x-data crée l'état local "show" (true/false) --}}
                        <div class="relative" x-data="{ show: false }">
                            <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            {{-- :type bascule entre 'text' et 'password' selon show --}}
                            <input :type="show ? 'text' : 'password'" name="password"
                                   class="input-field pl-10 pr-10" placeholder="********" required>
                            {{-- Le bouton inverse la valeur de show --}}
                            <button type="button" @click="show = !show"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i :class="show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                            </button>
                        </div>
                    </div>
 
                    {{-- Bouton de soumission --}}
                    <button type="submit" id="submitBtn"
                            class="btn-primary w-full flex items-center justify-center gap-2 py-3">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Se connecter</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
