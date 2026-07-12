@php
    // ══════════════════════════════════════════════════════════════
    // Changez 'light' en 'dark' (ou l'inverse) pour basculer TOUT le
    // site vers le thème clair ou sombre. C'est la seule ligne à
    // modifier : toutes les couleurs (fonds, textes, bordures) sont
    // pilotées par les variables CSS définies dans :root, qui lisent
    // cette valeur via l'attribut data-theme posé sur <html> juste
    // en dessous.
    // ══════════════════════════════════════════════════════════════
    $theme = 'light'; // 'light' ou 'dark'
@endphp
<!doctype html>
<html lang="fr" class="scroll-smooth" data-theme="{{ $theme }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $parametres->nom_affichage }} @if($parametres->slogan) — {{ $parametres->slogan }} @endif</title>
<meta name="description" content="{{ $parametres->description ?: ($parametres->nom_affichage.' — commandez, réservez ou faites-vous livrer en quelques clics.') }}">
<link rel="icon" href="{{ $parametres->logo_url }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>
<script>
  // ──────────────────────────────────────────────────────────────
  // Toutes les couleurs de la page sont pilotées par les variables
  // CSS définies dans :root (voir plus bas). Pour changer la
  // couleur de marque, l'accent "note/étoile" ou le vert de
  // confirmation/WhatsApp, il suffit de modifier les valeurs dans
  // :root — aucune classe HTML n'a besoin d'être touchée.
  // ──────────────────────────────────────────────────────────────
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          sans: ['"Inter"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        },
        colors: {
          brand: {
            50:  'var(--brand-50)',
            100: 'var(--brand-100)',
            200: 'var(--brand-200)',
            300: 'var(--brand-300)',
            600: 'var(--brand-600)',
            700: 'var(--brand-700)',
            900: 'var(--brand-900)',
          },
          success: {
            50:  'var(--success-50)',
            400: 'var(--success-400)',
            600: 'var(--success-600)',
            700: 'var(--success-700)',
          },
          rating: 'var(--rating)',
          ink: {
            DEFAULT: 'var(--ink)',
            dark: 'var(--ink-dark)',
          },
          // Neutres pilotés par le thème (clair/sombre) — voir $theme
          // tout en haut du fichier et le bloc :root plus bas.
          surface: {
            DEFAULT: 'var(--surface)',
            alt: 'var(--surface-alt)',
            subtle: 'var(--surface-subtle)',
            translucent: 'var(--surface-translucent)',
          },
          fg: {
            heading: 'var(--text-heading)',
            body: 'var(--text-body)',
            muted: 'var(--text-muted)',
          },
          line: {
            DEFAULT: 'var(--border)',
            strong: 'var(--border-strong)',
            soft: 'var(--border-soft)',
          },
        },
      }
    }
  }
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">

<style>
  /* ══════════════════════════════════════════════════════════════
     VARIABLES DE COULEUR — source unique pour toute la page.
     Pour changer l'identité visuelle (marque, note, confirmation),
     il suffit de modifier les valeurs ci-dessous : classes Tailwind
     (via tailwind.config plus haut) ET illustrations SVG en dessous
     s'actualisent automatiquement, aucune autre modification requise.
     ══════════════════════════════════════════════════════════════ */
  :root{
    /* Marque — orange flamme du logo (Chez Clarence). Constante dans
       les deux thèmes : l'orange reste lisible sur fond clair ou sombre. */
    --brand-50:  #FFF7ED;
    --brand-100: #FFEDD5;
    --brand-200: #FED7AA;
    --brand-300: #FDBA74;
    --brand-600: #EA580C;
    --brand-700: #C2410C;
    --brand-900: #7C2D12;

    /* Noir de marque — accent fort (bouton "Itinéraire GPS", etc.) */
    --ink:      #111111;
    --ink-dark: #262626;

    /* Notes / étoiles — reste ambre dans les deux thèmes */
    --rating: #F59E0B;

    /* Confirmation & WhatsApp — reste vert dans les deux thèmes */
    --success-50:  #ECFDF5;
    --success-400: #34D399;
    --success-600: #059669;
    --success-700: #047857;

    /* ── Neutres : PILOTÉS PAR LE THÈME (voir $theme en haut du fichier) ── */
    --surface:             #FFFFFF; /* fonds "blancs" (cartes, sections) */
    --surface-alt:          #F8FAFC; /* sections alternées, léger contraste */
    --surface-subtle:       #F1F5F9; /* vignettes, zones survolées */
    --surface-translucent:  rgba(255, 255, 255, .95); /* header, badges flottants */

    --text-heading: #0F172A; /* titres */
    --text-body:    #475569; /* texte courant */
    --text-muted:   #64748B; /* texte secondaire / discret */

    --border:        #E2E8F0; /* bordure par défaut */
    --border-strong: #CBD5E1; /* bordure de bouton/onglet */
    --border-soft:   #F1F5F9; /* séparateur discret */

    /* Neutres décoratifs des illustrations SVG */
    --neutral-plate:      #FFFFFF;
    --neutral-plate-edge: #E2E8F0;
    --neutral-shadow:     #F1F5F9;
  }

  /* ══════════════════════════════════════════════════════════════
     THÈME SOMBRE — activé quand $theme = 'dark' (attribut data-theme
     posé sur <html>). Seules les valeurs neutres et les teintes
     "pâles" (utilisées comme fonds de badge) sont réajustées ; les
     couleurs de marque, de note et de succès restent identiques.
     ══════════════════════════════════════════════════════════════ */
  html[data-theme="dark"]{
    --brand-50:  rgba(234, 88, 12, .14);
    --brand-100: rgba(234, 88, 12, .22);
    --brand-200: rgba(234, 88, 12, .32);

    --success-50: rgba(5, 150, 105, .16);

    --ink:      #3A3A41;
    --ink-dark: #4B4B54;

    --surface:            #121214;
    --surface-alt:         #1A1A1D;
    --surface-subtle:      #232327;
    --surface-translucent: rgba(18, 18, 20, .92);

    --text-heading: #F4F4F5;
    --text-body:    #C4C4CA;
    --text-muted:   #8B8B93;

    --border:        #2B2B30;
    --border-strong: #3A3A41;
    --border-soft:   #1F1F23;

    --neutral-plate:      #FFFFFF; /* une assiette reste blanche */
    --neutral-plate-edge: #3A3A41;
    --neutral-shadow:     rgba(0, 0, 0, .35);
  }

  html{ font-size: 16px; }
  body{ font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }

  .star-fill{ fill: var(--rating); }
  .star-empty{ fill:none; stroke: var(--rating); stroke-width:1.6; }

  .tabular{ font-variant-numeric: tabular-nums; }

  ::selection{ background: var(--brand-200); color: var(--brand-900); }
  a, button{ transition: all .2s ease; }
  :focus-visible{ outline: 3px solid var(--brand-600); outline-offset: 2px; border-radius:4px; }

  @media (prefers-reduced-motion: reduce){
    *{ animation-duration: .001ms !important; animation-iteration-count: 1 !important; scroll-behavior:auto !important; }
  }
  [x-cloak]{ display:none !important; }
</style>
</head>
<body class="bg-surface text-fg-body antialiased">

<div x-data="{ navOpen:false, scrolled:false }" x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 12)">

  {{-- ============ NAVBAR ============ --}}
  <header class="fixed top-0 inset-x-0 z-50 bg-surface-translucent backdrop-blur border-b border-line transition-shadow"
          :class="scrolled ? 'shadow-sm' : ''">
    <nav class="max-w-7xl mx-auto px-5 sm:px-8 h-18 flex items-center justify-between py-3">
      <a href="#accueil" class="flex items-center gap-3 shrink-0">
        <img src="{{ $parametres->logo_url }}" alt="{{ $parametres->nom_affichage }}" class="h-10 w-10 rounded-lg object-cover ring-1 ring-line">
        <span class="text-lg sm:text-xl font-bold text-fg-heading tracking-tight">{{ $parametres->nom_affichage }}</span>
      </a>

      <ul class="hidden lg:flex items-center gap-7 font-medium text-fg-body text-sm">
        <li><a href="#apropos" class="hover:text-brand-600">Le restaurant</a></li>
        <li><a href="#menu" class="hover:text-brand-600">Notre carte</a></li>
        <li><a href="#services" class="hover:text-brand-600">Services</a></li>
        <li><a href="#localisation" class="hover:text-brand-600">Nous trouver</a></li>
        <li><a href="#contact" class="hover:text-brand-600">Contact</a></li>
      </ul>

      <a href="{{ $whatsapp['commander'] }}" target="_blank" rel="noopener"
         class="hidden lg:inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold px-5 py-2.5 rounded-lg text-sm shadow-sm">
        <svg viewBox="0 0 32 32" class="w-4 h-4 fill-current"><path d="M19.11 17.2c-.28-.14-1.63-.8-1.88-.9-.25-.09-.44-.14-.62.14-.18.28-.72.9-.88 1.08-.16.18-.32.2-.6.07-.28-.14-1.18-.44-2.25-1.4-.83-.74-1.4-1.66-1.56-1.94-.16-.28-.02-.43.12-.57.13-.13.28-.34.42-.5.14-.17.18-.28.28-.47.09-.19.05-.35-.02-.5-.07-.14-.62-1.5-.85-2.05-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.47.07-.71.35-.25.28-.94.92-.94 2.24s.96 2.6 1.1 2.78c.14.18 1.9 2.9 4.6 4.06.64.28 1.15.44 1.54.57.65.2 1.24.18 1.71.11.52-.08 1.63-.66 1.86-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.53-.32z"></path><path d="M16 3C9.37 3 4 8.37 4 15c0 2.34.66 4.53 1.8 6.39L4 29l7.83-1.75A11.94 11.94 0 0 0 16 27c6.63 0 12-5.37 12-12S22.63 3 16 3zm0 21.8c-2.02 0-3.92-.58-5.52-1.58l-.4-.24-4.64 1.04 1-4.52-.26-.42A9.77 9.77 0 0 1 6.2 15c0-5.4 4.4-9.8 9.8-9.8s9.8 4.4 9.8 9.8-4.4 9.8-9.8 9.8z"></path></svg>
        Commander
      </a>

      <button @click="navOpen = !navOpen" class="lg:hidden text-fg-body p-2" aria-label="Ouvrir le menu">
        <svg x-show="!navOpen" viewBox="0 0 24 24" class="w-7 h-7 stroke-current fill-none" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <svg x-show="navOpen" x-cloak viewBox="0 0 24 24" class="w-7 h-7 stroke-current fill-none" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </nav>

    <div x-show="navOpen" x-cloak x-transition @click.away="navOpen=false"
         class="lg:hidden bg-surface border-t border-line px-6 pb-8 pt-4">
      <ul class="flex flex-col gap-4 font-medium text-fg-body text-base">
        <li><a @click="navOpen=false" href="#apropos" class="block py-1">Le restaurant</a></li>
        <li><a @click="navOpen=false" href="#menu" class="block py-1">Notre carte</a></li>
        <li><a @click="navOpen=false" href="#services" class="block py-1">Services</a></li>
        <li><a @click="navOpen=false" href="#localisation" class="block py-1">Nous trouver</a></li>
        <li><a @click="navOpen=false" href="#contact" class="block py-1">Contact</a></li>
        <li>
          <a href="{{ $whatsapp['commander'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 bg-brand-600 text-white font-semibold px-5 py-2.5 rounded-lg text-sm mt-2">
            Commander sur WhatsApp
          </a>
        </li>
      </ul>
    </div>
  </header>

  {{-- ============ HERO ============ --}}
  <section id="accueil" class="relative bg-gradient-to-b from-surface-alt to-surface pt-32 pb-20 overflow-hidden">
    <div class="absolute top-0 right-0 w-[32rem] h-[32rem] rounded-full bg-brand-50 blur-3xl -translate-y-1/3 translate-x-1/4"></div>

    <div class="relative max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-2 gap-16 items-center">
      <div data-aos="fade-up">
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-brand-700 bg-brand-50 border border-brand-100 rounded-full px-3.5 py-1.5">
          {{ $parametres->ville ?: 'Douala' }} · Cuisine locale &amp; grillades
        </span>
        <h1 class="mt-6 font-extrabold text-fg-heading text-[clamp(2.3rem,5vw,3.75rem)] leading-[1.08] tracking-tight">
          {{ $parametres->nom_affichage }}
        </h1>
        @if($parametres->slogan)
          <p class="mt-3 text-brand-600 font-semibold text-lg sm:text-xl">{{ $parametres->slogan }}</p>
        @endif
        <p class="mt-5 text-fg-body text-base sm:text-lg max-w-xl leading-relaxed">
          {{ $parametres->description ?: "Des plats mijotés avec soin, une salle chaleureuse et un service pensé pour vous : commandez, réservez ou faites-vous livrer en quelques secondes." }}
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
          <a href="{{ $whatsapp['commander'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3.5 rounded-lg shadow-sm">
            Commander maintenant
          </a>
          <a href="{{ $whatsapp['reserver'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 bg-surface border border-line-strong text-fg-body hover:bg-surface-alt font-semibold px-6 py-3.5 rounded-lg">
            Réserver une table
          </a>
          <a href="{{ $whatsapp['traiteur'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 text-brand-600 hover:text-brand-700 font-semibold px-3 py-3.5">
            Découvrir le traiteur →
          </a>
        </div>

        <div class="mt-9 flex flex-wrap items-center gap-6 text-fg-muted text-sm">
          <div class="flex items-center gap-2">
            <svg viewBox="0 0 24 24" class="w-5 h-5 fill-rating"><path d="M12 2l2.9 6.26L21.5 9l-5 4.64L17.8 21 12 17.3 6.2 21l1.3-7.36-5-4.64 6.6-.74z"/></svg>
            <span>Plats notés par nos habitués</span>
          </div>
          <div class="flex items-center gap-2">
            <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-brand-600 fill-none" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span>Livraison &amp; réservation instantanées</span>
          </div>
        </div>
      </div>

      <div class="relative" data-aos="fade-left" data-aos-delay="100">
        <div class="bg-surface rounded-2xl border border-line shadow-xl p-8">
          <svg viewBox="0 0 360 300" class="w-full">
            <circle cx="180" cy="150" r="110" fill="var(--brand-50)"/>
            <ellipse cx="180" cy="230" rx="120" ry="14" fill="var(--neutral-shadow)"/>
            <ellipse cx="180" cy="165" rx="118" ry="36" fill="var(--neutral-plate)" stroke="var(--neutral-plate-edge)" stroke-width="2"/>
            <ellipse cx="180" cy="158" rx="92" ry="24" fill="var(--brand-100)" opacity="0.6"/>
            <ellipse cx="130" cy="152" rx="20" ry="12" fill="var(--rating)"/>
            <ellipse cx="175" cy="146" rx="24" ry="14" fill="var(--success-600)"/>
            <ellipse cx="222" cy="153" rx="18" ry="11" fill="var(--brand-600)"/>
            <circle cx="290" cy="70" r="24" fill="var(--ink)"/>
            <text x="290" y="77" text-anchor="middle" font-size="20" font-weight="700" fill="var(--brand-600)">★</text>
          </svg>
        </div>
        <div class="absolute -bottom-5 -left-5 bg-surface rounded-xl border border-line shadow-lg px-5 py-3.5 hidden sm:flex items-center gap-3">
          <span class="w-9 h-9 rounded-full bg-success-50 grid place-items-center">
            <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-success-600 fill-none" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          </span>
          <div class="text-sm">
            <p class="font-semibold text-fg-heading">Commande confirmée</p>
            <p class="text-fg-muted text-xs">en quelques secondes sur WhatsApp</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ A PROPOS ============ --}}
  <section id="apropos" class="py-20 sm:py-28 bg-surface">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-2 gap-16 items-center">

      <div data-aos="fade-right" class="order-2 lg:order-1">
        <span class="text-xs font-semibold uppercase tracking-wide text-brand-700">{{ $parametres->entreprise ?: 'Notre maison' }}</span>
        <h2 class="mt-2 font-extrabold text-3xl sm:text-4xl text-fg-heading leading-tight tracking-tight">
          Une cuisine préparée avec soin, depuis {{ $parametres->ville ?: 'Douala' }}
        </h2>
        <p class="mt-5 text-fg-body text-base sm:text-lg leading-relaxed">
          {{ $parametres->description ?: "Chez nous, chaque plat raconte une histoire : celle d'ingrédients choisis avec soin, de recettes transmises et d'un service qui vous traite comme un membre de la famille." }}
        </p>

        <div class="mt-9 grid sm:grid-cols-2 gap-4">
          @php
            $valeurs = [
              ['titre' => 'Fraîcheur', 'texte' => 'Des produits sélectionnés chaque jour.', 'icone' => 'feuille'],
              ['titre' => 'Savoir-faire', 'texte' => 'Des recettes maîtrisées avec passion.', 'icone' => 'toque-mini'],
              ['titre' => 'Hospitalité', 'texte' => "Un accueil chaleureux à chaque visite.", 'icone' => 'coeur'],
              ['titre' => 'Rapidité', 'texte' => 'Commande, réservation et livraison sans attente.', 'icone' => 'eclair'],
            ];
          @endphp
          @foreach($valeurs as $valeur)
            <div class="flex items-start gap-3.5 bg-surface-alt border border-surface-subtle rounded-xl p-4">
              <span class="shrink-0 w-10 h-10 rounded-lg bg-brand-50 grid place-items-center">
                @switch($valeur['icone'])
                  @case('feuille')
                    <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-brand-600 fill-none" stroke-width="1.7"><path stroke-linecap="round" d="M5 20c9 0 14-5 14-14 0 0-14 0-14 14zM5 20c0-6 2-9 6-11"/></svg>
                    @break
                  @case('toque-mini')
                    <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-brand-600 fill-none" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12c-1.5 0-2.5-1.4-2-2.8.4-1 1.5-1.6 2.5-1.4.2-1.8 1.8-3.1 3.6-2.9C10.4 3.7 11.6 3 13 3c2 0 3.6 1.6 3.6 3.6 0 .2 0 .3 0 .5 1.3 0 2.4 1 2.4 2.3 0 1.4-1.1 2.6-2.6 2.6H6z"/><path d="M6.5 12v6.5A1.5 1.5 0 008 20h8a1.5 1.5 0 001.5-1.5V12"/></svg>
                    @break
                  @case('coeur')
                    <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-brand-600 fill-none" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.4-9.5-9C1 7.8 2.6 5 5.8 5c1.8 0 3.3 1 4.2 2.5C11 6 12.4 5 14.2 5c3.2 0 4.8 2.8 3.3 6-2.5 4.6-9.5 9-9.5 9z"/></svg>
                    @break
                  @default
                    <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-brand-600 fill-none" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M13 2L4 14h7l-1 8 9-12h-7l1-8z"/></svg>
                @endswitch
              </span>
              <div>
                <p class="font-semibold text-fg-heading text-sm">{{ $valeur['titre'] }}</p>
                <p class="text-sm text-fg-muted mt-0.5">{{ $valeur['texte'] }}</p>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-9 flex flex-wrap gap-3">
          <a href="{{ $whatsapp['commander'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 rounded-lg">
            Commander
          </a>
          <a href="{{ $whatsapp['reserver'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 border border-line-strong text-fg-body hover:bg-surface-alt font-semibold px-6 py-3 rounded-lg">
            Réserver
          </a>
          <a href="{{ $whatsapp['contact'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 text-brand-600 hover:text-brand-700 font-semibold px-3 py-3">
            Nous contacter →
          </a>
        </div>
      </div>

      <div data-aos="fade-left" class="order-1 lg:order-2 relative flex justify-center">
        <div class="bg-surface-alt rounded-2xl border border-line p-10 w-full max-w-sm">
          <svg viewBox="0 0 260 300">
            <rect x="70" y="120" width="120" height="130" rx="16" fill="var(--ink)"/>
            <circle cx="130" cy="90" r="52" fill="var(--brand-50)" stroke="var(--brand-600)" stroke-width="2.5"/>
            <path d="M80 90a50 50 0 01100 0z" fill="var(--neutral-plate)" stroke="var(--brand-600)" stroke-width="2.5"/>
            <rect x="100" y="180" width="60" height="8" rx="4" fill="var(--brand-300)"/>
            <rect x="100" y="198" width="42" height="6" rx="3" fill="var(--brand-200)"/>
            <circle cx="130" cy="90" r="8" fill="var(--brand-600)"/>
            <path d="M108 66 q22 -16 44 0" stroke="var(--brand-600)" stroke-width="3" fill="none" stroke-linecap="round"/>
            <circle cx="210" cy="200" r="26" fill="var(--success-600)"/>
            <path d="M198 200l8 8 16-16" stroke="var(--neutral-plate)" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ MENU ============ --}}
  <section id="menu" class="py-20 sm:py-28 bg-surface-alt border-y border-surface-subtle">
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
        <span class="text-xs font-semibold uppercase tracking-wide text-brand-700">Notre carte</span>
        <h2 class="mt-2 font-extrabold text-3xl sm:text-4xl text-fg-heading tracking-tight">Choisissez, on s'occupe du reste</h2>
        <p class="mt-3 text-fg-body">Les plats les plus commandés par nos clients gagnent des étoiles — filtrez par catégorie, par note ou par budget.</p>
      </div>

      {{-- Filtres --}}
      <div class="mt-12 flex flex-col gap-5" data-aos="fade-up">
        <div class="flex flex-wrap justify-center gap-2">
          <button @click="activeCategory='Tous'"
                  :class="activeCategory==='Tous' ? 'bg-brand-600 text-white border-brand-600' : 'bg-surface text-fg-body border-line-strong hover:bg-surface-subtle'"
                  class="px-4 py-2 rounded-lg text-sm font-semibold border">Tous les plats</button>
          @foreach($categories as $categorie)
            <button @click="activeCategory='{{ $categorie->intitule }}'"
                    :class="activeCategory==='{{ $categorie->intitule }}' ? 'bg-brand-600 text-white border-brand-600' : 'bg-surface text-fg-body border-line-strong hover:bg-surface-subtle'"
                    class="px-4 py-2 rounded-lg text-sm font-semibold border">
              {{ $categorie->intitule }} <span class="opacity-70">({{ $categorie->menusActifs->count() }})</span>
            </button>
          @endforeach
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10 bg-surface border border-line rounded-xl px-6 py-5">
          <div class="flex items-center gap-2">
            <span class="text-fg-body text-sm font-medium mr-1">Note minimum :</span>
            @for($i=1;$i<=5;$i++)
              <button @click="activeStars = activeStars === {{ $i }} ? 0 : {{ $i }}" class="p-0.5" aria-label="{{ $i }} étoiles minimum">
                <svg viewBox="0 0 24 24" class="w-5 h-5" :class="activeStars >= {{ $i }} ? 'star-fill' : 'star-empty'"><path d="M12 2l2.9 6.26L21.5 9l-5 4.64L17.8 21 12 17.3 6.2 21l1.3-7.36-5-4.64 6.6-.74z"/></svg>
              </button>
            @endfor
          </div>

          <div class="flex items-center gap-3 w-full sm:w-64">
            <span class="text-fg-body text-sm font-medium whitespace-nowrap">Prix max :</span>
            <input type="range" x-model.number="prix" min="0" :max="prixMax" step="100" class="w-full accent-brand-600">
            <span class="tabular text-brand-700 text-sm font-semibold whitespace-nowrap" x-text="prix.toLocaleString('fr-FR') + ' {{ $parametres->devise }}'"></span>
          </div>
        </div>
      </div>

      {{-- Grille des plats --}}
      <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="plat in filtered" :key="plat.id">
          <article class="bg-surface rounded-xl border border-line shadow-sm hover:shadow-md hover:-translate-y-0.5 transition overflow-hidden flex flex-col" data-aos="fade-up">
            <div class="h-40 bg-surface-subtle relative overflow-hidden">
              <img :src="plat.photo" :alt="plat.nom" class="w-full h-full object-cover">
              <span class="absolute top-3 left-3 bg-surface-translucent text-brand-700 text-[11px] font-semibold uppercase tracking-wide px-2.5 py-1 rounded-full border border-brand-100" x-text="plat.categorie"></span>
            </div>

            <div class="p-5 flex flex-col grow">
              <div class="flex items-start justify-between gap-3">
                <h3 class="font-semibold text-base text-fg-heading leading-snug" x-text="plat.nom"></h3>
                <span class="tabular text-brand-600 font-bold whitespace-nowrap" x-text="plat.prix.toLocaleString('fr-FR') + ' {{ $parametres->devise }}'"></span>
              </div>
              <p class="mt-1.5 text-sm text-fg-muted leading-relaxed grow" x-text="plat.description || 'Une spécialité préparée maison.'"></p>

              <div class="mt-4 pt-4 border-t border-surface-subtle flex items-center justify-between">
                <div class="flex items-center gap-0.5">
                  <template x-for="n in 5" :key="n">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" :class="n <= plat.etoiles ? 'star-fill' : 'star-empty'"><path d="M12 2l2.9 6.26L21.5 9l-5 4.64L17.8 21 12 17.3 6.2 21l1.3-7.36-5-4.64 6.6-.74z"/></svg>
                  </template>
                </div>
                <a :href="'https://wa.me/{{ $whatsapp['numero'] }}?text=' + encodeURIComponent('Bonjour, je souhaite commander : ' + plat.nom + ' — ' + plat.prix.toLocaleString('fr-FR') + ' {{ $parametres->devise }}')"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                  Commander
                </a>
              </div>
            </div>
          </article>
        </template>

        <template x-if="filtered.length === 0">
          <div class="col-span-full text-center py-16">
            <p class="text-fg-muted">Aucun plat ne correspond à ces filtres pour le moment.</p>
          </div>
        </template>
      </div>
    </div>
  </section>

  {{-- ============ SERVICES ============ --}}
  <section id="services" class="py-20 sm:py-28 bg-surface" x-data="{ open:false, currentId:null }">
    <div class="max-w-7xl mx-auto px-5 sm:px-8">
      <div class="text-center max-w-2xl mx-auto" data-aos="fade-up">
        <span class="text-xs font-semibold uppercase tracking-wide text-brand-700">Au-delà de l'assiette</span>
        <h2 class="mt-2 font-extrabold text-3xl sm:text-4xl text-fg-heading tracking-tight">Nos services</h2>
        <p class="mt-3 text-fg-body">Pour vos événements, vos cadeaux ou vos repas d'entreprise, nous nous adaptons à vos envies.</p>
      </div>

      <div class="mt-14 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($services as $service)
          <div class="group bg-surface rounded-xl p-6 shadow-sm hover:shadow-md transition border border-line" data-aos="fade-up" data-aos-delay="{{ $loop->index * 60 }}">
            <span class="inline-grid w-11 h-11 place-items-center rounded-lg bg-brand-50 group-hover:bg-brand-100">
              @switch($service['icone'])
                @case('cle')
                  <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-brand-600 fill-none" stroke-width="1.7"><circle cx="8" cy="8" r="4"/><path stroke-linecap="round" d="M11 11l9 9m-4-4l3-3m-6 6l-2-2"/></svg>
                  @break
                @case('toque')
                  <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-brand-600 fill-none" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12c-1.5 0-2.5-1.4-2-2.8.4-1 1.5-1.6 2.5-1.4.2-1.8 1.8-3.1 3.6-2.9C10.4 3.7 11.6 3 13 3c2 0 3.6 1.6 3.6 3.6 0 .2 0 .3 0 .5 1.3 0 2.4 1 2.4 2.3 0 1.4-1.1 2.6-2.6 2.6H6z"/><path d="M6.5 12v6.5A1.5 1.5 0 008 20h8a1.5 1.5 0 001.5-1.5V12"/></svg>
                  @break
                @case('cadeau')
                  <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-brand-600 fill-none" stroke-width="1.7"><rect x="4" y="9" width="16" height="11" rx="1.5"/><path d="M4 13h16M12 9v11"/><path stroke-linecap="round" d="M12 9C9 9 8 4 5.5 6S9 9 12 9zm0 0c3 0 4-5 6.5-3S15 9 12 9z"/></svg>
                  @break
                @case('scooter')
                  <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-brand-600 fill-none" stroke-width="1.7"><circle cx="6" cy="18" r="2.4"/><circle cx="18" cy="18" r="2.4"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 18h5l2-6h4M11 12l2-3h3M8 18v-4l-3-2"/></svg>
                  @break
                @case('ballons')
                  <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-brand-600 fill-none" stroke-width="1.7"><ellipse cx="8" cy="7" rx="4" ry="5"/><ellipse cx="16" cy="9" rx="3.4" ry="4.2"/><path stroke-linecap="round" d="M8 12l-1 8m9-11l-1 7"/></svg>
                  @break
                @default
                  <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-brand-600 fill-none" stroke-width="1.7"><circle cx="9" cy="8" r="2.6"/><circle cx="17" cy="9" r="2.2"/><path stroke-linecap="round" d="M4 19c0-3 2.5-5 5-5s5 2 5 5M14 19c.3-2.4 2-4 4.5-4S23 17 23 19"/></svg>
              @endswitch
            </span>
            <h3 class="mt-4 font-semibold text-lg text-fg-heading">{{ $service['titre'] }}</h3>
            <p class="mt-1.5 text-sm text-fg-muted leading-relaxed">{{ $service['resume'] }}</p>
            <button @click="open=true; currentId='{{ $service['id'] }}'" class="mt-4 inline-flex items-center gap-1.5 text-brand-600 font-semibold text-sm hover:text-brand-700">
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
        <div @click="open=false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-surface rounded-xl max-w-lg w-full p-8 shadow-2xl border border-line" x-transition
             @click.outside="open=false">
          <button @click="open=false" class="absolute top-4 right-4 text-fg-muted hover:text-fg-body" aria-label="Fermer">
            <svg viewBox="0 0 24 24" class="w-6 h-6 stroke-current fill-none" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
          </button>
          <h3 class="font-bold text-xl text-fg-heading pr-6">{{ $service['titre'] }}</h3>
          <p class="mt-4 text-fg-body leading-relaxed">{{ $service['description'] }}</p>
          <a href="{{ $service['lien_whatsapp'] }}" target="_blank" rel="noopener"
             class="mt-7 inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 rounded-lg">
            Demander un devis sur WhatsApp
          </a>
        </div>
      </div>
    @endforeach
  </section>

  {{-- ============ LOCALISATION ============ --}}
  <section id="localisation" class="py-20 sm:py-28 bg-surface-alt border-y border-surface-subtle">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-2 gap-12 items-stretch">
      <div data-aos="fade-right" class="flex flex-col justify-center">
        <span class="text-xs font-semibold uppercase tracking-wide text-brand-700">Nous trouver</span>
        <h2 class="mt-2 font-extrabold text-3xl sm:text-4xl text-fg-heading tracking-tight">Passez nous voir</h2>
        <p class="mt-4 text-fg-body leading-relaxed">{{ $parametres->adresse ?: 'Adresse à venir' }}</p>

        <ul class="mt-6 space-y-3 text-fg-body text-sm">
          @if($parametres->horaires)
            <li class="flex items-center gap-3">
              <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-brand-600 fill-none shrink-0" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3.5 2"/></svg>
              {{ $parametres->horaires }}
            </li>
          @endif
          @if($parametres->telephone)
            <li class="flex items-center gap-3">
              <svg viewBox="0 0 24 24" class="w-5 h-5 stroke-brand-600 fill-none shrink-0" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5c0 8.3 6.7 15 15 15l3-4-6-3-2 2c-2-1-4-3-5-5l2-2-3-6z"/></svg>
              {{ $parametres->telephone }}@if($parametres->telephone2) · {{ $parametres->telephone2 }} @endif
            </li>
          @endif
        </ul>

        <div class="mt-8 flex flex-wrap gap-3">
          <a href="{{ $lienItineraire }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 bg-ink hover:bg-ink-dark text-white font-semibold px-6 py-3.5 rounded-lg shadow-sm">
            <svg viewBox="0 0 24 24" class="w-5 h-5 fill-none stroke-current" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11l18-7-7 18-2-8-9-3z"/></svg>
            Lancer l'itinéraire GPS
          </a>
          <a href="{{ $whatsapp['reserver'] }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 bg-surface border border-line-strong text-fg-body hover:bg-surface-subtle font-semibold px-6 py-3.5 rounded-lg">
            Réserver une table
          </a>
        </div>
      </div>

      <div class="rounded-xl overflow-hidden min-h-[22rem] shadow-sm border border-line" data-aos="fade-left">
        @if($parametres->latitude && $parametres->longitude)
          <iframe
            class="w-full h-full min-h-[22rem]"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            src="https://www.google.com/maps?q={{ $parametres->latitude }},{{ $parametres->longitude }}&z=16&output=embed">
          </iframe>
        @else
          <div class="w-full h-full min-h-[22rem] bg-surface grid place-items-center text-fg-muted text-sm">
            Localisation à venir
          </div>
        @endif
      </div>
    </div>
  </section>

  {{-- ============ CONTACT ============ --}}
  <section id="contact" class="py-20 sm:py-28 bg-surface">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-5 gap-12">

      <div class="lg:col-span-2" data-aos="fade-right">
        <span class="text-xs font-semibold uppercase tracking-wide text-brand-700">Contact</span>
        <h2 class="mt-2 font-extrabold text-3xl text-fg-heading tracking-tight">Parlons de votre prochain repas</h2>
        <p class="mt-4 text-fg-body leading-relaxed">Une question, une envie particulière ? Écrivez-nous, nous répondons rapidement sur WhatsApp.</p>

        <div class="mt-7 space-y-3 text-sm text-fg-body">
          @if($parametres->adresse)
            <div class="flex gap-3"><span class="text-brand-600 font-semibold">—</span><span>{{ $parametres->adresse }}</span></div>
          @endif
          @if($parametres->telephone)
            <div class="flex gap-3"><span class="text-brand-600 font-semibold">—</span><span>{{ $parametres->telephone }}</span></div>
          @endif
          @if($parametres->email)
            <div class="flex gap-3"><span class="text-brand-600 font-semibold">—</span><span>{{ $parametres->email }}</span></div>
          @endif
          @if($parametres->horaires)
            <div class="flex gap-3"><span class="text-brand-600 font-semibold">—</span><span>{{ $parametres->horaires }}</span></div>
          @endif
        </div>
      </div>

      <div class="lg:col-span-3" data-aos="fade-left"
           x-data="{ nom:'', telephone:'', sujet:'Commande', message:'' }">
        <form class="bg-surface rounded-xl p-7 sm:p-8 shadow-sm border border-line space-y-5"
              @submit.prevent="
                const texte = 'Bonjour, je suis ' + nom + '.%0ASujet : ' + sujet + '.%0ATéléphone : ' + telephone + '.%0AMessage : ' + message;
                window.open('https://wa.me/{{ $whatsapp['numero'] }}?text=' + texte, '_blank');
              ">
          <div class="grid sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-semibold text-fg-body mb-1.5">Nom</label>
              <input x-model="nom" required type="text" placeholder="Votre nom"
                     class="w-full rounded-lg border border-line-strong px-4 py-3 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
            </div>
            <div>
              <label class="block text-sm font-semibold text-fg-body mb-1.5">Téléphone</label>
              <input x-model="telephone" required type="tel" placeholder="+237 6xx xx xx xx"
                     class="w-full rounded-lg border border-line-strong px-4 py-3 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-fg-body mb-1.5">Sujet</label>
            <select x-model="sujet" class="w-full rounded-lg border border-line-strong px-4 py-3 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
              <option>Commande</option>
              <option>Réservation de table</option>
              <option>Service traiteur</option>
              <option>Privatisation</option>
              <option>Autre demande</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-fg-body mb-1.5">Message</label>
            <textarea x-model="message" required rows="4" placeholder="Votre message..."
                      class="w-full rounded-lg border border-line-strong px-4 py-3 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none"></textarea>
          </div>
          <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3.5 rounded-lg">
            Envoyer sur WhatsApp
          </button>
        </form>
      </div>
    </div>
  </section>

  {{-- ============ FOOTER ============ --}}
  <footer class="bg-slate-900 text-slate-300 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-10">
      <div>
        <span class="text-xl font-bold text-white">{{ $parametres->nom_affichage }}</span>
        <p class="mt-3 text-sm leading-relaxed text-slate-400">{{ $parametres->description ? \Illuminate\Support\Str::limit($parametres->description, 140) : 'Une cuisine généreuse, un accueil chaleureux.' }}</p>
        <div class="mt-5 flex gap-3">
          <a href="#" class="w-9 h-9 rounded-lg border border-slate-700 grid place-items-center hover:bg-brand-600 hover:border-brand-600" aria-label="Facebook">
            <svg viewBox="0 0 24 24" class="w-4 h-4 fill-current"><path d="M14 9h3V5h-3c-2.2 0-4 1.8-4 4v2H8v4h2v6h4v-6h3l1-4h-4V9c0-.6.4-1 1-1z"/></svg>
          </a>
          <a href="#" class="w-9 h-9 rounded-lg border border-slate-700 grid place-items-center hover:bg-brand-600 hover:border-brand-600" aria-label="Instagram">
            <svg viewBox="0 0 24 24" class="w-4 h-4 fill-none stroke-current" stroke-width="1.8"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1"/></svg>
          </a>
          <a href="{{ $whatsapp['defaut'] }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg border border-slate-700 grid place-items-center hover:bg-success-600 hover:border-success-600" aria-label="WhatsApp">
            <svg viewBox="0 0 32 32" class="w-4 h-4 fill-current"><path d="M19.11 17.2c-.28-.14-1.63-.8-1.88-.9-.25-.09-.44-.14-.62.14-.18.28-.72.9-.88 1.08-.16.18-.32.2-.6.07-.28-.14-1.18-.44-2.25-1.4-.83-.74-1.4-1.66-1.56-1.94-.16-.28-.02-.43.12-.57.13-.13.28-.34.42-.5.14-.17.18-.28.28-.47.09-.19.05-.35-.02-.5-.07-.14-.62-1.5-.85-2.05-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.47.07-.71.35-.25.28-.94.92-.94 2.24s.96 2.6 1.1 2.78c.14.18 1.9 2.9 4.6 4.06.64.28 1.15.44 1.54.57.65.2 1.24.18 1.71.11.52-.08 1.63-.66 1.86-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.53-.32z"></path><path d="M16 3C9.37 3 4 8.37 4 15c0 2.34.66 4.53 1.8 6.39L4 29l7.83-1.75A11.94 11.94 0 0 0 16 27c6.63 0 12-5.37 12-12S22.63 3 16 3zm0 21.8c-2.02 0-3.92-.58-5.52-1.58l-.4-.24-4.64 1.04 1-4.52-.26-.42A9.77 9.77 0 0 1 6.2 15c0-5.4 4.4-9.8 9.8-9.8s9.8 4.4 9.8 9.8-4.4 9.8-9.8 9.8z"></path></svg>
          </a>
        </div>
      </div>

      <div>
        <p class="font-semibold text-white mb-4">Liens rapides</p>
        <ul class="space-y-2.5 text-sm">
          <li><a href="#apropos" class="hover:text-white">Le restaurant</a></li>
          <li><a href="#menu" class="hover:text-white">Notre carte</a></li>
          <li><a href="#services" class="hover:text-white">Services</a></li>
          <li><a href="#localisation" class="hover:text-white">Nous trouver</a></li>
        </ul>
      </div>

      <div>
        <p class="font-semibold text-white mb-4">Services</p>
        <ul class="space-y-2.5 text-sm">
          @foreach(array_slice($services, 0, 4) as $service)
            <li><a href="#services" class="hover:text-white">{{ $service['titre'] }}</a></li>
          @endforeach
        </ul>
      </div>

      <div>
        <p class="font-semibold text-white mb-4">Contact</p>
        <ul class="space-y-2.5 text-sm text-slate-400">
          @if($parametres->adresse)<li>{{ $parametres->adresse }}</li>@endif
          @if($parametres->telephone)<li>{{ $parametres->telephone }}</li>@endif
          @if($parametres->email)<li>{{ $parametres->email }}</li>@endif
        </ul>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-5 sm:px-8 mt-12 pt-6 border-t border-slate-800 flex flex-col sm:flex-row justify-between gap-3 text-xs text-slate-500">
      <span>© {{ date('Y') }} {{ $parametres->nom_affichage }}. Tous droits réservés.</span>
      @if($parametres->mention_legale)<span>{{ $parametres->mention_legale }}</span>@endif
    </div>
  </footer>

  {{-- ============ CHATBOT / BOUTON FLOTTANT WHATSAPP ============ --}}
  <div class="fixed bottom-5 right-5 z-[80]" x-data="{ chatOpen:false }">
    <div x-show="chatOpen" x-cloak x-transition
         class="mb-4 w-[19rem] bg-surface rounded-xl shadow-2xl overflow-hidden border border-line">
      <div class="bg-brand-600 px-5 py-4 flex items-center gap-3">
        <img src="{{ $parametres->logo_url }}" class="w-9 h-9 rounded-lg object-cover" alt="">
        <div>
          <p class="text-white font-semibold text-sm leading-tight">{{ $parametres->nom_affichage }}</p>
          <p class="text-brand-100 text-xs flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-success-400 inline-block"></span> En ligne sur WhatsApp</p>
        </div>
      </div>
      <div class="p-4 space-y-2.5 max-h-72 overflow-y-auto">
        <p class="text-sm text-fg-body bg-surface-alt rounded-lg rounded-tl-none px-3.5 py-2.5">
          Bonjour 👋 Je peux vous aider à commander, réserver une table ou obtenir des infos sur nos services. Que souhaitez-vous faire ?
        </p>
        <div class="grid gap-2 pt-1">
          <a href="{{ $whatsapp['commander'] }}" target="_blank" rel="noopener" class="text-left text-sm font-medium border border-line hover:bg-brand-50 hover:border-brand-300 text-fg-body rounded-lg px-3.5 py-2.5">🍽️ Passer une commande</a>
          <a href="{{ $whatsapp['reserver'] }}" target="_blank" rel="noopener" class="text-left text-sm font-medium border border-line hover:bg-brand-50 hover:border-brand-300 text-fg-body rounded-lg px-3.5 py-2.5">📅 Réserver une table</a>
          <a href="{{ $whatsapp['traiteur'] }}" target="_blank" rel="noopener" class="text-left text-sm font-medium border border-line hover:bg-brand-50 hover:border-brand-300 text-fg-body rounded-lg px-3.5 py-2.5">🎉 Service traiteur</a>
          <a href="{{ $whatsapp['contact'] }}" target="_blank" rel="noopener" class="text-left text-sm font-medium border border-line hover:bg-brand-50 hover:border-brand-300 text-fg-body rounded-lg px-3.5 py-2.5">💬 Une autre question</a>
        </div>
      </div>
      <div class="p-3 border-t border-surface-subtle">
        <a href="{{ $whatsapp['defaut'] }}" target="_blank" rel="noopener"
           class="w-full inline-flex items-center justify-center gap-2 bg-success-600 hover:bg-success-700 text-white font-semibold text-sm px-4 py-2.5 rounded-lg">
          Ouvrir WhatsApp
        </a>
      </div>
    </div>

    <button @click="chatOpen = !chatOpen"
            class="w-16 h-16 rounded-full bg-success-600 hover:bg-success-700 shadow-2xl grid place-items-center text-white"
            aria-label="Ouvrir le chat WhatsApp">
      <svg x-show="!chatOpen" viewBox="0 0 32 32" class="w-8 h-8 fill-current"><path d="M19.11 17.2c-.28-.14-1.63-.8-1.88-.9-.25-.09-.44-.14-.62.14-.18.28-.72.9-.88 1.08-.16.18-.32.2-.6.07-.28-.14-1.18-.44-2.25-1.4-.83-.74-1.4-1.66-1.56-1.94-.16-.28-.02-.43.12-.57.13-.13.28-.34.42-.5.14-.17.18-.28.28-.47.09-.19.05-.35-.02-.5-.07-.14-.62-1.5-.85-2.05-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.47.07-.71.35-.25.28-.94.92-.94 2.24s.96 2.6 1.1 2.78c.14.18 1.9 2.9 4.6 4.06.64.28 1.15.44 1.54.57.65.2 1.24.18 1.71.11.52-.08 1.63-.66 1.86-1.3.23-.64.23-1.19.16-1.3-.07-.11-.25-.18-.53-.32z"></path><path d="M16 3C9.37 3 4 8.37 4 15c0 2.34.66 4.53 1.8 6.39L4 29l7.83-1.75A11.94 11.94 0 0 0 16 27c6.63 0 12-5.37 12-12S22.63 3 16 3zm0 21.8c-2.02 0-3.92-.58-5.52-1.58l-.4-.24-4.64 1.04 1-4.52-.26-.42A9.77 9.77 0 0 1 6.2 15c0-5.4 4.4-9.8 9.8-9.8s9.8 4.4 9.8 9.8-4.4 9.8-9.8 9.8z"></path></svg>
      <svg x-show="chatOpen" x-cloak viewBox="0 0 24 24" class="w-7 h-7 stroke-current fill-none" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
  </div>

</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => AOS.init({ once: true, duration: 600, easing: 'ease-out-cubic' }));

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