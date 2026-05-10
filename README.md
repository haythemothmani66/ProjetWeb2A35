# Module Événement — MVC

Application PHP (front + back office) pour gérer catégories, événements et participations.

## Structure MVC

| Élément | Chemin |
|--------|--------|
| Point d’entrée web | `public/index.php` |
| Contrôleurs | `app/controllers/` |
| Modèles | `app/models/` |
| Vues | `app/views/` |
| Noyau | `app/App.php`, `app/controllers/Controller.php` |
| Helpers | `app/helpers/` |
| Config BDD | `config/database.php` |
| Assets | `public/css/`, `public/js/`, `public/images/` |

## Installation

1. Importer `database.sql` ou laisser `Database::initializeSchema()` créer les tables.
2. Ajuster `config/database.php` si besoin (hôte, utilisateur, mot de passe).
3. Apache : `mod_rewrite` activé ; adapter **`RewriteBase`** dans `.htaccess` (racine) et `public/.htaccess` si le dossier n’est pas `event_module`.

## URLs (exemple)

- Accueil : `http://localhost/event_module/Home/index`
- Admin catégories : `http://localhost/event_module/AdminCategorie/index`
- Admin événements : `http://localhost/event_module/AdminEvenement/index`
- Admin participations : `http://localhost/event_module/AdminParticipation/index`
