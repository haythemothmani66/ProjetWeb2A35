
EduMatch MVC Refactor (Partenaire + Contract)

This project has been reorganized into MVC for the Partenaire and Contract entities while preserving existing FrontOffice and BackOffice templates.

Core structure

- config.php
- Model/
	- Partenaire.php
	- Contract.php
- Controller/
	- PartenaireController.php
	- ContractController.php
- View/
	- FrontOffice/
	- BackOffice/
		- partenaire/
			- addPartenaire.php
			- updatePartenaire.php
			- listPartenaire.php
			- verificationPartenaire.php
		- contract/
			- addContract.php
			- updateContract.php
			- listContract.php
			- verificationContract.php

Entry point

- index.php dispatches MVC routes with query parameters:
	- ?controller=partenaire&action=list
	- ?controller=partenaire&action=add
	- ?controller=contract&action=list
	- ?controller=contract&action=add

Compatibility

- Existing frontoffice templates are preserved.
- Existing backoffice legacy HTML pages for partner/contract now redirect to MVC routes.
- Legacy config/database.php now delegates to secure PDO connection via getConnexion().

Database

- Tables used:
	- partenaires
	- contrats
- Models auto-create required table structure if missing.

Validation and security

- Server-side validation added in both controllers.
- Prepared statements used in all CRUD operations.
- File upload checks for type and max size:
	- Partner logo: images up to 2MB
	- Contract PDF: PDF up to 5MB

Removed obsolete files

- Empty legacy model/controller placeholders for partenariat.
- Temporary debug/test files.

