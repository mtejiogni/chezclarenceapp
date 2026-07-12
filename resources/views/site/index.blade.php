<!doctype html>
<html lang="fr" class="scroll-smooth">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $parametres->nom_affichage }} @if($parametres->slogan) — {{ $parametres->slogan }} @endif</title>
<meta name="description" content="{{ $parametres->description ?: ($parametres->nom_affichage.' — commandez, réservez ou faites-vous livrer en quelques clics.') }}">
<link rel="icon" href="{{ $parametres->logo_url }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;0,9..144,600;0,9..144,700;0,9..144,900;1,9..144,500;1,9..144,600&family=Manrope:wght@400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          forest: { DEFAULT: '#173B2B', dark: '#0E2419', light: '#21543D' },
          gold:   { DEFAULT: '#D8A439', dark: '#B8862A', light: '#EFCA7B' },
          spice:  { DEFAULT: '#B23A1B', dark: '#8A2C13', light: '#D8623D' },
          cream:  { DEFAULT: '#FBF2DD', dark: '#F2E4C4' },
          ink:    '#1B1A16',
        },
        fontFamily: {
          display: ['"Fraunces"', 'serif'],
          body: ['"Manrope"', 'sans-serif'],
          mono: ['"Space Mono"', 'monospace'],
        },
        boxShadow: {
          ticket: '0 18px 40px -18px rgba(23,59,43,0.45)',
        },
      }
    }
  }
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">

<style>
  :root{ --wax-color: rgba(216,164,57,0.16); }
  html{ font-size: 16px; }
  body{ font-family: 'Manrope', sans-serif; background:#FBF2DD; color:#1B1A16; }
  h1,h2,h3,h4,.font-display{ font-family: 'Fraunces', serif; }
  .font-mono{ font-family: 'Space Mono', monospace; }

  .wax-pattern{
    background-image: repeating-linear-gradient(45deg, var(--wax-color) 0, var(--wax-color) 2px, transparent 2px, transparent 26px),
                       repeating-linear-gradient(-45deg, var(--wax-color) 0, var(--wax-color) 2px, transparent 2px, transparent 26px);
  }

  .ticket-card{ position:relative; }
  .ticket-card::before,
  .ticket-card::after{
    content:"";
    position:absolute; width:22px; height:22px; border-radius:9999px;
    background:#FBF2DD; left:-11px; z-index:2;
  }
  .ticket-card::before{ top:-11px; }
  .ticket-card::after{ bottom:-11px; }
  .ticket-perforation{ border-top: 2px dashed rgba(27,26,22,0.18); }

  .steam path{ animation: steam-rise 3.2s ease-in-out infinite; transform-origin: bottom; }
  .steam path:nth-child(2){ animation-delay: .5s; }
  .steam path:nth-child(3){ animation-delay: 1s; }
  @keyframes steam-rise{
    0%{ transform: translateY(0) scaleY(1); opacity:.0; }
    20%{ opacity:.55; }
    50%{ transform: translateY(-10px) scaleY(1.06); opacity:.55; }
    100%{ transform: translateY(-22px) scaleY(1.1); opacity:0; }
  }

  .star-fill{ fill:#D8A439; }
  .star-empty{ fill:none; stroke:#D8A439; stroke-width:1.6; }

  ::selection{ background:#D8A439; color:#0E2419; }
  a, button{ transition: all .2s ease; }
  :focus-visible{ outline: 3px solid #D8A439; outline-offset: 3px; border-radius:4px; }

  @media (prefers-reduced-motion: reduce){
    *{ animation-duration: .001ms !important; animation-iteration-count: 1 !important; scroll-behavior:auto !important; }
  }
  [x-cloak]{ display:none !important; }
</style>
</head>
<body class="bg-cream text-ink antialiased">

<div x-data="{ navOpen:false, scrolled:false }" x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 24)">

  {{-- ============ NAVBAR ============ --}}
  <header class="fixed top-0 inset-x-0 z-50 transition-colors duration-300"
          :class="scrolled ? 'bg-forest/95 backdrop-blur shadow-lg' : 'bg-transparent'">
    <nav class="max-w-7xl mx-auto px-5 sm:px-8 h-20 flex items-center justify-between">
      <a href="#accueil" class="flex items-center gap-3 shrink-0">
        <img src="{{ $parametres->logo_url }}" alt="{{ $parametres->nom_affichage }}" class="h-10 w-10 rounded-full object-cover ring-2 ring-gold/70">
        <span class="font-display text-xl sm:text-2xl font-semibold text-cream tracking-tight">{{ $parametres->nom_affichage }}</span>
      </a>

      <ul class="hidden lg:flex items-center gap-8 font-medium text-cream/90 text-sm tracking-wide uppercase">
        <li><a href="#apropos" class="hover:text-gold">Le restaurant</a></li>
        <li><a href="#menu" class="hover:text-gold">Notre carte</a></li>
        <li><a href="#services" class="hover:text-gold">Services</a></li>
        <li><a href="#localisation" class="hover:text-gold">Nous trouver</a></li>
        <li><a href="#contact" class="hover:text-gold">Contact</a></li>
      </ul>

      <a href="{{ $whatsapp['commander'] }}" target="_blank" rel="noopener"
         class="hidden lg:inline-flex items-center gap-2 bg-spice hover:bg-spice-dark text-cream font-semibold px-5 py-2.5 rounded-full text-sm shadow-md">
        <svg viewBox="0 0 32 32" class="w-4 h-4 fill-current"><path d="M19.11 17.2c-.28-.14-1.63-.8-1.88-.9-.25-.09-.44-.14-.62.14-.18.28-.72.9-.88 1.08-.16.18-.32.2-.6.07-.28-.14-1.18-.44-2.25-1.4-.83-.74-1.4-1.66-1.56-1.94-.16-.28-.02-.43.12-.57.13-.13.28-.34.42-.5.14-.17.18-.28.28-.47.09-.19.05-.35-.02-.5-.07-.14-.62-1.5-.85-2.05-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.47.07-.71.35-.25.28-.94.92-.94 2.24s.96 2.6 1.1 2.78c.14.18 1.9 2.9 4.6 4.06.64.28 1.15.44 1.54.57.65.2 1.24.18 1.71.11.52-.08 1.63-.66 1.86-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.53-.32z"></path><path d="M16 3C9.37 3 4 8.37 4 15c0 2.34.66 4.53 1.8 6.39L4 29l7.83-1.75A11.94 11.94 0 0 0 16 27c6.63 0 12-5.37 12-12S22.63 3 16 3zm0 21.8c-2.02 0-3.92-.58-5.52-1.58l-.4-.24-4.64 1.04 1-4.52-.26-.42A9.77 9.77 0 0 1 6.2 15c0-5.4 4.4-9.8 9.8-9.8s9.8 4.4 9.8 9.8-4.4 9.8-9.8 9.8z"></path></svg>
        Commander
      </a>

      <button @click="navOpen = !navOpen" class="lg:hidden text-cream p-2" aria-label="Ouvrir le menu">
        <svg x-show="!navOpen" viewBox="0 0 24 24" class="w-7 h-7 stroke-current fill-none" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <svg x-show="navOpen" x-cloak viewBox="0 0 24 24" class="w-7 h-7 stroke-current fill-none" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </nav>

    <div x-show="navOpen" x-cloak x-transition @click.away="navOpen=false"
         class="lg:hidden bg-forest-dark/98 backdrop-blur px-6 pb-8 pt-2">
      <ul class="flex flex-col gap-5 font-medium text-cream/90 text-base">
        <li><a @click="navOpen=false" href="#apropos" class="block py-1">Le restaurant</a></li>
        <li><a @click="navOpen=false" href="#menu" class="block py-1">Notre carte</a></li>
        <li><a @click="navOpen=false" href="#services" class="block py-1">Services</a></li>
        <li><a @click="navOpen=false" href="#localisation" class="block py-1">Nous trouver</a></li>
        <li><a @click="navOpen=false" href="#contact" class="block py-1">Contact</a></li>
        <li>
          <a href="{{ $whatsapp['commander'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 bg-spice text-cream font-semibold px-5 py-2.5 rounded-full text-sm mt-2">
            Commander sur WhatsApp
          </a>
        </li>
      </ul>
    </div>
  </header>

  {{-- ============ HERO ============ --}}
  <section id="accueil" class="relative min-h-screen flex items-center bg-forest wax-pattern overflow-hidden pt-28 pb-16">
    <div class="absolute -top-24 -right-24 w-[28rem] h-[28rem] rounded-full bg-gold/10 blur-3xl"></div>
    <div class="absolute bottom-0 left-0 w-72 h-72 rounded-full bg-spice/20 blur-3xl"></div>

    <div class="relative max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-2 gap-16 items-center w-full">
      <div data-aos="fade-up">
        <span class="inline-block font-mono text-xs uppercase tracking-[0.3em] text-gold border border-gold/40 rounded-full px-4 py-1.5">
          {{ $parametres->ville ?: 'Douala' }} · Cuisine locale &amp; grillades
        </span>
        <h1 class="mt-6 font-display font-semibold text-cream text-[clamp(2.6rem,6vw,4.6rem)] leading-[1.05]">
          {{ $parametres->nom_affichage }}
        </h1>
        @if($parametres->slogan)
          <p class="mt-4 font-display italic text-gold text-xl sm:text-2xl">{{ $parametres->slogan }}</p>
        @endif
        <p class="mt-6 text-cream/80 text-base sm:text-lg max-w-xl leading-relaxed">
          {{ $parametres->description ?: "Des plats mijotés avec soin, une salle chaleureuse et un service pensé pour vous : commandez, réservez ou faites-vous livrer en quelques secondes." }}
        </p>

        <div class="mt-9 flex flex-wrap gap-3.5">
          <a href="{{ $whatsapp['commander'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 bg-spice hover:bg-spice-dark text-cream font-semibold px-6 py-3.5 rounded-full shadow-lg shadow-spice/30">
            Commander maintenant
          </a>
          <a href="{{ $whatsapp['reserver'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 bg-transparent border border-gold text-gold hover:bg-gold hover:text-forest-dark font-semibold px-6 py-3.5 rounded-full">
            Réserver une table
          </a>
          <a href="{{ $whatsapp['traiteur'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 text-cream/90 hover:text-gold font-semibold px-3 py-3.5">
            Découvrir le traiteur →
          </a>
        </div>

        <div class="mt-10 flex items-center gap-6 text-cream/70 text-sm">
          <div class="flex items-center gap-2">
            <svg viewBox="0 0 24 24" class="w-5 h-5 fill-gold"><path d="M12 2l2.9 6.26L21.5 9l-5 4.64L17.8 21 12 17.3 6.2 21l1.3-7.36-5-4.64 6.6-.74z"/></svg>
            <span>Plats notés par nos habitués</span>
          </div>
          <div class="flex items-center gap-2">
            <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-gold fill-none" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span>Livraison &amp; réservation instantanées</span>
          </div>
        </div>
      </div>

      <div class="relative flex justify-center" data-aos="zoom-in" data-aos-delay="150">
        <svg viewBox="0 0 420 420" class="w-full max-w-md">
          <circle cx="210" cy="220" r="170" fill="#21543D"/>
          <ellipse cx="210" cy="330" rx="150" ry="18" fill="#0E2419" opacity="0.4"/>
          <ellipse cx="210" cy="255" rx="150" ry="46" fill="#FBF2DD"/>
          <ellipse cx="210" cy="248" rx="150" ry="46" fill="#FFFFFF"/>
          <ellipse cx="210" cy="248" rx="118" ry="32" fill="#D8A439" opacity="0.18"/>
          <ellipse cx="150" cy="240" rx="26" ry="16" fill="#B23A1B"/>
          <ellipse cx="200" cy="232" rx="30" ry="18" fill="#8A5A2B"/>
          <ellipse cx="255" cy="242" rx="22" ry="14" fill="#4E7A3D"/>
          <ellipse cx="215" cy="255" rx="18" ry="10" fill="#D8A439"/>
          <g class="steam" stroke="#FBF2DD" stroke-width="4" fill="none" stroke-linecap="round" opacity="0.8">
            <path d="M170 210 q-10 -20 0 -35 q10 -15 0 -32"/>
            <path d="M210 205 q-10 -20 0 -35 q10 -15 0 -32"/>
            <path d="M250 210 q-10 -20 0 -35 q10 -15 0 -32"/>
          </g>
          <circle cx="340" cy="90" r="26" fill="#D8A439"/>
          <text x="340" y="97" text-anchor="middle" class="font-display" font-size="22" font-weight="700" fill="#173B2B">★</text>
        </svg>
      </div>
    </div>

    <a href="#apropos" class="absolute bottom-8 left-1/2 -translate-x-1/2 text-cream/60 hover:text-gold" aria-label="Défiler">
      <svg viewBox="0 0 24 24" class="w-7 h-7 stroke-current fill-none animate-bounce" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v14m0 0l-6-6m6 6l6-6"/></svg>
    </a>
  </section>

  {{-- ============ A PROPOS ============ --}}
  <section id="apropos" class="py-24 sm:py-32 bg-cream">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-2 gap-16 items-center">

      <div data-aos="fade-right" class="order-2 lg:order-1 relative flex justify-center">
        <svg viewBox="0 0 380 420" class="w-full max-w-sm">
          <path d="M190 10c88 0 160 72 160 160 0 70-46 118-46 160 0 40-46 70-114 70S76 400 76 360c0-42-46-90-46-160C30 82 102 10 190 10z" fill="#EFCA7B" opacity="0.35"/>
          <rect x="120" y="150" width="140" height="150" rx="18" fill="#173B2B"/>
          <circle cx="190" cy="120" r="60" fill="#B23A1B"/>
          <path d="M130 120a60 60 0 01120 0z" fill="#FBF2DD"/>
          <rect x="150" y="220" width="80" height="10" rx="5" fill="#D8A439"/>
          <rect x="150" y="245" width="55" height="8" rx="4" fill="#EFCA7B"/>
          <circle cx="190" cy="120" r="10" fill="#FBF2DD"/>
          <path d="M160 90 q30 -20 60 0" stroke="#0E2419" stroke-width="4" fill="none" stroke-linecap="round"/>
        </svg>
      </div>

      <div data-aos="fade-left" class="order-1 lg:order-2">
        <span class="font-mono text-xs uppercase tracking-[0.3em] text-spice">{{ $parametres->entreprise ?: 'Notre maison' }}</span>
        <h2 class="mt-3 font-display font-semibold text-4xl sm:text-5xl text-forest leading-tight">
          Une cuisine préparée avec cœur, depuis {{ $parametres->ville ?: 'Douala' }}
        </h2>
        <p class="mt-6 text-ink/75 text-base sm:text-lg leading-relaxed">
          {{ $parametres->description ?: "Chez nous, chaque plat raconte une histoire : celle d'ingrédients choisis avec soin, de recettes transmises et d'un service qui vous traite comme un membre de la famille." }}
        </p>

        <div class="mt-10 grid sm:grid-cols-2 gap-6">
          @php
            $valeurs = [
              ['titre' => 'Fraîcheur', 'texte' => 'Des produits sélectionnés chaque jour.', 'icone' => 'feuille'],
              ['titre' => 'Savoir-faire', 'texte' => 'Des recettes maîtrisées avec passion.', 'icone' => 'toque-mini'],
              ['titre' => 'Hospitalité', 'texte' => "Un accueil chaleureux à chaque visite.", 'icone' => 'coeur'],
              ['titre' => 'Rapidité', 'texte' => 'Commande, réservation et livraison sans attente.', 'icone' => 'eclair'],
            ];
          @endphp
          @foreach($valeurs as $valeur)
            <div class="flex items-start gap-3.5">
              <span class="shrink-0 w-11 h-11 rounded-2xl bg-forest/8 grid place-items-center">
                @switch($valeur['icone'])
                  @case('feuille')
                    <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-forest fill-none" stroke-width="1.7"><path stroke-linecap="round" d="M5 20c9 0 14-5 14-14 0 0-14 0-14 14zM5 20c0-6 2-9 6-11"/></svg>
                    @break
                  @case('toque-mini')
                    <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-forest fill-none" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12c-1.5 0-2.5-1.4-2-2.8.4-1 1.5-1.6 2.5-1.4.2-1.8 1.8-3.1 3.6-2.9C10.4 3.7 11.6 3 13 3c2 0 3.6 1.6 3.6 3.6 0 .2 0 .3 0 .5 1.3 0 2.4 1 2.4 2.3 0 1.4-1.1 2.6-2.6 2.6H6z"/><path d="M6.5 12v6.5A1.5 1.5 0 008 20h8a1.5 1.5 0 001.5-1.5V12"/></svg>
                    @break
                  @case('coeur')
                    <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-forest fill-none" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.4-9.5-9C1 7.8 2.6 5 5.8 5c1.8 0 3.3 1 4.2 2.5C11 6 12.4 5 14.2 5c3.2 0 4.8 2.8 3.3 6-2.5 4.6-9.5 9-9.5 9z"/></svg>
                    @break
                  @default
                    <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-forest fill-none" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M13 2L4 14h7l-1 8 9-12h-7l1-8z"/></svg>
                @endswitch
              </span>
              <div>
                <p class="font-display font-semibold text-forest">{{ $valeur['titre'] }}</p>
                <p class="text-sm text-ink/60 mt-0.5">{{ $valeur['texte'] }}</p>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-10 flex flex-wrap gap-3.5">
          <a href="{{ $whatsapp['commander'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 bg-forest hover:bg-forest-dark text-cream font-semibold px-6 py-3 rounded-full">
            Commander
          </a>
          <a href="{{ $whatsapp['reserver'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 border border-forest/30 text-forest hover:bg-forest hover:text-cream font-semibold px-6 py-3 rounded-full">
            Réserver
          </a>
          <a href="{{ $whatsapp['contact'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 text-spice hover:text-spice-dark font-semibold px-3 py-3">
            Nous contacter →
          </a>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ MENU ============ --}}
  <section id="menu" class="py-24 sm:py-32 bg-forest-dark wax-pattern relative">
    @php
      $plats = collect();
      foreach ($categories as $cat) {
          foreach ($cat->menusActifs as $m) {
              $plats->push([
                  'id' => $m->idmenu,
                  'nom' => $m->intitule,
                  'description' => $m->description,
                  'prix' => (float) $m->pu,
                  'etoiles' => $m->etoiles,
                  'categorie' => $cat->intitule,
                  'photo' => $m->photo_url,
              ]);
          }
      }
      $nomsCategories = $categories->pluck('intitule')->values();
    @endphp

    <div class="max-w-7xl mx-auto px-5 sm:px-8" x-data="menuFilter(@js($plats), @js($nomsCategories), {{ $prixMax }})">
      <div class="text-center max-w-2xl mx-auto" data-aos="fade-up">
        <span class="font-mono text-xs uppercase tracking-[0.3em] text-gold">Notre carte</span>
        <h2 class="mt-3 font-display font-semibold text-4xl sm:text-5xl text-cream">Choisissez, on s'occupe du reste</h2>
        <p class="mt-4 text-cream/70">Les plats les plus commandés par nos clients gagnent des étoiles — filtrez par catégorie, par note ou par budget.</p>
      </div>

      {{-- Filtres --}}
      <div class="mt-14 flex flex-col gap-6" data-aos="fade-up">
        <div class="flex flex-wrap justify-center gap-2.5">
          <button @click="activeCategory='Tous'"
                  :class="activeCategory==='Tous' ? 'bg-gold text-forest-dark' : 'bg-cream/10 text-cream/80 hover:bg-cream/20'"
                  class="px-4 py-2 rounded-full text-sm font-semibold">Tous les plats</button>
          @foreach($categories as $categorie)
            <button @click="activeCategory='{{ $categorie->intitule }}'"
                    :class="activeCategory==='{{ $categorie->intitule }}' ? 'bg-gold text-forest-dark' : 'bg-cream/10 text-cream/80 hover:bg-cream/20'"
                    class="px-4 py-2 rounded-full text-sm font-semibold">
              {{ $categorie->intitule }} <span class="opacity-60">({{ $categorie->menusActifs->count() }})</span>
            </button>
          @endforeach
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10 bg-cream/5 rounded-2xl px-6 py-5">
          <div class="flex items-center gap-2">
            <span class="text-cream/70 text-sm font-medium mr-1">Note minimum :</span>
            @for($i=1;$i<=5;$i++)
              <button @click="activeStars = activeStars === {{ $i }} ? 0 : {{ $i }}" class="p-0.5" aria-label="{{ $i }} étoiles minimum">
                <svg viewBox="0 0 24 24" class="w-5 h-5" :class="activeStars >= {{ $i }} ? 'star-fill' : 'star-empty'"><path d="M12 2l2.9 6.26L21.5 9l-5 4.64L17.8 21 12 17.3 6.2 21l1.3-7.36-5-4.64 6.6-.74z"/></svg>
              </button>
            @endfor
          </div>

          <div class="flex items-center gap-3 w-full sm:w-64">
            <span class="text-cream/70 text-sm font-medium whitespace-nowrap">Prix max :</span>
            <input type="range" x-model.number="prix" min="0" :max="prixMax" step="100" class="w-full accent-gold">
            <span class="font-mono text-gold text-sm whitespace-nowrap" x-text="prix.toLocaleString('fr-FR') + ' {{ $parametres->devise }}'"></span>
          </div>
        </div>
      </div>

      {{-- Grille des plats --}}
      <div class="mt-16 grid sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-14">
        <template x-for="plat in filtered" :key="plat.id">
          <article class="ticket-card bg-cream rounded-2xl shadow-ticket overflow-hidden flex flex-col" data-aos="fade-up">
            <div class="h-40 bg-forest/10 relative overflow-hidden">
              <img :src="plat.photo" :alt="plat.nom" class="w-full h-full object-cover">
              <span class="absolute top-3 left-3 bg-forest-dark/80 text-cream text-[11px] font-mono uppercase tracking-wider px-2.5 py-1 rounded-full" x-text="plat.categorie"></span>
            </div>

            <div class="p-5 flex flex-col grow ticket-perforation">
              <div class="flex items-start justify-between gap-3">
                <h3 class="font-display font-semibold text-lg text-forest leading-snug" x-text="plat.nom"></h3>
                <span class="font-mono text-spice font-bold whitespace-nowrap" x-text="plat.prix.toLocaleString('fr-FR') + ' {{ $parametres->devise }}'"></span>
              </div>
              <p class="mt-1.5 text-sm text-ink/60 leading-relaxed grow" x-text="plat.description || 'Une spécialité préparée maison.'"></p>

              <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-0.5">
                  <template x-for="n in 5" :key="n">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" :class="n <= plat.etoiles ? 'star-fill' : 'star-empty'"><path d="M12 2l2.9 6.26L21.5 9l-5 4.64L17.8 21 12 17.3 6.2 21l1.3-7.36-5-4.64 6.6-.74z"/></svg>
                  </template>
                </div>
                <a :href="'https://wa.me/{{ $whatsapp['numero'] }}?text=' + encodeURIComponent('Bonjour, je souhaite commander : ' + plat.nom + ' — ' + plat.prix.toLocaleString('fr-FR') + ' {{ $parametres->devise }}')"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 bg-forest hover:bg-forest-dark text-cream text-sm font-semibold px-4 py-2 rounded-full">
                  Commander
                </a>
              </div>
            </div>
          </article>
        </template>

        <template x-if="filtered.length === 0">
          <div class="col-span-full text-center py-16">
            <p class="text-cream/70">Aucun plat ne correspond à ces filtres pour le moment.</p>
          </div>
        </template>
      </div>
    </div>
  </section>

  {{-- ============ SERVICES ============ --}}
  <section id="services" class="py-24 sm:py-32 bg-cream" x-data="{ open:false, currentId:null }">
    <div class="max-w-7xl mx-auto px-5 sm:px-8">
      <div class="text-center max-w-2xl mx-auto" data-aos="fade-up">
        <span class="font-mono text-xs uppercase tracking-[0.3em] text-spice">Au-delà de l'assiette</span>
        <h2 class="mt-3 font-display font-semibold text-4xl sm:text-5xl text-forest">Nos services</h2>
        <p class="mt-4 text-ink/65">Pour vos événements, vos cadeaux ou vos repas d'entreprise, nous nous adaptons à vos envies.</p>
      </div>

      <div class="mt-16 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($services as $service)
          <div class="group bg-white rounded-2xl p-7 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all border border-forest/5" data-aos="fade-up" data-aos-delay="{{ $loop->index * 60 }}">
            <span class="inline-grid w-12 h-12 place-items-center rounded-2xl bg-forest/8 group-hover:bg-gold/20">
              @switch($service['icone'])
                @case('cle')
                  <svg viewBox="0 0 24 24" class="w-6 h-6 stroke-forest fill-none" stroke-width="1.7"><circle cx="8" cy="8" r="4"/><path stroke-linecap="round" d="M11 11l9 9m-4-4l3-3m-6 6l-2-2"/></svg>
                  @break
                @case('toque')
                  <svg viewBox="0 0 24 24" class="w-6 h-6 stroke-forest fill-none" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12c-1.5 0-2.5-1.4-2-2.8.4-1 1.5-1.6 2.5-1.4.2-1.8 1.8-3.1 3.6-2.9C10.4 3.7 11.6 3 13 3c2 0 3.6 1.6 3.6 3.6 0 .2 0 .3 0 .5 1.3 0 2.4 1 2.4 2.3 0 1.4-1.1 2.6-2.6 2.6H6z"/><path d="M6.5 12v6.5A1.5 1.5 0 008 20h8a1.5 1.5 0 001.5-1.5V12"/></svg>
                  @break
                @case('cadeau')
                  <svg viewBox="0 0 24 24" class="w-6 h-6 stroke-forest fill-none" stroke-width="1.7"><rect x="4" y="9" width="16" height="11" rx="1.5"/><path d="M4 13h16M12 9v11"/><path stroke-linecap="round" d="M12 9C9 9 8 4 5.5 6S9 9 12 9zm0 0c3 0 4-5 6.5-3S15 9 12 9z"/></svg>
                  @break
                @case('scooter')
                  <svg viewBox="0 0 24 24" class="w-6 h-6 stroke-forest fill-none" stroke-width="1.7"><circle cx="6" cy="18" r="2.4"/><circle cx="18" cy="18" r="2.4"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 18h5l2-6h4M11 12l2-3h3M8 18v-4l-3-2"/></svg>
                  @break
                @case('ballons')
                  <svg viewBox="0 0 24 24" class="w-6 h-6 stroke-forest fill-none" stroke-width="1.7"><ellipse cx="8" cy="7" rx="4" ry="5"/><ellipse cx="16" cy="9" rx="3.4" ry="4.2"/><path stroke-linecap="round" d="M8 12l-1 8m9-11l-1 7"/></svg>
                  @break
                @default
                  <svg viewBox="0 0 24 24" class="w-6 h-6 stroke-forest fill-none" stroke-width="1.7"><circle cx="9" cy="8" r="2.6"/><circle cx="17" cy="9" r="2.2"/><path stroke-linecap="round" d="M4 19c0-3 2.5-5 5-5s5 2 5 5M14 19c.3-2.4 2-4 4.5-4S23 17 23 19"/></svg>
              @endswitch
            </span>
            <h3 class="mt-5 font-display font-semibold text-xl text-forest">{{ $service['titre'] }}</h3>
            <p class="mt-2 text-sm text-ink/60 leading-relaxed">{{ $service['resume'] }}</p>
            <button @click="open=true; currentId='{{ $service['id'] }}'" class="mt-5 inline-flex items-center gap-1.5 text-spice font-semibold text-sm hover:text-spice-dark">
              En savoir plus
              <svg viewBox="0 0 24 24" class="w-4 h-4 stroke-current fill-none" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Modales de services --}}
    @foreach($services as $service)
      <div x-show="open && currentId==='{{ $service['id'] }}'" x-cloak
           class="fixed inset-0 z-[70] grid place-items-center px-5"
           x-transition.opacity>
        <div @click="open=false" class="absolute inset-0 bg-ink/70 backdrop-blur-sm"></div>
        <div class="relative bg-cream rounded-2xl max-w-lg w-full p-8 shadow-2xl" x-transition
             @click.outside="open=false">
          <button @click="open=false" class="absolute top-4 right-4 text-ink/50 hover:text-ink" aria-label="Fermer">
            <svg viewBox="0 0 24 24" class="w-6 h-6 stroke-current fill-none" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
          </button>
          <h3 class="font-display font-semibold text-2xl text-forest pr-6">{{ $service['titre'] }}</h3>
          <p class="mt-4 text-ink/70 leading-relaxed">{{ $service['description'] }}</p>
          <a href="{{ $service['lien_whatsapp'] }}" target="_blank" rel="noopener"
             class="mt-7 inline-flex items-center gap-2 bg-spice hover:bg-spice-dark text-cream font-semibold px-6 py-3 rounded-full">
            Demander un devis sur WhatsApp
          </a>
        </div>
      </div>
    @endforeach
  </section>

  {{-- ============ LOCALISATION ============ --}}
  <section id="localisation" class="py-24 sm:py-32 bg-forest-dark">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-2 gap-12 items-stretch">
      <div data-aos="fade-right" class="flex flex-col justify-center">
        <span class="font-mono text-xs uppercase tracking-[0.3em] text-gold">Nous trouver</span>
        <h2 class="mt-3 font-display font-semibold text-4xl sm:text-5xl text-cream">Passez nous voir</h2>
        <p class="mt-5 text-cream/70 leading-relaxed">{{ $parametres->adresse ?: 'Adresse à venir' }}</p>

        <ul class="mt-6 space-y-3 text-cream/80 text-sm">
          @if($parametres->horaires)
            <li class="flex items-center gap-3">
              <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-gold fill-none shrink-0" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3.5 2"/></svg>
              {{ $parametres->horaires }}
            </li>
          @endif
          @if($parametres->telephone)
            <li class="flex items-center gap-3">
              <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-gold fill-none shrink-0" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5c0 8.3 6.7 15 15 15l3-4-6-3-2 2c-2-1-4-3-5-5l2-2-3-6z"/></svg>
              {{ $parametres->telephone }}@if($parametres->telephone2) · {{ $parametres->telephone2 }} @endif
            </li>
          @endif
        </ul>

        <div class="mt-9 flex flex-wrap gap-3.5">
          <a href="{{ $lienItineraire }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 bg-gold hover:bg-gold-dark text-forest-dark font-semibold px-6 py-3.5 rounded-full">
            <svg viewBox="0 0 24 24" class="w-5 h-5 fill-none stroke-current" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11l18-7-7 18-2-8-9-3z"/></svg>
            Lancer l'itinéraire GPS
          </a>
          <a href="{{ $whatsapp['reserver'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 border border-cream/30 text-cream hover:bg-cream/10 font-semibold px-6 py-3.5 rounded-full">
            Réserver une table
          </a>
        </div>
      </div>

      <div class="rounded-2xl overflow-hidden min-h-[22rem] shadow-2xl" data-aos="fade-left">
        @if($parametres->latitude && $parametres->longitude)
          <iframe
            class="w-full h-full min-h-[22rem]"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            src="https://www.google.com/maps?q={{ $parametres->latitude }},{{ $parametres->longitude }}&z=16&output=embed">
          </iframe>
        @else
          <div class="w-full h-full min-h-[22rem] bg-forest grid place-items-center text-cream/60 text-sm">
            Localisation à venir
          </div>
        @endif
      </div>
    </div>
  </section>

  {{-- ============ CONTACT ============ --}}
  <section id="contact" class="py-24 sm:py-32 bg-cream">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-5 gap-12">

      <div class="lg:col-span-2" data-aos="fade-right">
        <span class="font-mono text-xs uppercase tracking-[0.3em] text-spice">Contact</span>
        <h2 class="mt-3 font-display font-semibold text-4xl text-forest">Parlons de votre prochain repas</h2>
        <p class="mt-4 text-ink/65 leading-relaxed">Une question, une envie particulière ? Écrivez-nous, nous répondons rapidement sur WhatsApp.</p>

        <div class="mt-8 space-y-4 text-sm">
          @if($parametres->adresse)
            <div class="flex gap-3"><span class="text-gold font-mono">—</span><span>{{ $parametres->adresse }}</span></div>
          @endif
          @if($parametres->telephone)
            <div class="flex gap-3"><span class="text-gold font-mono">—</span><span>{{ $parametres->telephone }}</span></div>
          @endif
          @if($parametres->email)
            <div class="flex gap-3"><span class="text-gold font-mono">—</span><span>{{ $parametres->email }}</span></div>
          @endif
          @if($parametres->horaires)
            <div class="flex gap-3"><span class="text-gold font-mono">—</span><span>{{ $parametres->horaires }}</span></div>
          @endif
        </div>
      </div>

      <div class="lg:col-span-3" data-aos="fade-left"
           x-data="{ nom:'', telephone:'', sujet:'Commande', message:'' }">
        <form class="bg-white rounded-2xl p-7 sm:p-8 shadow-sm border border-forest/5 space-y-5"
              @submit.prevent="
                const texte = 'Bonjour, je suis ' + nom + '.%0ASujet : ' + sujet + '.%0ATéléphone : ' + telephone + '.%0AMessage : ' + message;
                window.open('https://wa.me/{{ $whatsapp['numero'] }}?text=' + texte, '_blank');
              ">
          <div class="grid sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-semibold text-forest mb-1.5">Nom</label>
              <input x-model="nom" required type="text" placeholder="Votre nom"
                     class="w-full rounded-xl border border-forest/15 px-4 py-3 text-sm focus:border-gold focus:ring-2 focus:ring-gold/30 outline-none">
            </div>
            <div>
              <label class="block text-sm font-semibold text-forest mb-1.5">Téléphone</label>
              <input x-model="telephone" required type="tel" placeholder="+237 6xx xx xx xx"
                     class="w-full rounded-xl border border-forest/15 px-4 py-3 text-sm focus:border-gold focus:ring-2 focus:ring-gold/30 outline-none">
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-forest mb-1.5">Sujet</label>
            <select x-model="sujet" class="w-full rounded-xl border border-forest/15 px-4 py-3 text-sm focus:border-gold focus:ring-2 focus:ring-gold/30 outline-none">
              <option>Commande</option>
              <option>Réservation de table</option>
              <option>Service traiteur</option>
              <option>Privatisation</option>
              <option>Autre demande</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-forest mb-1.5">Message</label>
            <textarea x-model="message" required rows="4" placeholder="Votre message..."
                      class="w-full rounded-xl border border-forest/15 px-4 py-3 text-sm focus:border-gold focus:ring-2 focus:ring-gold/30 outline-none"></textarea>
          </div>
          <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-spice hover:bg-spice-dark text-cream font-semibold px-6 py-3.5 rounded-xl">
            Envoyer sur WhatsApp
          </button>
        </form>
      </div>
    </div>
  </section>

  {{-- ============ FOOTER ============ --}}
  <footer class="bg-forest-dark text-cream/80 pt-20 pb-8">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-10">
      <div>
        <span class="font-display text-2xl font-semibold text-cream">{{ $parametres->nom_affichage }}</span>
        <p class="mt-3 text-sm leading-relaxed text-cream/60">{{ $parametres->description ? \Illuminate\Support\Str::limit($parametres->description, 140) : 'Une cuisine généreuse, un accueil chaleureux.' }}</p>
        <div class="mt-5 flex gap-3">
          <a href="#" class="w-9 h-9 rounded-full border border-cream/20 grid place-items-center hover:bg-gold hover:border-gold hover:text-forest-dark" aria-label="Facebook">
            <svg viewBox="0 0 24 24" class="w-4 h-4 fill-current"><path d="M14 9h3V5h-3c-2.2 0-4 1.8-4 4v2H8v4h2v6h4v-6h3l1-4h-4V9c0-.6.4-1 1-1z"/></svg>
          </a>
          <a href="#" class="w-9 h-9 rounded-full border border-cream/20 grid place-items-center hover:bg-gold hover:border-gold hover:text-forest-dark" aria-label="Instagram">
            <svg viewBox="0 0 24 24" class="w-4 h-4 fill-none stroke-current" stroke-width="1.8"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1"/></svg>
          </a>
          <a href="{{ $whatsapp['defaut'] }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full border border-cream/20 grid place-items-center hover:bg-gold hover:border-gold hover:text-forest-dark" aria-label="WhatsApp">
            <svg viewBox="0 0 32 32" class="w-4 h-4 fill-current"><path d="M19.11 17.2c-.28-.14-1.63-.8-1.88-.9-.25-.09-.44-.14-.62.14-.18.28-.72.9-.88 1.08-.16.18-.32.2-.6.07-.28-.14-1.18-.44-2.25-1.4-.83-.74-1.4-1.66-1.56-1.94-.16-.28-.02-.43.12-.57.13-.13.28-.34.42-.5.14-.17.18-.28.28-.47.09-.19.05-.35-.02-.5-.07-.14-.62-1.5-.85-2.05-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.47.07-.71.35-.25.28-.94.92-.94 2.24s.96 2.6 1.1 2.78c.14.18 1.9 2.9 4.6 4.06.64.28 1.15.44 1.54.57.65.2 1.24.18 1.71.11.52-.08 1.63-.66 1.86-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.53-.32z"></path><path d="M16 3C9.37 3 4 8.37 4 15c0 2.34.66 4.53 1.8 6.39L4 29l7.83-1.75A11.94 11.94 0 0 0 16 27c6.63 0 12-5.37 12-12S22.63 3 16 3zm0 21.8c-2.02 0-3.92-.58-5.52-1.58l-.4-.24-4.64 1.04 1-4.52-.26-.42A9.77 9.77 0 0 1 6.2 15c0-5.4 4.4-9.8 9.8-9.8s9.8 4.4 9.8 9.8-4.4 9.8-9.8 9.8z"></path></svg>
          </a>
        </div>
      </div>

      <div>
        <p class="font-display font-semibold text-cream mb-4">Liens rapides</p>
        <ul class="space-y-2.5 text-sm">
          <li><a href="#apropos" class="hover:text-gold">Le restaurant</a></li>
          <li><a href="#menu" class="hover:text-gold">Notre carte</a></li>
          <li><a href="#services" class="hover:text-gold">Services</a></li>
          <li><a href="#localisation" class="hover:text-gold">Nous trouver</a></li>
        </ul>
      </div>

      <div>
        <p class="font-display font-semibold text-cream mb-4">Services</p>
        <ul class="space-y-2.5 text-sm">
          @foreach(array_slice($services, 0, 4) as $service)
            <li><a href="#services" class="hover:text-gold">{{ $service['titre'] }}</a></li>
          @endforeach
        </ul>
      </div>

      <div>
        <p class="font-display font-semibold text-cream mb-4">Contact</p>
        <ul class="space-y-2.5 text-sm text-cream/70">
          @if($parametres->adresse)<li>{{ $parametres->adresse }}</li>@endif
          @if($parametres->telephone)<li>{{ $parametres->telephone }}</li>@endif
          @if($parametres->email)<li>{{ $parametres->email }}</li>@endif
        </ul>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-5 sm:px-8 mt-14 pt-6 border-t border-cream/10 flex flex-col sm:flex-row justify-between gap-3 text-xs text-cream/50">
      <span>© {{ date('Y') }} {{ $parametres->nom_affichage }}. Tous droits réservés.</span>
      @if($parametres->mention_legale)<span>{{ $parametres->mention_legale }}</span>@endif
    </div>
  </footer>

  {{-- ============ CHATBOT / BOUTON FLOTTANT WHATSAPP ============ --}}
  <div class="fixed bottom-5 right-5 z-[80]" x-data="{ chatOpen:false }">
    <div x-show="chatOpen" x-cloak x-transition
         class="mb-4 w-[19rem] bg-white rounded-2xl shadow-2xl overflow-hidden border border-forest/10">
      <div class="bg-forest px-5 py-4 flex items-center gap-3">
        <img src="{{ $parametres->logo_url }}" class="w-9 h-9 rounded-full object-cover" alt="">
        <div>
          <p class="text-cream font-semibold text-sm leading-tight">{{ $parametres->nom_affichage }}</p>
          <p class="text-cream/60 text-xs flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block"></span> En ligne sur WhatsApp</p>
        </div>
      </div>
      <div class="p-4 space-y-2.5 max-h-72 overflow-y-auto">
        <p class="text-sm text-ink/70 bg-cream rounded-xl rounded-tl-none px-3.5 py-2.5">
          Bonjour 👋 Je peux vous aider à commander, réserver une table ou obtenir des infos sur nos services. Que souhaitez-vous faire ?
        </p>
        <div class="grid gap-2 pt-1">
          <a href="{{ $whatsapp['commander'] }}" target="_blank" rel="noopener" class="text-left text-sm font-medium border border-forest/15 hover:bg-forest hover:text-cream rounded-xl px-3.5 py-2.5">🍽️ Passer une commande</a>
          <a href="{{ $whatsapp['reserver'] }}" target="_blank" rel="noopener" class="text-left text-sm font-medium border border-forest/15 hover:bg-forest hover:text-cream rounded-xl px-3.5 py-2.5">📅 Réserver une table</a>
          <a href="{{ $whatsapp['traiteur'] }}" target="_blank" rel="noopener" class="text-left text-sm font-medium border border-forest/15 hover:bg-forest hover:text-cream rounded-xl px-3.5 py-2.5">🎉 Service traiteur</a>
          <a href="{{ $whatsapp['contact'] }}" target="_blank" rel="noopener" class="text-left text-sm font-medium border border-forest/15 hover:bg-forest hover:text-cream rounded-xl px-3.5 py-2.5">💬 Une autre question</a>
        </div>
      </div>
      <div class="p-3 border-t border-forest/10">
        <a href="{{ $whatsapp['defaut'] }}" target="_blank" rel="noopener"
           class="w-full inline-flex items-center justify-center gap-2 bg-spice hover:bg-spice-dark text-cream font-semibold text-sm px-4 py-2.5 rounded-full">
          Ouvrir WhatsApp
        </a>
      </div>
    </div>

    <button @click="chatOpen = !chatOpen"
            class="w-16 h-16 rounded-full bg-spice hover:bg-spice-dark shadow-2xl grid place-items-center text-cream"
            aria-label="Ouvrir le chat WhatsApp">
      <svg x-show="!chatOpen" viewBox="0 0 32 32" class="w-8 h-8 fill-current"><path d="M19.11 17.2c-.28-.14-1.63-.8-1.88-.9-.25-.09-.44-.14-.62.14-.18.28-.72.9-.88 1.08-.16.18-.32.2-.6.07-.28-.14-1.18-.44-2.25-1.4-.83-.74-1.4-1.66-1.56-1.94-.16-.28-.02-.43.12-.57.13-.13.28-.34.42-.5.14-.17.18-.28.28-.47.09-.19.05-.35-.02-.5-.07-.14-.62-1.5-.85-2.05-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.47.07-.71.35-.25.28-.94.92-.94 2.24s.96 2.6 1.1 2.78c.14.18 1.9 2.9 4.6 4.06.64.28 1.15.44 1.54.57.65.2 1.24.18 1.71.11.52-.08 1.63-.66 1.86-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.53-.32z"></path><path d="M16 3C9.37 3 4 8.37 4 15c0 2.34.66 4.53 1.8 6.39L4 29l7.83-1.75A11.94 11.94 0 0 0 16 27c6.63 0 12-5.37 12-12S22.63 3 16 3zm0 21.8c-2.02 0-3.92-.58-5.52-1.58l-.4-.24-4.64 1.04 1-4.52-.26-.42A9.77 9.77 0 0 1 6.2 15c0-5.4 4.4-9.8 9.8-9.8s9.8 4.4 9.8 9.8-4.4 9.8-9.8 9.8z"></path></svg>
      <svg x-show="chatOpen" x-cloak viewBox="0 0 24 24" class="w-7 h-7 stroke-current fill-none" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
  </div>

</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => AOS.init({ once: true, duration: 700, easing: 'ease-out-cubic' }));

  function menuFilter(plats, categories, prixMax) {
    return {
      plats: plats,
      categories: categories,
      activeCategory: 'Tous',
      activeStars: 0,
      prixMax: prixMax,
      prix: prixMax,
      get filtered() {
        return this.plats.filter(p => {
          const matchCat = this.activeCategory === 'Tous' || p.categorie === this.activeCategory;
          const matchStars = this.activeStars === 0 || p.etoiles >= this.activeStars;
          const matchPrix = p.prix <= this.prix;
          return matchCat && matchStars && matchPrix;
        });
      }
    }
  }
</script>
</body>
</html>