# Module Evenement - Plateforme Education

Ce projet implemente un module MVC complet pour connecter les eleves et les professeurs via des evenements.

## Ce qui est inclus

- FrontOffice: liste des evenements, fiche detail, inscription.
- BackOffice: CRUD categories, CRUD evenements, gestion participations.
- Validation sans HTML5: controles JavaScript personnalises + validation serveur PHP.
- Structure MVC stricte (Model / View / Controller).

## Structure

- `app/models`: `Categorie.php`, `Evenement.php`, `Participation.php`
- `app/controllers`: `HomeController.php`, `AdminCategorieController.php`, `AdminEvenementController.php`, `AdminParticipationController.php`
- `app/views/front`: `index.php`, `detail.php`, `register.php`
- `app/views/back/categories`: `index.php`, `create.php`, `edit.php`
- `app/views/back/evenements`: `index.php`, `create.php`, `edit.php`
- `app/views/back/participations`: `index.php`, `edit.php`
- `public`: point d'entree web + assets
- `database.sql`: schema SQL complet

## Installation pas a pas

1. Creer la base et les tables:
   - importer `database.sql` dans phpMyAdmin.
2. Verifier la connexion DB dans `config/database.php`:
   - base: `event_db`
   - user: `root`
   - password: vide (par defaut XAMPP)
3. Lancer Apache + MySQL dans XAMPP.
4. Ouvrir:
   - FrontOffice: `http://localhost/event_module/public/index.php?url=Home/index`
   - BackOffice Categories: `http://localhost/event_module/public/index.php?url=AdminCategorie/index`
   - BackOffice Evenements: `http://localhost/event_module/public/index.php?url=AdminEvenement/index`
   - BackOffice Participations: `http://localhost/event_module/public/index.php?url=AdminParticipation/index`

## Workflow conseille pour la demo

1. Ajouter 2-3 categories.
2. Ajouter 2 evenements lies aux categories.
3. Verifier la liste FO et la page detail.
4. Faire une inscription depuis FO.
5. Verifier la participation en BO et modifier son statut/note.

## Regles de validation appliquees

- Categorie: nom >= 3 caracteres, couleur hex valide.
- Evenement: titre >= 5, description >= 10, date_fin >= date_debut, capacite > 0.
- Contraintes de type:
  - `en ligne` -> `lien_acces` obligatoire.
  - `presentiel` -> `lieu` obligatoire.
- Participation: email valide, telephone valide, note entre 0 et 5.

## Technologies

- PHP (MVC)
- MySQL + PDO
- Bootstrap 5
- JavaScript (validation custom)
