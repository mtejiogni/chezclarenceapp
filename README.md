# RESTO-GESTION

Plateforme complète de gestion pour restaurant — commande, cuisine, caisse, livraison et vitrine publique — développée avec Laravel.

> Application de démonstration/production construite pour **Chez Clarence** (Restaurant · Snack · Grill — Douala, Cameroun, depuis 1990), par **HI-TECH Vision SARL**.

---

## Sommaire

1. [Présentation](#présentation)
2. [Fonctionnalités par rôle](#fonctionnalités-par-rôle)
3. [Stack technique](#stack-technique)
4. [Architecture du projet](#architecture-du-projet)
5. [Modèle de données](#modèle-de-données)
6. [Conventions du projet](#conventions-du-projet)
7. [Prérequis](#prérequis)
8. [Installation en local](#installation-en-local)
9. [Comptes de démonstration](#comptes-de-démonstration)
10. [Lancer le projet au quotidien](#lancer-le-projet-au-quotidien)
11. [Site vitrine public](#site-vitrine-public)
12. [API REST & application mobile](#api-rest--application-mobile)
13. [Déploiement en production](#déploiement-en-production)
14. [Sauvegarde, restauration & maintenance](#sauvegarde-restauration--maintenance)
15. [Sécurité](#sécurité)
16. [Commandes Artisan utiles](#commandes-artisan-utiles)
17. [Dépannage courant](#dépannage-courant)
18. [Licence & contact](#licence--contact)

---

## Présentation

RESTO-GESTION digitalise l'ensemble du parcours d'un restaurant :

- **Prise de commande** en salle, à emporter ou en livraison, avec suivi de statut en temps réel.
- **Cuisine** : file d'attente des plats à préparer, changement de statut d'une commande.
- **Caisse** : encaissement, génération de reçus PDF au format ticket thermique 80 mm.
- **Livraison** : suivi des courses en cours, mise à jour du statut par le livreur.
- **Administration** : gestion des utilisateurs, du menu, des catégories, des tables, des statistiques et des paramètres du restaurant.
- **Sauvegarde & restauration** : export SQL/CSV/PDF, corbeille avec restauration, import, purge sécurisée.
- **Vitrine publique** : page de présentation du restaurant permettant aux clients de commander, réserver une table, découvrir le service traiteur ou contacter le restaurant directement via WhatsApp — sans création de compte.
- **API REST** (JWT) : prévue pour une future application mobile, documentée via Swagger.

Le projet est architecturé pour que **la même base de code serve à la fois le back-office web (le personnel) et l'API mobile (les clients)**.

---

## Fonctionnalités par rôle

| Rôle | Accès principal |
|---|---|
| **Administrateur** | Tableau de bord global, gestion des utilisateurs, du menu, des catégories, des tables, statistiques, paramètres du restaurant, sauvegarde & restauration |
| **Caissier** | Encaissement, génération et impression des reçus, tableau de bord caisse |
| **Serveur** | Prise de commande en salle, suivi des commandes actives, association à une table |
| **Cuisinier** | File d'attente cuisine, prise en charge et passage au statut "prête" |
| **Livreur** | Suivi des livraisons en cours, mise à jour du statut (Livrée / Annulée) |
| **Client** | Consultation du menu et de l'historique de ses commandes (via l'app mobile à venir) ; sur le site public, commande/réservation directe via WhatsApp sans compte requis |

Le contrôle d'accès repose sur la colonne `role` de la table `users` et un middleware dédié (`role:Administrateur,Caissier`, logique **OU** entre plusieurs rôles autorisés).

---

## Stack technique

| Domaine | Choix technique |
|---|---|
| Framework backend | **Laravel** (PHP) |
| Base de données | **MySQL** |
| Moteur de vues | **Blade** |
| CSS (back-office) | **Tailwind CSS**, compilé via **Vite** + PostCSS + Autoprefixer |
| CSS (site vitrine public) | **Tailwind CSS** via CDN (aucun build requis pour cette page) |
| Interactivité front | **Alpine.js** |
| Icônes | **Font Awesome** (back-office), icônes SVG inline sur mesure (site vitrine) |
| Alertes / confirmations | **SweetAlert2** |
| Animations au scroll | **AOS** (site vitrine) |
| Authentification web | Sessions Laravel + CSRF + throttle sur la connexion |
| Authentification API | **JWT** via `tymon/jwt-auth` |
| Documentation API | **Swagger / OpenAPI** via `l5-swagger` |
| Génération PDF | **barryvdh/dompdf** (reçus de caisse, exports) |
| Archives | **ZipArchive** (module de sauvegarde) |
| Permissions | Table `permissions`/`roles` (Spatie) présentes en base mais **non utilisées activement** — le contrôle d'accès réel repose sur `users.role` + middleware `CheckRole` |
| Environnement de développement | WampServer (Windows), MySQL via phpMyAdmin |

---

## Architecture du projet

```
resto-gestion/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Web/                    ← Contrôleurs du back-office (session)
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── MenuController.php
│   │   │   │   ├── CommandeController.php
│   │   │   │   ├── LigneController.php
│   │   │   │   ├── HistoriqueController.php
│   │   │   │   ├── LivraisonController.php
│   │   │   │   ├── CaisseController.php
│   │   │   │   ├── StatistiqueController.php
│   │   │   │   ├── ParametreController.php
│   │   │   │   ├── SauvegardeController.php
│   │   │   │   └── SiteController.php   ← page vitrine publique
│   │   │   └── Api/                     ← Contrôleurs API REST (JWT, mobile)
│   │   │       ├── AuthApiController.php
│   │   │       ├── MenuApiController.php
│   │   │       ├── CommandeApiController.php
│   │   │       └── UserApiController.php
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php
│   │   │   └── ApiJwtMiddleware.php
│   │   └── Requests/                    ← Form Requests (validation)
│   ├── Models/
│   │   ├── User.php
│   │   ├── Parametre.php
│   │   ├── Categorie.php
│   │   ├── Menu.php
│   │   ├── TableResto.php
│   │   ├── Commande.php
│   │   ├── Ligne.php
│   │   ├── Statut.php
│   │   └── Historique.php
│   └── Services/                        ← Logique métier réutilisable
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php        ← Layout back-office (sidebar, thème sombre)
│   │   ├── site/index.blade.php         ← Page vitrine publique (autonome, thème clair)
│   │   ├── dashboard/, menu/, commande/, caisse/, livraison/,
│   │   │   sauvegarde/, parametre/, ...
│   ├── css/app.css
│   └── js/app.js
├── routes/
│   ├── web.php                          ← Routes back-office + site public
│   └── api.php                          ← Routes API (JWT)
├── storage/app/public/                  ← Fichiers uploadés (logos, photos plats)
├── public/storage → lien symbolique vers storage/app/public
├── vite.config.js
├── tailwind.config.js
└── .env
```

**Séparation `Web` / `Api`** : les contrôleurs `Web` gèrent les pages Blade consommées par le personnel (et les clients sur le site public), les contrôleurs `Api` exposent les mêmes fonctionnalités en JSON, authentifiées par JWT, pour la future application mobile.

---

## Modèle de données

| Table | Rôle |
|---|---|
| `users` | Comptes (personnel + clients), clé primaire `iduser`, champ `role`, soft delete (`deleted_at` + `void`) |
| `parametres` | Réglages uniques du restaurant : identité, coordonnées, WhatsApp, caisse/reçus. Toujours accéder via `Parametre::config()` |
| `categories` | Catégories du menu (clé primaire `idcategorie`) |
| `menus` | Plats (clé primaire `idmenu`), liés à une catégorie, prix unitaire (`pu`) |
| `tables` (modèle `TableResto`) | Tables physiques du restaurant (clé primaire `idtable`) |
| `commandes` | Commandes clients (clé primaire `idcommande`), type Standard / Livraison / À emporter, statut courant, référence `CMD-XXXXXX` |
| `lignes` | Lignes de commande (plat, quantité, remise, prix), sans timestamps |
| `statuts` | Référentiel des statuts de commande, ordonnés par `priorite` |
| `historiques` | Journal des changements de statut d'une commande |
| `permissions` / `roles` / `model_has_*` | Tables Spatie présentes mais non exploitées activement |

**Cycle de vie d'une commande** : `En attente` → `En préparation` → `Expédiée` *(livraison uniquement)* → `Livrée` / `Servie`, ou `Annulée` à tout moment tant que la commande est modifiable.

---

## Conventions du projet

Merci de respecter ces conventions déjà en place avant d'ajouter du code :

- **Clés primaires explicites** : chaque table utilise `id<entité>` (ex. `idmenu`, `idcommande`) plutôt que `id` générique.
- **Suppression douce à deux niveaux** : `deleted_at` (soft delete Eloquent standard) **et** un champ `void` utilisé comme marqueur de corbeille métier par le module Sauvegarde. Ne jamais supprimer définitivement une ligne dont `void` n'est pas déjà renseigné.
- **Champs `statut`** : valeurs textuelles françaises (`Activé` / `Désactivé`, `En attente` / `En préparation` / ...), jamais de booléens ou d'enums numériques.
- **Devise** : FCFA par défaut, configurable dans `parametres.devise`.
- **Accesseurs Eloquent** plutôt que logique dupliquée en vue : `photo_url`, `logo_url`, `whatsapp_url`, `montant_formatte`, `couleur_statut`, etc. Toujours privilégier l'accesseur existant plutôt que reformater une valeur dans une vue.
- **Textes et noms de rôles en français** dans toute l'application (`Administrateur`, `Livrée`, `Désactivé`...) — ne pas traduire en anglais dans le code métier.

---

## Prérequis

- PHP ≥ 8.2
- Composer
- Node.js ≥ 18 et npm
- MySQL ≥ 8.0 (ou MariaDB équivalent)
- Extension PHP `zip` activée (module Sauvegarde)
- WampServer / XAMPP / Laravel Herd ou tout environnement équivalent en local

---

## Installation en local

```bash
# 1. Cloner le projet
git clone <url-du-depot> resto-gestion
cd resto-gestion

# 2. Dépendances PHP
composer install

# 3. Dépendances front-end
npm install

# 4. Fichier d'environnement
cp .env.example .env
php artisan key:generate

# 5. Configurer la base de données dans .env
# DB_DATABASE=restodb
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Clé JWT (API mobile)
php artisan jwt:secret

# 7. Migrations + données de démonstration
php artisan migrate:fresh --seed

# 8. Lien symbolique pour les fichiers uploadés (logos, photos de plats)
php artisan storage:link

# 9. Compiler les assets du back-office
npm run dev      # développement (watch)
# ou
npm run build    # production (une seule fois)
```

> Le **site vitrine public** (`resources/views/site/index.blade.php`) n'a besoin d'aucune étape de build : il charge Tailwind CSS, Alpine.js et AOS via CDN directement dans la page.

---

## Comptes de démonstration

Créés par `UserSeeder` lors du `php artisan migrate:fresh --seed` :

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | admin@chezclarence.cm | Admin@2026! |
| Caissier | caissier@chezclarence.cm | Caissier@2026! |
| Serveur | serveur@chezclarence.cm | Serveur@2026! |
| Cuisinier | cuisinier@chezclarence.cm | Cuisinier@2026! |
| Livreur | livreur@chezclarence.cm | Livreur@2026! |
| Client (démo) | client@chezclarence.cm | Client@2026! |

> ⚠️ **Changez impérativement ces mots de passe avant toute mise en production.**

---

## Lancer le projet au quotidien

```bash
# Terminal 1 — compilation des assets en continu (back-office)
npm run dev

# Terminal 2 — serveur applicatif
php artisan serve
# ou, avec WampServer déjà démarré :
# http://localhost/resto-gestion/public
```

Points d'entrée utiles :

| Page | URL |
|---|---|
| Site vitrine public | `/` |
| Connexion back-office | `/connexion` |
| Tableau de bord (selon rôle) | `/dashboard` |
| Documentation API (Swagger) | `/api/documentation` |

---

## Site vitrine public

Le module public (`SiteController` + `resources/views/site/index.blade.php`) est une page unique (single page) autonome, pensée pour convertir un visiteur en client :

- **Présentation du restaurant** à partir des données réelles de `parametres` (nom, description, coordonnées, horaires).
- **Menu interactif** filtrable par catégorie, note (étoiles calculées à partir du volume de commandes de chaque plat) et budget.
- **Services annexes** (privatisation, traiteur, carte cadeau, livraison, événements, formule entreprise) avec fenêtres modales de demande de devis.
- **Carte de localisation** intégrée + bouton d'itinéraire GPS.
- **Formulaire de contact** et **chatbot flottant**, tous deux redirigeant vers WhatsApp avec un message pré-rempli selon l'intention (commander, réserver, traiteur, question).
- **Aucune création de compte requise** : toutes les actions passent par WhatsApp (`parametres.whatsapp`).

### Thématisation (clair / sombre)

Toutes les couleurs de cette page sont centralisées dans un bloc `:root` en CSS, découplées des classes Tailwind grâce à des couleurs personnalisées pointant vers des variables CSS (`brand`, `success`, `rating`, `ink`, `surface`, `fg`, `line`). Pour basculer l'intégralité de la page en thème sombre, une seule ligne à modifier en tout début de fichier :

```php
@php
    $theme = 'light'; // 'light' ou 'dark'
@endphp
```

Toute évolution de l'identité visuelle (couleur de marque, teintes neutres) se fait exclusivement dans ce bloc `:root`, sans toucher au reste du template.

---

## API REST & application mobile

- Authentification par jeton **JWT** (`tymon/jwt-auth`), header `Authorization: Bearer <token>`.
- Routes exposées dans `routes/api.php`, contrôleurs dans `app/Http/Controllers/Api/`.
- Documentation interactive générée avec **Swagger/OpenAPI** :

```bash
php artisan l5-swagger:generate
```

Consultable ensuite sur `/api/documentation`.

---

## Déploiement en production

Checklist avant mise en ligne :

```bash
# Environnement
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Optimisations Laravel
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Assets front-end
npm run build

# Base de données
php artisan migrate --force

# Fichiers uploadés
php artisan storage:link
```

Recommandations complémentaires :

- Servir l'application derrière **HTTPS** (certificat SSL obligatoire, notamment pour les liens WhatsApp et la géolocalisation du site public).
- Configurer une **tâche planifiée** (`php artisan schedule:run` via cron) si des sauvegardes automatiques ou des rapports périodiques sont mis en place.
- Définir un **mot de passe fort et unique** pour chaque compte de démonstration avant l'ouverture au public.
- Vérifier que l'extension PHP `zip` est active côté hébergeur (module Sauvegarde).
- Mettre en place une **sauvegarde régulière de la base de données**, indépendante du module interne (ex. sauvegarde automatique côté hébergeur ou `mysqldump` planifié).

---

## Sauvegarde, restauration & maintenance

Le module **Sauvegarde & Restauration** (Administrateur uniquement) propose :

- **Export** de tout ou partie des données en SQL, CSV ou PDF.
- **Corbeille** : toutes les suppressions passent d'abord par le champ `void` (soft delete métier) avant d'être définitivement effaçables — aucune suppression irréversible n'est possible sur une ligne encore active.
- **Restauration** d'un élément depuis la corbeille.
- **Import** de données (fichier ZIP) avec aperçu avant application.
- **Purge définitive**, volontairement cantonnée aux lignes déjà présentes dans la corbeille.

> Ce module ne remplace pas une sauvegarde serveur régulière ; il complète une politique de sauvegarde infrastructure classique (ex. `mysqldump` planifié, snapshots hébergeur).

---

## Sécurité

- Mots de passe **hachés** (jamais stockés en clair), via le cast `hashed` du modèle `User`.
- **CSRF** actif sur tous les formulaires du back-office.
- **Throttle** sur la route de connexion (limite les tentatives de force brute).
- Séparation stricte des accès par **rôle** via middleware, aussi bien côté web (session) que côté API (JWT).
- Suppression définitive de données **impossible** hors du circuit corbeille du module Sauvegarde.
- Penser à retirer/désactiver les comptes de démonstration avant mise en production.

---

## Commandes Artisan utiles

| Commande | Description |
|---|---|
| `php artisan make:controller NomController` | Créer un contrôleur |
| `php artisan make:model Nom -m` | Créer un modèle + sa migration |
| `php artisan make:request NomRequest` | Créer une classe de validation |
| `php artisan migrate` | Appliquer les migrations |
| `php artisan migrate:fresh --seed` | Réinitialiser la base + données de démo |
| `php artisan db:seed` | Rejouer les seeders |
| `php artisan route:list` | Lister toutes les routes |
| `php artisan storage:link` | Créer le lien symbolique pour les fichiers uploadés |
| `php artisan l5-swagger:generate` | Régénérer la documentation API |
| `php artisan jwt:secret` | (Re)générer la clé de signature JWT |
| `php artisan tinker` | Console PHP interactive |
| `php artisan config:clear` / `cache:clear` / `route:clear` / `view:clear` | Vider les caches en développement |

---

## Dépannage courant

| Symptôme | Cause probable | Solution |
|---|---|---|
| `Undefined variable $parametres` dans une vue | Le contrôleur n'a pas transmis la variable à la vue | Vérifier le `return view(...)` du contrôleur concerné |
| Images de plats/logo introuvables | Lien symbolique de stockage manquant | `php artisan storage:link` |
| Erreur SQL `Column not found: 'name'` sur `users` lors du seed | Seeder par défaut de Laravel non adapté au schéma personnalisé (`iduser`, `nom`, `prenom`...) | Utiliser le `UserSeeder` du projet, pas celui généré par défaut |
| Modifications Blade/Tailwind non visibles | `npm run dev` non lancé, ou cache de vue Laravel | Lancer `npm run dev` ; `php artisan view:clear` |
| Erreur `config/permission.php not loaded` sur la migration des permissions | Cache de config à vider | `php artisan config:clear` puis relancer la migration |
| Liens WhatsApp inactifs sur le site public | Champ `parametres.whatsapp` vide | Renseigner un numéro au format international sans espaces (ex. `237699000000`) |

---

## Licence & contact

Projet développé par **HI-TECH Vision SARL** pour **Chez Clarence** (Douala, Cameroun).

Ce dépôt et son contenu sont la propriété de leurs auteurs respectifs — usage et redistribution soumis à autorisation préalable, sauf mention contraire ajoutée par l'équipe projet.

Pour toute question technique sur ce dépôt, contactez l'équipe de développement HI-TECH Vision SARL.