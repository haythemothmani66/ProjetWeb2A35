# CHECKLIST INTEGRATION & MERGE — EduMatch 2A35
## Fichier de reference obligatoire avant/pendant/apres chaque merge

> **REGLE D'OR** : Ne JAMAIS dire "c'est termine" sans avoir verifie TOUS les points ci-dessous.
> Ce fichier existe car l'integration de `gestion_de_partenariat` a pris des heures a cause de bugs detectes un par un au lieu d'un seul audit systematique.

---

## ERREURS COMMISES LORS DU MERGE PARTENARIAT (a ne JAMAIS repeter)

| # | Erreur | Consequence | Temps perdu |
|---|--------|-------------|-------------|
| 1 | `PARTNER_SYNC_API_URL` pointait vers `contract_sync.php` au lieu de `partner_sync.php` | Formulaire partenariat ne sauvegardait rien en DB | 1h+ de debug |
| 2 | `MailHelper.php` : `setFrom('noreply@edumatch.com')` alors que le compte SMTP est `chahdtissaoui29@gmail.com` | Mails arrives VIDES chez le destinataire | 1h+ |
| 3 | Lien `contract.html` (localhost) dans le mail d'approbation | "Adresse introuvable" pour le destinataire | 30min |
| 4 | `partners_may_like.html` jamais converti en `.php` | Pas de session, pas de navbar unifiee, pas de protection | 1h |
| 5 | `partner_details.php` → lien retour vers `.html` au lieu de `.php` | Navigation cassee, envoie vers ancien fichier | 30min |
| 6 | `view/frontoffice/index.php` → redirect vers `index.html` | Home renvoie vers ancien template sans session | 30min |
| 7 | 3 APIs (`check_partner_status`, `contract_sync`, `partner_sync`) sur `databaseedumatch` au lieu de `edumatch` | APIs ne trouvent pas les tables | 1h |
| 8 | 3 APIs en `mysqli` au lieu de `PDO + Config::getConnexion()` | Incompatible avec notre architecture | 1h |
| 9 | Styles CSS du dropdown navbar supprimes par erreur de `partners_may_like.php` | Navbar completement cassee visuellement | 30min |
| 10 | Chatbot system prompt trop permissif | Bot donnait des recettes de pizza au lieu de refuser | 20min |
| 11 | Navbar dupliquee — chaque page a ses propres styles au lieu d'un CSS global | Confusion sur quels styles garder/supprimer | 30min |
| 12 | Fonctionnalites eliminees par erreur (recommandations) en pensant "simplifier" | Perte de fonctionnalites du module | GRAVE |
| 13 | Singleton PHPMailer gardait l'etat corrompu entre les envois | Mails suivants potentiellement casses | 20min |

**TOTAL : ~8h de bugs evitables en une seule passe d'audit de 15 minutes.**

---

## CHECKLIST PRE-MERGE (AVANT de merger une branche)

### A. Analyse du SQL du module
- [ ] Recuperer l'export SQL du membre
- [ ] Verifier le nom de la base de donnees → doit etre `edumatch` (PAS `databaseedumatch`, `projetweb`, etc.)
- [ ] Lister toutes les tables du module
- [ ] Verifier les FK vers `user(id)` — chaque table qui reference un utilisateur DOIT avoir `FOREIGN KEY → user(id)`
- [ ] Verifier qu'il n'y a PAS de table `users` / `utilisateurs` dupliquee (seule notre table `user` existe)
- [ ] Verifier les types de donnees compatibles (id INT, timestamps, etc.)
- [ ] Verifier les collations → `utf8mb4_general_ci` partout

### B. Analyse du code source du module
- [ ] Identifier TOUTES les pages du module (frontoffice + backoffice)
- [ ] Identifier TOUS les controllers et models
- [ ] Identifier TOUTES les APIs/endpoints
- [ ] Identifier les fichiers de config (connexion DB, .env, credentials)
- [ ] Lister les dependances externes (librairies JS, PHP, APIs tierces)
- [ ] Verifier le `.gitignore` du membre — noter les fichiers potentiellement manquants

### C. Detection des incompatibilites
- [ ] Connexion DB : `mysqli` ? `new PDO(...)` ? autre chose que `Config::getConnexion()` ?
- [ ] Sessions : `$_SESSION['id']` au lieu de `$_SESSION['user_id']` ? `$_SESSION['role']` au lieu de `$_SESSION['user_role']` ?
- [ ] Template backoffice different du notre (Dasher BS5) ?
- [ ] Template frontoffice different du notre (Eduleb) ?
- [ ] Login/signup duplique ? (seul notre `AuthController` gere l'auth)
- [ ] Fichiers `.html` qui devraient etre `.php` ?

---

## CHECKLIST PENDANT LE MERGE (pour CHAQUE fichier integre)

### 1. Connexion Base de Donnees
```
OBLIGATOIRE : require_once __DIR__ . '/../../config/database.php';
OBLIGATOIRE : $pdo = Config::getConnexion();
INTERDIT    : new PDO('mysql:host=...', ...)
INTERDIT    : mysqli_connect(...)
INTERDIT    : new mysqli(...)
INTERDIT    : Autre nom de base que 'edumatch'
```
- [ ] Chaque fichier PHP qui touche la DB utilise `Config::getConnexion()`
- [ ] Aucune connexion DB en dur dans les fichiers
- [ ] Aucune reference a une autre base de donnees

### 2. Sessions (cles standardisees)
```
OBLIGATOIRE : $_SESSION['user_id']
OBLIGATOIRE : $_SESSION['user_role']    → 'admin' | 'encadrant' | 'etudiant' | 'partenariat'
OBLIGATOIRE : $_SESSION['user_nom']
OBLIGATOIRE : $_SESSION['user_prenom']
OBLIGATOIRE : $_SESSION['user_photo']
INTERDIT    : $_SESSION['id'], $_SESSION['ID'], $_SESSION['userId']
INTERDIT    : $_SESSION['role'], $_SESSION['type'], $_SESSION['user_type']
INTERDIT    : $_SESSION['name'], $_SESSION['username']
```
- [ ] Grep `\$_SESSION\[` dans chaque fichier du module
- [ ] Remplacer toute cle non standard par la cle standard

### 3. Navbar Frontoffice (UNE SEULE pour tout le projet)
```
OBLIGATOIRE : include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/template/_navbar.php';
```
- [ ] CHAQUE page frontoffice (.php) inclut `_navbar.php`
- [ ] AUCUNE page n'a sa propre navbar en dur dans le HTML
- [ ] Les styles CSS du dropdown DOIVENT etre presents dans chaque page :
```css
.header-group { display: flex; flex-direction: row; align-items: center; gap: 10px; }
.btn-backoffice { ... }
.user-dropdown { position: relative; display: flex; align-items: center; gap: 8px; cursor: pointer; }
.user-dropdown .user-avatar { width: 36px; height: 36px; border-radius: 50%; ... }
.user-dropdown .user-name { font-weight: 600; font-size: 14px; ... }
.user-dropdown .dropdown-caret { ... }
.user-dropdown-menu { display: none; position: absolute; top: 100%; right: 0; ... }
.user-dropdown-menu.show { display: block; }
.user-dropdown-menu a { ... }
.user-dropdown-menu a i { ... }
.user-dropdown-menu hr { ... }
```
- [ ] NE JAMAIS supprimer ces styles en pensant qu'ils sont "dupliques" — ils sont NECESSAIRES car pas dans un CSS global

### 4. Sidebar/Layout Backoffice (Dasher Bootstrap 5)
```
OBLIGATOIRE : include __DIR__ . '/../../src/partials_php/sidebar.php';
OBLIGATOIRE : include __DIR__ . '/../../src/partials_php/topbar.php';
```
- [ ] CHAQUE page backoffice utilise le layout Dasher (sidebar.php + topbar.php)
- [ ] Si le module a un template backoffice different → MIGRER le contenu vers notre layout Dasher
- [ ] Garder le contenu/logique PHP du module, remplacer UNIQUEMENT le wrapper HTML/template
- [ ] Ajouter les liens du module dans `sidebar.php`
- [ ] Protection admin : `if ($_SESSION['user_role'] !== 'admin') { redirect... }`

### 5. Protection par Role
```
Pages ADMIN only     : toutes les pages backoffice
Pages ETUDIANT       : soumettre devoirs, passer quiz, voir offres emploi
Pages ENCADRANT      : corriger devoirs, creer quiz, gerer evenements
Pages PARTENARIAT    : formulaire partenariat, espace partenaire
Pages TOUS connectes : recommandations, details partenaire, feed
Pages PUBLIQUES      : index.php, sign-in, sign-up, forget-password
```
- [ ] Chaque page a un controle de role en haut du fichier
- [ ] Les pages backoffice verifient `$_SESSION['user_role'] === 'admin'`
- [ ] Les pages frontoffice verifient au minimum `!empty($_SESSION['user_id'])`
- [ ] Redirection vers `sign-in.php` si pas connecte
- [ ] Redirection vers `index.php` si mauvais role

### 6. Liens et URLs
```
OBLIGATOIRE : $baseUrl = '/gestion_users';
OBLIGATOIRE : Liens vers index.php (PAS index.html)
OBLIGATOIRE : Liens vers fichiers .php (PAS .html)
INTERDIT    : Liens vers index.html
INTERDIT    : Liens absolus localhost dans les mails
INTERDIT    : Chemins absolus C:\... dans le code
```
- [ ] Grep `index\.html` → remplacer par `index.php` (ou le bon chemin)
- [ ] Grep `\.html` dans les liens href/action → verifier si devrait etre `.php`
- [ ] Grep `localhost` dans les mails → acceptable en dev mais signaler
- [ ] Utiliser `$baseUrl` ou `__DIR__` pour tous les chemins

### 7. APIs et Endpoints
- [ ] Chaque API utilise `Config::getConnexion()` (pas mysqli, pas autre PDO)
- [ ] Chaque API retourne du JSON propre avec `Content-Type: application/json`
- [ ] Verifier que les URLs d'API dans le JS frontend pointent vers le bon fichier
- [ ] Tester chaque endpoint avec `php -l` (syntaxe)
- [ ] Verifier les credentials (API keys, SMTP) → doivent etre dans `.env` ou constantes correctes

### 8. PHPMailer / Emails
```
OBLIGATOIRE : setFrom() = meme adresse que Username SMTP
OBLIGATOIRE : Pas de singleton PHPMailer (nouvelle instance a chaque envoi)
OBLIGATOIRE : AltBody renseigne (version texte du mail)
INTERDIT    : setFrom('noreply@domaine.com') si le SMTP est gmail
INTERDIT    : Lien contract.html dans les mails
```
- [ ] `setFrom` correspond au compte SMTP authentifie
- [ ] Pas de singleton/cache sur l'objet PHPMailer
- [ ] Liens dans les mails pointent vers des pages `.php` valides
- [ ] Contenu du mail en francais
- [ ] Tester l'envoi avec une vraie adresse email

### 9. Chatbot / IA
- [ ] System prompt interdit STRICTEMENT les questions hors-sujet EduMatch
- [ ] Modele Groq correct : `llama-3.3-70b-versatile`
- [ ] API key Groq valide dans `.env`
- [ ] Le chatbot est inclus via `chatbot.html` dans les pages qui le necessitent

### 10. Fichiers Orphelins
- [ ] Lister les fichiers `.html` qui ont un equivalent `.php` → supprimer le `.html`
- [ ] Lister les dossiers vides → supprimer
- [ ] Lister les fichiers de debug/test → supprimer
- [ ] Verifier que `.gitignore` inclut : `.env`, `vendor/`, fichiers temp

---

## CHECKLIST POST-MERGE (APRES le merge, AVANT de dire "c'est fait")

### Test fonctionnel obligatoire
- [ ] `php -l` sur CHAQUE fichier PHP modifie/ajoute (zero erreur de syntaxe)
- [ ] Ouvrir chaque page frontoffice dans le navigateur → navbar correcte ?
- [ ] Ouvrir chaque page backoffice → sidebar + topbar corrects ?
- [ ] Tester la fonctionnalite PRINCIPALE du module (CRUD complet)
- [ ] Tester l'envoi de mail si le module en envoie
- [ ] Tester les APIs avec le navigateur/console
- [ ] Tester le chatbot si present sur la page
- [ ] Verifier les donnees en DB apres les operations CRUD

### Verification git
- [ ] `git status` → pas de fichiers oublies
- [ ] `git diff` → pas de changements accidentels dans d'autres modules
- [ ] Le commit de merge est propre et decrit ce qui a ete integre
- [ ] Les fichiers `.env` et credentials ne sont PAS dans le commit

---

## REGLES ABSOLUES (JAMAIS d'exception)

1. **NE JAMAIS eliminer une fonctionnalite** d'un module en pensant "simplifier" — chaque fonctionnalite est le travail d'un membre de l'equipe
2. **NE JAMAIS supposer** qu'un style CSS est "duplique" sans verifier qu'il existe dans un fichier CSS global — si ce n'est pas dans `style.css`, il est NECESSAIRE dans la page
3. **NE JAMAIS dire "c'est fait"** sans avoir teste les fonctionnalites principales dans le navigateur
4. **TOUJOURS grep** avant de modifier : `\.html`, `$_SESSION`, `mysqli`, `databaseedumatch`, `index\.html`
5. **TOUJOURS verifier les URLs** dans le JS frontend — elles doivent pointer vers les bons endpoints PHP
6. **TOUJOURS verifier les credentials** (SMTP, API keys) — `setFrom` = compte SMTP
7. **UN SEUL `_navbar.php`** pour tout le frontoffice — jamais de navbar en dur
8. **UN SEUL layout Dasher** pour tout le backoffice — adapter les templates differents
9. **UNE SEULE base de donnees** : `edumatch` — jamais d'autre nom
10. **UNE SEULE methode de connexion** : `Config::getConnexion()` — jamais de PDO/mysqli direct

---

## STRUCTURE DU PROJET (reference)

```
gestion_users/
├── config/
│   └── database.php          ← Config::getConnexion() + getDBConnection()
├── config.php                ← Wrapper compatibilite (getConnexion, helpers)
├── .env                      ← GROQ_API_KEY, credentials (dans .gitignore)
├── index.php                 ← Router principal
├── controller/
│   ├── AuthController.php    ← Login, signup, logout, OTP
│   ├── UserController.php    ← CRUD users (admin)
│   ├── ProfilController.php  ← Profil etudiant
│   ├── PartenaireController.php
│   ├── ContractController.php
│   └── devoirs.php           ← (a migrer vers DevoirsController.php)
├── model/
│   ├── User.php
│   ├── Profil.php
│   ├── Partenaire.php
│   └── Contract.php
├── view/
│   ├── template/             ← Pages avec _navbar.php (front commun)
│   │   ├── _navbar.php       ← NAVBAR UNIQUE (auto-refresh session DB)
│   │   ├── index.php         ← Homepage
│   │   ├── sign-in.php
│   │   ├── sign-up.php
│   │   ├── profil.php
│   │   └── ...
│   ├── frontoffice/          ← Pages modules front
│   │   ├── partenariat.php
│   │   ├── partners_may_like.php
│   │   ├── partner_details.php
│   │   ├── submit.php        ← Devoirs
│   │   ├── feed.php          ← Feed devoirs
│   │   └── ...
│   └── backoffice/           ← Admin (Dasher BS5)
│       ├── layout/
│       │   ├── header.php    ← Include sidebar + protection admin
│       │   └── footer.php
│       ├── src/partials_php/
│       │   ├── sidebar.php   ← Sidebar unique backoffice
│       │   └── topbar.php
│       └── src/pages/backoffice/
│           ├── dashboard.php
│           ├── users.php
│           └── ...
├── api/
│   ├── groq_chatbot.php      ← Chatbot Groq
│   ├── partner_sync.php      ← Sync partenaires
│   ├── contract_sync.php     ← Sync contrats
│   ├── check_partner_status.php
│   ├── get_recommendations.php
│   ├── RecommendationService.php
│   └── MailHelper.php        ← Envoi mails (PHPMailer)
├── lib/PHPMailer/             ← Librairie PHPMailer
├── assets/                    ← CSS, JS, images, fonts
└── uploads/                   ← Photos profil, logos
```

---

## MODULES A INTEGRER (statut)

| Branche | Module | Statut | Problemes connus |
|---------|--------|--------|-----------------|
| `gestion_user_1` | Users, Auth, Profil | MERGE OK | Base du projet |
| `gestion_de_partenariat` | Partenaires, Contrats | MERGE OK | 13 bugs corriges (voir tableau ci-dessus) |
| `gestion_de_devoirs` | Devoirs, Corrections | EN ATTENTE | Fichiers partiels deja presents |
| `OffreEmploi` | Offres emploi, Candidatures | EN ATTENTE | Tables `offre_emploi`, `candidature` existent deja |
| `gestion_de_quiz` | Quiz educatifs | EN ATTENTE | A analyser |
| `MetiersSimplesEvenements` | Metiers, Evenements | EN ATTENTE | A analyser |

---

## COMMANDES UTILES POUR L'AUDIT

```bash
# Trouver toutes les connexions DB non standard
grep -rn "mysqli\|new PDO\|databaseedumatch" --include="*.php"

# Trouver toutes les sessions non standard
grep -rn "\$_SESSION\[" --include="*.php" | grep -v "user_id\|user_role\|user_nom\|user_prenom\|user_photo\|success_message\|form_errors\|form_data\|flash_messages\|otp\|reset_"

# Trouver tous les liens .html dans les fichiers PHP
grep -rn "\.html" --include="*.php" | grep -v "\.gitignore\|chatbot\.html\|sidebar-collapse\.html\|topbar-second\.html"

# Verifier syntaxe PHP de tous les fichiers
find . -name "*.php" -exec php -l {} \;

# Trouver les fichiers sans navbar
grep -rL "_navbar.php" view/frontoffice/*.php view/template/*.php
```

---

*Derniere mise a jour : 8 mai 2026*
*Auteur : Integration IA (Claude Opus 4.6) — apres les erreurs du merge partenariat*
