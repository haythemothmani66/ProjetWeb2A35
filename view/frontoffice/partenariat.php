<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/config/database.php';

// --- Protection : roles 'partenariat' et 'admin' autorises ---
$allowedRoles = ['partenariat', 'admin'];
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', $allowedRoles, true)) {
    // Si pas connecte du tout -> page de connexion
    if (empty($_SESSION['user_id'])) {
        header('Location: /gestion_users/view/template/sign-in.php');
    } else {
        // Connecte mais pas le bon role -> accueil
        header('Location: /gestion_users/view/template/index.php');
    }
    exit;
}

$baseUrl = '/gestion_users';
?>
<!DOCTYPE html>
<html lang="fr">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>EduMatch - Partenariat</title>
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/bootstrap/css/bootstrap.min.css">
		<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/fonts/font-awesome.min.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/fonts/themify-icons.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/owlcarousel/css/owl.carousel.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/owlcarousel/css/owl.theme.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/jquery-simple-mobilemenu.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/magnific-popup.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/animate.css">
		<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
		<style>
		  .header-group { display: flex; flex-direction: row; align-items: center; gap: 10px; }
		  .btn-backoffice { background: linear-gradient(135deg, #6366f1, #8B5CF6); color: white; padding: 10px 20px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; transition: all 0.3s ease; display: inline-block; text-align: center; border: none; cursor: pointer; }
		  .btn-backoffice:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-decoration: none; }
		  .user-dropdown { position: relative; display: flex; align-items: center; gap: 8px; cursor: pointer; }
		  .user-dropdown .user-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #525fe1; }
		  .user-dropdown .user-name { font-weight: 600; font-size: 14px; color: #0b104a; white-space: nowrap; }
		  .user-dropdown .dropdown-caret { font-size: 10px; color: #6c757d; transition: transform 0.2s; }
		  .user-dropdown:hover .dropdown-caret { transform: rotate(180deg); }
		  .user-dropdown-menu { display: none; position: absolute; top: 100%; right: 0; background: white; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.12); min-width: 200px; padding: 8px 0; z-index: 1000; margin-top: 8px; }
		  .user-dropdown-menu.show { display: block; }
		  .user-dropdown-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 18px; color: #333; text-decoration: none; font-size: 14px; font-weight: 500; transition: background 0.2s; }
		  .user-dropdown-menu a:hover { background: #f5f7fa; color: #525fe1; }
		  .user-dropdown-menu a i { width: 18px; text-align: center; }
		  .user-dropdown-menu hr { margin: 6px 0; border-color: #eee; }
		</style>
	</head>
	
    <body data-spy="scroll" data-offset="80">

		<!-- START PRELOADER -->
		<div class="preloaders">
			<span class="loader"></span>
		</div>
		<!-- END PRELOADER -->		

		<?php include $_SERVER['DOCUMENT_ROOT'] . '/gestion_users/view/template/_navbar.php'; ?>		

		<!-- START SECTION TOP -->
		<section class="section-top">
			<div class="container">
				<div class="col-lg-10 offset-lg-1 text-center">
					<div class="section-top-title wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.3s" data-wow-offset="0">
						<h1>Partenariat</h1>
						<ul>
							<li><a href="<?= $baseUrl ?>/view/template/index.php">Accueil</a></li>
							<li> / Partenariat</li>
						</ul>
					</div><!-- //.HERO-TEXT -->
				</div><!--- END COL -->
			</div><!--- END CONTAINER -->
		</section>	
		<!-- END SECTION TOP -->
		
		<!-- START FAQ -->
		<section class="faq_area section-padding">
			<div class="container">															
				<div class="row justify-content-center">		
					<div class="col-lg-7 col-sm-12 col-xs-12">
						<div class="partnership_content" style="line-height: 1.8;">
							<h2 style="font-size: 28px; color: #1a1a1a; margin-bottom: 20px; font-weight: 700;">Devenez partenaire EduMatch</h2>

							<p style="font-size: 16px; color: #555; margin-bottom: 18px;">
								Chez <strong>EduMatch</strong>, nous croyons au pouvoir de la collaboration pour creer des opportunites d'apprentissage significatives.
								Nous travaillons avec des organisations, institutions et entreprises innovantes pour connecter les etudiants a des ressources, experiences et connaissances precieuses.
							</p>

							<p style="font-size: 16px; color: #555; margin-bottom: 18px;">
								En devenant partenaire, vous rejoignez un reseau croissant dedie a faconner l'avenir de l'education.
								Que vous soyez une universite, une startup ou une entreprise etablie, EduMatch vous offre l'opportunite de mettre en valeur votre expertise, d'atteindre un public plus large et d'avoir un impact reel.
							</p>

							<p style="font-size: 16px; color: #555; margin-bottom: 18px;">
								Remplissez le formulaire ci-dessous pour soumettre votre demande de partenariat. Notre equipe examinera attentivement votre candidature et vous repondra dans les plus brefs delais.
							</p>

							<p style="font-size: 16px; color: #1a1a1a; margin-bottom: 18px;"><strong>Ensemble, construisons de meilleures experiences d'apprentissage.</strong></p>
						</div>
					</div><!-- END COL  -->	
					<div class="col-lg-5 col-sm-12 col-xs-12">
						<div class="faq_img">
							<img src="../../assets/img/faq.jpg" alt="image partenariat" />
						</div>
					</div>					
				</div><!--END  ROW  -->
			</div><!--- END CONTAINER -->
		</section>
		<!-- END FAQ -->


		<!-- START PARTNER APPLICATION FORM -->
		<section class="partner_form_area section-padding" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); position: relative;">
			<div class="container">
				<div class="row">
					<div class="col-lg-8 offset-lg-2 col-sm-12 col-xs-12">
						<!-- Form Title -->
						<div class="form_header text-center" style="margin-bottom: 50px;">
							<h2 style="font-size: 36px; color: #1a1a1a; margin-bottom: 15px; font-weight: 700;">Devenez notre partenaire</h2>
							<p style="font-size: 16px; color: #666; line-height: 1.6;">Rejoignez notre reseau croissant de partenaires et sponsors. Aidez-nous a creer de meilleures opportunites educatives a travers le monde.</p>
						</div>

						<!-- Partner Form Card -->
						<form id="partnerForm" class="partner_form" novalidate style="background: white; border-radius: 15px; padding: 50px; box-shadow: 0 15px 45px rgba(0,0,0,0.1); animation: formFadeIn 0.8s ease-out;">
							<input type="hidden" name="status" value="pending">
							<div id="partnerFormAlert" class="form_alert" role="alert" aria-live="polite"></div>

							<!-- Row 1: Organization Name & Partner Type -->
							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
								<!-- Organization Name -->
								<div class="form_group">
									<label for="org_name" style="display: block; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; font-size: 14px;">Nom de l'organisation *</label>
									<input 
										type="text" 
										id="org_name"
										name="organization_name" 
										class="form_input"
										placeholder="Entrez le nom de votre organisation"
										required
										style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.3s ease; font-family: inherit;"
										onInput="validateField(this)"
									>
									<small style="display: block; margin-top: 6px; color: #999; font-size: 13px;">Le nom officiel de votre organisation</small>
								</div>

								<!-- Partner Type Dropdown -->
								<div class="form_group">
									<label for="partner_type" style="display: block; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; font-size: 14px;">Type de partenaire *</label>
									<select 
										id="partner_type"
										name="partner_type" 
										class="form_input"
										required
										style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.3s ease; font-family: inherit; background-color: white; cursor: pointer;"
										onchange="validateField(this)"
									>
										<option value="">-- Selectionnez un type --</option>
										<option value="company">Entreprise</option>
										<option value="university">Universite</option>
										<option value="startup">Startup</option>
										<option value="ngo">ONG</option>
									</select>
									<small style="display: block; margin-top: 6px; color: #999; font-size: 13px;">Choisissez le type de votre organisation</small>
								</div>
							</div>

							<!-- Row 2: Email & Telephone -->
							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
								<!-- Email -->
								<div class="form_group">
									<label for="email" style="display: block; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; font-size: 14px;">Adresse email *</label>
									<input 
										type="email" 
										id="email"
										name="email" 
										class="form_input"
										placeholder="contact@yourcompany.com"
										required
										style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.3s ease; font-family: inherit;"
										onInput="validateField(this)"
									>
									<small style="display: block; margin-top: 6px; color: #999; font-size: 13px;">Nous utiliserons ceci pour vous contacter</small>
								</div>

								<!-- Telephone -->
								<div class="form_group">
									<label for="phone" style="display: block; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; font-size: 14px;">Telephone *</label>
									<input 
										type="tel" 
										id="phone"
										name="telephone" 
										class="form_input"
										placeholder="1234567890"
										pattern="[0-9]{7,15}"
										required
										style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.3s ease; font-family: inherit;"
										onInput="validateField(this)"
									>
									<small style="display: block; margin-top: 6px; color: #999; font-size: 13px;">Chiffres uniquement (7-15 chiffres)</small>
								</div>
							</div>

							<!-- Row 3: Address & Country -->
							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
								<!-- Address -->
								<div class="form_group">
									<label for="address" style="display: block; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; font-size: 14px;">Adresse</label>
									<input 
										type="text" 
										id="address"
										name="address" 
										class="form_input"
										placeholder="Adresse postale"
										style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.3s ease; font-family: inherit;"
										onInput="validateField(this)"
									>
									<small style="display: block; margin-top: 6px; color: #999; font-size: 13px;">Adresse de votre bureau</small>
								</div>

								<!-- Country -->
								<div class="form_group">
									<label for="country" style="display: block; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; font-size: 14px;">Pays</label>
									<input 
										type="text" 
										id="country"
										name="country" 
										class="form_input"
										placeholder="Ex : France"
										style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.3s ease; font-family: inherit;"
										onInput="validateField(this)"
									>
									<small style="display: block; margin-top: 6px; color: #999; font-size: 13px;">Pays d'activite</small>
								</div>
							</div>

							<!-- Row 4: Domain & Logo -->
							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
								<!-- Domain -->
								<div class="form_group">
									<label for="domain" style="display: block; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; font-size: 14px;">Site web</label>
									<input 
										type="url" 
										id="domain"
										name="domain" 
										class="form_input"
										placeholder="https://yourcompany.com"
										style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.3s ease; font-family: inherit;"
										onInput="validateField(this)"
									>
									<small style="display: block; margin-top: 6px; color: #999; font-size: 13px;">URL de votre site web</small>
								</div>

								<!-- Logo Upload -->
								<div class="form_group">
									<label for="logo" style="display: block; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; font-size: 14px;">Logo de l'organisation</label>
									<input 
										type="file" 
										id="logo"
										name="logo" 
										accept="image/*"
										class="form_input"
										style="width: 100%; padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; cursor: pointer; transition: all 0.3s ease;"
										onchange="validateField(this)"
									>
									<small style="display: block; margin-top: 6px; color: #999; font-size: 13px;">PNG, JPG (max 2 Mo)</small>
								</div>
							</div>

							<!-- Description Textarea -->
							<div class="form_group" style="margin-bottom: 25px;">
								<label for="auth_key" style="display: block; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; font-size: 14px;">Cle d'authentification *</label>
								<input
									type="password"
									id="auth_key"
									name="auth_key"
									class="form_input"
									placeholder="Creez votre cle secrete"
									autocomplete="new-password"
									style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.3s ease; font-family: inherit;"
									onInput="validateField(this)"
								>
								<small style="display: block; margin-top: 6px; color: #999; font-size: 13px;">Obligatoire lors de la premiere soumission. Conservez-la precieusement : elle est requise pour consulter, modifier ou supprimer cet enregistrement.</small>
							</div>

							<!-- Description Textarea -->
							<div class="form_group" style="margin-bottom: 30px;">
								<label for="description" style="display: block; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; font-size: 14px;">A propos de votre organisation</label>
								<textarea 
									id="description"
									name="description" 
									class="form_input"
									placeholder="Parlez-nous de votre organisation, de ce que vous faites et de la raison pour laquelle vous souhaitez devenir notre partenaire..."
									rows="5"
									style="width: 100%; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.3s ease; font-family: inherit; resize: vertical; min-height: 120px;"
									onInput="validateField(this)"
								></textarea>
								<small style="display: block; margin-top: 6px; color: #999; font-size: 13px;">Aidez-nous a comprendre votre organisation</small>
							</div>

							<!-- Submit Button -->
							<div style="text-align: center;">
								<button
									type="button"
									id="cancelPartnerEditBtn"
									class="partner_submit_btn"
									style="display: none; background: #6c757d; color: white; padding: 16px 32px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; margin-right: 10px;"
								>
									Cancel Modification
								</button>
								<button 
									type="submit" 
									id="submitPartnerBtn"
									class="partner_submit_btn"
									style="background: linear-gradient(135deg, #ff7f50 0%, #ff6347 100%); color: white; padding: 16px 50px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 5px 15px rgba(255, 127, 80, 0.3); text-transform: uppercase; letter-spacing: 1px;"
									onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(255, 127, 80, 0.4)';"
									onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(255, 127, 80, 0.3)';"
								>
									Postuler comme partenaire
								</button>
								<p style="margin-top: 15px; color: #999; font-size: 13px;">Nous examinerons votre candidature et vous repondrons dans les 48 heures.</p>
							</div>
						</form>
					</div>
				</div>
			</div>
		</section>



		<style>

			.custom-toast {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #4caf50;
    color: white;
    padding: 16px 24px;
    border-radius: 10px;
    font-weight: 600;
    z-index: 10000;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    animation: slideInRight 0.3s ease-out;
    font-family: 'DM Sans', sans-serif;
}
.custom-toast-error {
    background: #f44336;
}
.custom-toast-warning {
    background: #ff9800;
}
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}

			.status_result .approved-card {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
}

.status_result .pending-card {
    background: #fff3e0;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
}

.status_result .rejected-card {
    background: #ffebee;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
}

.status_result .approved-icon, .status_result .pending-icon, .status_result .rejected-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.redirect-btn {
    display: inline-block;
    margin-top: 15px;
    padding: 12px 30px;
    background: linear-gradient(135deg, #ff7f50 0%, #ff6347 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
}

.redirect-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 127, 80, 0.3);
    color: white;
}
			/* Form Animations */
			@keyframes formFadeIn {
				from {
					opacity: 0;
					transform: translateY(30px);
				}
				to {
					opacity: 1;
					transform: translateY(0);
				}
			}

			/* Input Focus Effects */
			.form_input:focus {
				outline: none;
				border-color: #ff7f50 !important;
				box-shadow: 0 0 0 3px rgba(255, 127, 80, 0.1) !important;
				transition: all 0.3s ease;
			}

			/* Valid Field Styling */
			.form_input:valid:not(:placeholder-shown) {
				border-color: #4caf50 !important;
				background-color: #f1f8f4 !important;
			}

			/* Invalid Field Styling */
			.form_input:invalid:not(:placeholder-shown) {
				border-color: #ff6b6b !important;
				background-color: #ffe0e0 !important;
			}

			/* Select Focus */
			select.form_input:focus {
				outline: none;
				border-color: #ff7f50 !important;
				box-shadow: 0 0 0 3px rgba(255, 127, 80, 0.1) !important;
			}

			/* Validation states and messages */
			.form_input.is-valid {
				border-color: #2e7d32 !important;
				background-color: #f1f8f4 !important;
			}

			.form_input.is-invalid {
				border-color: #c62828 !important;
				background-color: #fff1f1 !important;
			}

			.validation-message {
				display: none;
				margin-top: 6px;
				font-size: 13px;
				font-weight: 600;
				color: #c62828;
			}

			.form_alert {
				display: none;
				margin-bottom: 20px;
				padding: 12px 14px;
				border-radius: 8px;
				font-size: 14px;
				font-weight: 600;
			}

			.form_alert.error {
				display: block;
				background-color: #fff1f1;
				border: 1px solid #ef9a9a;
				color: #b71c1c;
			}

			.form_alert.success {
				display: block;
				background-color: #eef8f0;
				border: 1px solid #a5d6a7;
				color: #1b5e20;
			}

			.contract_search_card {
				background: #ffffff;
				border-radius: 12px;
				padding: 26px;
				box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
			}

			.contract_action_btn {
				border: none;
				border-radius: 8px;
				padding: 11px 16px;
				font-size: 14px;
				font-weight: 600;
				cursor: pointer;
			}

			.contract_action_btn-primary {
				background: linear-gradient(135deg, #ff7f50 0%, #ff6347 100%);
				color: #fff;
			}

			.contract_action_btn-secondary {
				background: #f2f2f2;
				color: #333;
			}

			.contract_table_container {
				overflow-x: auto;
				border: 1px solid #ececec;
				border-radius: 10px;
			}

			.contract_table {
				width: 100%;
				border-collapse: collapse;
				min-width: 700px;
			}

			.contract_table th,
			.contract_table td {
				padding: 12px;
				border-bottom: 1px solid #f0f0f0;
				text-align: left;
				font-size: 14px;
			}

			.contract_table th {
				background: #fafafa;
				font-weight: 700;
			}

			.contract_row_actions {
				display: flex;
				gap: 8px;
				flex-wrap: wrap;
			}

			.contract_row_btn {
				border: none;
				border-radius: 6px;
				padding: 7px 10px;
				font-size: 12px;
				font-weight: 600;
				cursor: pointer;
			}

			.contract_row_btn-view {
				background: #e6f4ea;
				color: #1b5e20;
			}

			.contract_row_btn-edit {
				background: #fff4e5;
				color: #8a5800;
			}

			.contract_row_btn-delete {
				background: #ffebee;
				color: #b71c1c;
			}

			.contract_details_box {
				margin-top: 16px;
				border: 1px solid #e8e8e8;
				border-radius: 10px;
				padding: 16px;
				background: #fcfcfc;
			}

			.contract_details_box h4 {
				font-size: 20px;
				margin-bottom: 10px;
			}

			.contract_details_box p {
				margin-bottom: 8px;
				font-size: 14px;
				color: #333;
			}

			/* Textarea focus */
			textarea.form_input:focus {
				outline: none;
				border-color: #ff7f50 !important;
				box-shadow: 0 0 0 3px rgba(255, 127, 80, 0.1) !important;
			}

			/* Input Hover Effect */
			.form_input:hover:not(:focus) {
				border-color: #ff7f50;
				transition: all 0.3s ease;
			}

			/* Mobile Responsive */
			@media (max-width: 768px) {
				div[style*="display: grid; grid-template-columns: 1fr 1fr"] {
					grid-template-columns: 1fr !important;
				}

				div[style*="display: grid; grid-template-columns: 2fr 1fr 1fr"] {
					grid-template-columns: 1fr !important;
				}
				div[style*="display: flex; gap: 12px"] {
    flex-direction: column !important;
}

				.partner_form {
					padding: 30px 20px !important;
				}

				.form_header h2 {
					font-size: 26px !important;
				}
			}
		</style>

		<!-- END PARTNER APPLICATION FORM -->
		
<!-- START MODERN FOOTER -->
    <footer class="modern-footer bg-dark text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="footer-brand">
              <a href="/gestion_users/view/template/index.php" class="text-decoration-none">
                <img src="/gestion_users/assets/img/logo.png" alt="EduMatch Logo" class="mb-3" style="height: 50px;">
                <h3 class="text-white fw-bold">EduMatch</h3>
              </a>
              <p class="mt-3 text-light opacity-75">
                Plateforme intelligente de mise en relation des etudiants avec des professeurs experts dans toutes les matieres academiques pour des experiences d'apprentissage personnalisees.
              </p>
              <div class="social-links mt-3">
                <a href="#" class="text-white me-3 fs-4"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-white me-3 fs-4"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-white me-3 fs-4"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" class="text-white me-3 fs-4"><i class="fab fa-instagram"></i></a>
              </div>
            </div>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Plateforme</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="submit.html" class="text-light text-decoration-none">Soumettre une demande</a></li>
              <li class="mb-2"><a href="feed.html" class="text-light text-decoration-none">Mises en relation</a></li>
              <li class="mb-2"><a href="about.html" class="text-light text-decoration-none">Comment ca marche</a></li>
              <li class="mb-2"><a href="contact.html" class="text-light text-decoration-none">Etre mis en relation</a></li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Matieres academiques</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Mathematiques</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sciences</a></li>
							<li class="mb-2"><a href="#" class="text-light text-decoration-none">Programmation</a></li>
							<li class="mb-2"><a href="#" class="text-light text-decoration-none">Algorithmique</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Langues</a></li>
              <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sciences humaines</a></li>
            </ul>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Coordonnees</h5>
            <div class="contact-info">
              <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Tunisia,Tunis</p>
              <p class="mb-2"><i class="fas fa-phone me-2"></i>+216 90 549 254</p>
              <p class="mb-2"><i class="fas fa-envelope me-2"></i>edumatch@gmail.com</p>
            </div>
            <div class="newsletter mt-3">
              <h6 class="fw-bold mb-2">Restez informe sur le tutorat academique</h6>
              <div class="input-group">
                <input type="email" class="form-control" placeholder="Votre email" style="border-radius: 25px 0 0 25px;">
                <button class="btn btn-primary" type="button" style="border-radius: 0 25px 25px 0;">S'abonner</button>
              </div>
            </div>
          </div>
        </div>
        <hr class="my-4 opacity-25">
        <div class="row align-items-center">
          <div class="col-md-6">
            <p class="mb-0 text-light opacity-75">&copy; 2026 EduMatch. Tous droits reserves.</p>
          </div>
          <div class="col-md-6 text-md-end">
            <a href="#" class="text-light text-decoration-none me-3">Politique de confidentialite</a>
            <a href="#" class="text-light text-decoration-none me-3">Conditions d'utilisation</a>
            <a href="#" class="text-light text-decoration-none">Assistance</a>
          </div>
        </div>
      </div>
    </footer>
    <!-- END MODERN FOOTER -->	
	
	<!-- Latest jQuery -->
		<script src="../../assets/js/jquery-1.12.4.min.js"></script>
	<!-- Latest compiled and minified Bootstrap -->
		<script src="../../assets/bootstrap/js/bootstrap.min.js"></script>
	<!-- modernizer JS -->		
		<script src="../../assets/js/modernizr-2.8.3.min.js"></script>	
	<!-- jquery-simple-mobilemenu.min -->
		<script src="../../assets/js/jquery-simple-mobilemenu.js"></script>		
	<!-- owl-carousel min js  -->
		<script src="../../assets/owlcarousel/js/owl.carousel.min.js"></script>					
	<!-- magnific-popup js -->               
		<script src="../../assets/js/jquery.magnific-popup.min.js"></script>						
	<!-- countTo js -->
		<script src="../../assets/js/jquery.inview.min.js"></script>								
	<!-- scrolltopcontrol js -->
		<script src="../../assets/js/scrolltopcontrol.js"></script>			
	<!-- WOW - Reveal Animations When You Scroll -->
		<script src="../../assets/js/wow.min.js"></script>				
	<!-- scripts js -->
		<script src="../../assets/js/scripts.js"></script>
		<script>
			(function () {
				const form = document.getElementById('partnerForm');
				if (!form) {
					return;
				}

				const STORAGE_KEY = 'edumatch_frontoffice_partners';
				const PARTNER_SYNC_API_URL = '/gestion_users/api/partner_sync.php';
				const alertBox = document.getElementById('partnerFormAlert');
				const searchAlert = document.getElementById('partnerSearchAlert');
				const searchInput = document.getElementById('search_org_name');
				const searchBtn = document.getElementById('searchPartnersBtn');
				const clearSearchBtn = document.getElementById('clearPartnerSearchBtn');
				const resultsWrapper = document.getElementById('partnerResultsWrapper');
				const partnersTableBody = document.getElementById('partnersTableBody');
				const partnersCountInfo = document.getElementById('partnersCountInfo');
				const detailsBox = document.getElementById('partnerDetailsBox');
				const submitBtn = document.getElementById('submitPartnerBtn');
				const cancelEditBtn = document.getElementById('cancelPartnerEditBtn');

				let editingPartnerId = null;
				let currentSearchQuery = '';

				const fields = {
					orgName: document.getElementById('org_name'),
					partnerType: document.getElementById('partner_type'),
					email: document.getElementById('email'),
					phone: document.getElementById('phone'),
					address: document.getElementById('address'),
					country: document.getElementById('country'),
					domain: document.getElementById('domain'),
					logo: document.getElementById('logo'),
					authKey: document.getElementById('auth_key'),
					description: document.getElementById('description')

				};
				const checkOrgNameInput = document.getElementById('check_org_name');
				const checkStatusBtn = document.getElementById('checkStatusBtn');
				const statusResultDiv = document.getElementById('statusResult');

				// Function to show toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'custom-toast' + (type === 'error' ? ' custom-toast-error' : (type === 'warning' ? ' custom-toast-warning' : ''));
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.5s ease-out';
        setTimeout(() => {
            toast.remove();
        }, 500);
    }, 4000);
}

				function showFormAlert(message, type) {
					if (!alertBox) {
						return;
					}

					alertBox.className = 'form_alert ' + type;
					alertBox.textContent = message;
				}

				function showSearchAlert(message, type) {
					if (!searchAlert) {
						return;
					}

					searchAlert.className = 'form_alert ' + type;
					searchAlert.textContent = message;
				}

				function clearFormAlert() {
					if (!alertBox) {
						return;
					}

					alertBox.className = 'form_alert';
					alertBox.textContent = '';
				}

				function clearSearchAlert() {
					if (!searchAlert) {
						return;
					}

					searchAlert.className = 'form_alert';
					searchAlert.textContent = '';
				}

				function getOrCreateMessageBox(field) {
					if (!field) {
						return null;
					}

					const group = field.closest('.form_group');
					if (!group) {
						return null;
					}

					let box = group.querySelector('.validation-message');
					if (!box) {
						box = document.createElement('div');
						box.className = 'validation-message';
						const hint = group.querySelector('small');
						if (hint) {
							hint.insertAdjacentElement('beforebegin', box);
						} else {
							group.appendChild(box);
						}
					}

					return box;
				}

				function setFieldState(field, message) {
					if (!field) {
						return true;
					}

					const messageBox = getOrCreateMessageBox(field);
					const hasValue = field.type === 'file'
						? Boolean(field.files && field.files.length)
						: String(field.value || '').trim() !== '';

					if (message) {
						field.classList.remove('is-valid');
						field.classList.add('is-invalid');
						field.setCustomValidity(message);
						if (messageBox) {
							messageBox.style.display = 'block';
							messageBox.textContent = message;
						}
						return false;
					}

					field.classList.remove('is-invalid');
					field.setCustomValidity('');
					if (hasValue) {
						field.classList.add('is-valid');
					} else {
						field.classList.remove('is-valid');
					}
					if (messageBox) {
						messageBox.style.display = 'none';
						messageBox.textContent = '';
					}
					return true;
				}

				function escapeHtml(value) {
					return String(value || '')
						.replace(/&/g, '&amp;')
						.replace(/</g, '&lt;')
						.replace(/>/g, '&gt;')
						.replace(/"/g, '&quot;')
						.replace(/'/g, '&#39;');
				}

				function isHttpUrl(value) {
					try {
						const parsed = new URL(value);
						return parsed.protocol === 'http:' || parsed.protocol === 'https:';
					} catch (error) {
						return false;
					}
				}

				function isSha256Hash(value) {
					return /^[a-f0-9]{64}$/i.test(String(value || ''));
				}

				async function hashAuthKey(value) {
					const normalized = String(value || '').trim();
					if (!normalized) {
						return '';
					}

					if (window.crypto && window.crypto.subtle && window.TextEncoder) {
						const data = new TextEncoder().encode(normalized);
						const hashBuffer = await window.crypto.subtle.digest('SHA-256', data);
						return Array.from(new Uint8Array(hashBuffer)).map(function (byte) {
							return byte.toString(16).padStart(2, '0');
						}).join('');
					}

					let fallbackHash = 0;
					for (let i = 0; i < normalized.length; i += 1) {
						fallbackHash = ((fallbackHash << 5) - fallbackHash) + normalized.charCodeAt(i);
						fallbackHash |= 0;
					}

					const unsigned = (fallbackHash >>> 0).toString(16).padStart(8, '0');
					return (unsigned + unsigned + unsigned + unsigned + unsigned + unsigned + unsigned + unsigned).slice(0, 64);
				}

async function syncPartnerToDatabase(partner, action) {
    if (!partner || !partner.id) {
        return;
    }

    try {
        const response = await fetch(PARTNER_SYNC_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: action || 'upsert',
                record: {
                    id: partner.id,
                    organization_name: partner.organization_name,
                    partner_type: partner.partner_type,
                    email: partner.email,
                    telephone: partner.telephone,
                    address: partner.address,
                    country: partner.country,
                    domain: partner.domain,
                    logo_file_name: partner.logo_file_name,
                    description: partner.description,
                    status: partner.status || 'pending',
                    auth_key_hash: partner.auth_key_hash,
                    created_at: partner.created_at,
                    updated_at: partner.updated_at
                }
            })
        });

        if (!response.ok) {
            console.warn('Partner sync failed with HTTP status', response.status);
            return;
        }

        const payload = await response.json();
        if (!payload || payload.success !== true) {
            console.warn('Partner sync failed', payload);
        }
    } catch (error) {
        console.warn('Partner sync request failed', error);
    }
}

				// Function to check partner status and redirect if approved
async function checkPartnerStatusAndRedirect(orgName, partnerRecord = null) {
    let partner = partnerRecord;
    
    if (!partner && orgName) {
        const allPartners = loadPartners();
        const normalizedQuery = orgName.toLowerCase();
        partner = allPartners.find(function(p) {
            return String(p.organization_name || '').toLowerCase() === normalizedQuery;
        });
    }
    
    if (!partner) {
        showToast('Organisation introuvable. Veuillez d''abord postuler.', 'error');
        return false;
    }
    
    if (partner.status === 'approved') {
        showToast('\u2705 Votre demande de partenariat a ete acceptee ! Redirection vers la page du contrat...', 'success');
        
        // Store partner info in sessionStorage for the contract page
        sessionStorage.setItem('approved_partner', JSON.stringify({
            id: partner.id,
            organization_name: partner.organization_name,
            email: partner.email,
            status: partner.status
        }));
        
        // Redirect after 2 seconds
        setTimeout(() => {
            window.location.href = 'http://localhost/gestion_users/view/frontoffice/contract.html';
        }, 2000);
        return true;
    } else if (partner.status === 'pending') {
        showToast('\u23F3 Votre demande de partenariat est toujours en cours d''examen. Nous vous informerons une fois approuvee.', 'warning');
        return false;
    } else if (partner.status === 'rejected') {
        showToast('\u274C Votre demande de partenariat a ete refusee. Veuillez nous contacter pour plus d''informations.', 'error');
        return false;
    }
    
    return false;
}

// Nouvelle fonction qui interroge la base de données
async function displayStatusResult(orgName) {
    if (!statusResultDiv) return;
    
    // Afficher un loader
    statusResultDiv.style.display = 'block';
    statusResultDiv.innerHTML = `
        <div style="background: #f5f5f5; border-radius: 12px; padding: 20px; text-align: center;">
            <div style="display: inline-block; width: 30px; height: 30px; border: 3px solid #f3f3f3; border-top: 3px solid #ff7f50; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="margin-top: 10px;">Checking status...</p>
        </div>
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    `;
    
    try {
        // Appel API vers le backend
        const response = await fetch('/gestion_users/api/check_partner_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ organization_name: orgName })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            statusResultDiv.innerHTML = `
                <div style="background: #ffebee; border-radius: 12px; padding: 20px; text-align: center;">
                    <span style="font-size: 48px;">❌</span>
                    <p style="margin-top: 10px; color: #c62828;">Error: ${data.error || 'Something went wrong'}</p>
                </div>
            `;
            return;
        }
        
        if (!data.found) {
            statusResultDiv.innerHTML = `
                <div style="background: #f5f5f5; border-radius: 12px; padding: 20px; text-align: center;">
                    <span style="font-size: 48px;">🔍</span>
                    <p style="margin-top: 10px; color: #666;">No partnership application found for "${escapeHtml(orgName)}". Please submit an application first.</p>
                </div>
            `;
            return;
        }
        
        const partner = data.partner;
        
        if (partner.status === 'approved') {
            statusResultDiv.innerHTML = `
                <div class="approved-card">
                    <div class="approved-icon">✅</div>
                    <h4 style="color: #2e7d32; margin-bottom: 10px;">Partenariat approuve !</h4>
                    <p style="color: #1b5e20;">Felicitations ! Votre demande de partenariat a ete acceptee par EduMatch.</p>
                    <p style="color: #1b5e20; margin-top: 10px;">Cliquez sur le bouton ci-dessous pour proceder au contrat de partenariat.</p>
                    <button id="proceedToContractBtn" class="redirect-btn">Continuer vers le contrat \u2192</button>
                </div>
            `;
            
            const proceedBtn = document.getElementById('proceedToContractBtn');
            if (proceedBtn) {
                proceedBtn.addEventListener('click', () => {
                    sessionStorage.setItem('approved_partner', JSON.stringify({
                        id: partner.id,
                        organization_name: partner.organization_name,
                        email: partner.email,
                        status: partner.status
                    }));
                    window.location.href = 'http://localhost/gestion_users/view/frontoffice/contract.html';
                });
            }
        } else if (partner.status === 'pending') {
            statusResultDiv.innerHTML = `
                <div class="pending-card">
                    <div class="pending-icon">⏳</div>
                    <h4 style="color: #e65100; margin-bottom: 10px;">En cours d'examen</h4>
                    <p style="color: #bf360c;">Votre demande de partenariat est en cours d'examen.</p>
                    <p style="color: #bf360c; margin-top: 10px;">Nous vous informerons une fois une decision prise.</p>
                </div>
            `;
        } else if (partner.status === 'rejected') {
            statusResultDiv.innerHTML = `
                <div class="rejected-card">
                    <div class="rejected-icon">❌</div>
                    <h4 style="color: #c62828; margin-bottom: 10px;">Non approuve</h4>
                    <p style="color: #b71c1c;">Votre demande de partenariat n''a pas ete acceptee pour le moment.</p>
                    <p style="color: #b71c1c; margin-top: 10px;">Veuillez contacter notre equipe pour plus d''informations.</p>
                </div>
            `;
        }
        
    } catch (error) {
        console.error('Error checking status:', error);
        statusResultDiv.innerHTML = `
            <div style="background: #ffebee; border-radius: 12px; padding: 20px; text-align: center;">
                <span style="font-size: 48px;">❌</span>
                <p style="margin-top: 10px; color: #c62828;">Network error. Please try again later.</p>
            </div>
        `;
    }
}

				function loadPartners() {
					const raw = localStorage.getItem(STORAGE_KEY);
					if (!raw) {
						return [];
					}

					try {
						const parsed = JSON.parse(raw);
						return Array.isArray(parsed) ? parsed : [];
					} catch (error) {
						console.error('Invalid partner storage content', error);
						return [];
					}
				}

				function savePartners(partners) {
					localStorage.setItem(STORAGE_KEY, JSON.stringify(partners));
				}

				function getNextSequentialId(records) {
					return Math.floor(Math.random() * 1000000000) + 100000;
				}

				function validateOrgName() {
					const value = fields.orgName.value.trim();
					if (!value) {
						return 'Le nom de l''organisation est requis.';
					}
					if (value.length < 3) {
						return 'Le nom de l''organisation doit contenir au moins 3 caracteres.';
					}
					return '';
				}

				function validatePartnerType() {
					if (!fields.partnerType.value) {
						return 'Veuillez selectionner un type de partenaire.';
					}
					return '';
				}

				function validateEmail() {
					const value = fields.email.value.trim();
					const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
					if (!value) {
						return 'L''adresse email est requise.';
					}
					if (!emailPattern.test(value)) {
						return 'Veuillez entrer une adresse email valide (exemple : contact@entreprise.com).';
					}
					return '';
				}

				function validatePhone() {
					const rawValue = fields.phone.value.trim();
					const digitsOnly = rawValue.replace(/\D/g, '');
					if (!rawValue) {
						return 'Le numero de telephone est requis.';
					}
					if (!/^\d{7,15}$/.test(digitsOnly) || digitsOnly !== rawValue) {
						return 'Le numero de telephone doit contenir entre 7 et 15 chiffres uniquement.';
					}
					return '';
				}

				function validateAddress() {
					const value = fields.address.value.trim();
					if (value && value.length < 5) {
						return 'L''adresse doit contenir au moins 5 caracteres si renseignee.';
					}
					return '';
				}

				function validateCountry() {
					const value = fields.country.value.trim();
					if (value && value.length < 2) {
						return 'Le pays doit contenir au moins 2 caracteres.';
					}
					return '';
				}

				function validateDomain() {
					const value = fields.domain.value.trim();
					if (!value) {
						return '';
					}
					if (!isHttpUrl(value)) {
						return 'Le site web doit commencer par http:// ou https:// et etre une URL valide.';
					}
					return '';
				}

				function validateLogo() {
					if (!fields.logo.files || !fields.logo.files.length) {
						if (editingPartnerId) {
							return '';
						}
						return '';
					}

					const file = fields.logo.files[0];
					if (!file.type.startsWith('image/')) {
						return 'Le logo doit etre un fichier image valide (PNG, JPG, WEBP...).';
					}
					if (file.size > 2 * 1024 * 1024) {
						return 'Le fichier logo depasse la taille maximale autorisee (2 Mo).';
					}
					return '';
				}

				function validateDescription() {
					const value = fields.description.value.trim();
					if (value && value.length < 10) {
						return 'La description doit contenir au moins 10 caracteres si renseignee.';
					}
					return '';
				}

				function validateAuthKey() {
					const value = fields.authKey.value.trim();
					if (!editingPartnerId && !value) {
						return 'La cle d''authentification est requise.';
					}
					if (value && value.length < 6) {
						return 'La cle d''authentification doit contenir au moins 6 caracteres.';
					}
					return '';
				}

				const validators = {
					org_name: validateOrgName,
					partner_type: validatePartnerType,
					email: validateEmail,
					phone: validatePhone,
					address: validateAddress,
					country: validateCountry,
					domain: validateDomain,
					logo: validateLogo,
					auth_key: validateAuthKey,
					description: validateDescription
				};

				window.validateField = function (field) {
					if (!field || !field.id || !validators[field.id]) {
						return true;
					}
					const errorMessage = validators[field.id]();
					return setFieldState(field, errorMessage);
				};

				function validatePartnerForm(showGlobalMessage) {
					let firstInvalidField = null;
					let invalidCount = 0;

					Object.entries(validators).forEach(function (entry) {
						const fieldId = entry[0];
						const validator = entry[1];
						const field = document.getElementById(fieldId);
						const errorMessage = validator();
						const isValid = setFieldState(field, errorMessage);
						if (!isValid) {
							invalidCount += 1;
							if (!firstInvalidField) {
								firstInvalidField = field;
							}
						}
					});

					if (showGlobalMessage) {
						if (invalidCount > 0) {
							showFormAlert('Veuillez corriger les champs en surbrillance avant de soumettre votre demande.', 'error');
							if (firstInvalidField) {
								firstInvalidField.focus();
							}
						} else {
							showFormAlert('Demande de partenariat valide. Votre formulaire est pret a etre envoye.', 'success');
						}
					}

					return invalidCount === 0;
				}

				function collectFormData() {
					const file = fields.logo.files && fields.logo.files[0] ? fields.logo.files[0] : null;
					return {
						organization_name: fields.orgName.value.trim(),
						partner_type: fields.partnerType.value,
						email: fields.email.value.trim(),
						telephone: fields.phone.value.trim(),
						address: fields.address.value.trim(),
						country: fields.country.value.trim(),
						domain: fields.domain.value.trim(),
						auth_key_plain: fields.authKey.value.trim(),
						description: fields.description.value.trim(),
						logo_file_name: file ? file.name : null,
						status: 'pending'
					};
				}

				function resetEditMode() {
					editingPartnerId = null;
					if (submitBtn) {
						submitBtn.textContent = 'Postuler comme partenaire';
					}
					if (cancelEditBtn) {
						cancelEditBtn.style.display = 'none';
					}
				}

				function startEditMode(partner) {
					editingPartnerId = partner.id;
					fields.orgName.value = partner.organization_name || '';
					fields.partnerType.value = partner.partner_type || '';
					fields.email.value = partner.email || '';
					fields.phone.value = partner.telephone || '';
					fields.address.value = partner.address || '';
					fields.country.value = partner.country || '';
					fields.domain.value = partner.domain || '';
					fields.authKey.value = '';
					fields.description.value = partner.description || '';
					fields.logo.value = '';

					if (submitBtn) {
						submitBtn.textContent = 'Enregistrer les modifications';
					}
					if (cancelEditBtn) {
						cancelEditBtn.style.display = 'inline-block';
					}

					showFormAlert('Edit mode is active. Update the fields and click Save Partner Changes. Leave authentication key empty to keep the current key.', 'success');
					window.scrollTo({ top: form.offsetTop - 100, behavior: 'smooth' });
				}

				function hidePartnerDetails() {
					if (!detailsBox) {
						return;
					}

					detailsBox.style.display = 'none';
					detailsBox.innerHTML = '';
				}

				function showPartnerDetails(partner) {
					if (!detailsBox) {
						return;
					}

					detailsBox.innerHTML = '' +
						'<h4>Partner Details</h4>' +
						'<p><strong>Organization:</strong> ' + escapeHtml(partner.organization_name || '-') + '</p>' +
						'<p><strong>Type:</strong> ' + escapeHtml(partner.partner_type || '-') + '</p>' +
						'<p><strong>Email:</strong> ' + escapeHtml(partner.email || '-') + '</p>' +
						'<p><strong>Phone:</strong> ' + escapeHtml(partner.telephone || '-') + '</p>' +
						'<p><strong>Address:</strong> ' + escapeHtml(partner.address || '-') + '</p>' +
						'<p><strong>Country:</strong> ' + escapeHtml(partner.country || '-') + '</p>' +
						'<p><strong>Domain:</strong> ' + escapeHtml(partner.domain || '-') + '</p>' +
						'<p><strong>Logo File:</strong> ' + escapeHtml(partner.logo_file_name || 'Aucun fichier televerse') + '</p>' +
						'<p><strong>Description:</strong> ' + escapeHtml(partner.description || '-') + '</p>';

					detailsBox.style.display = 'block';
				}

				function findPartnerById(partnerId) {
					const partners = loadPartners();
					for (let i = 0; i < partners.length; i += 1) {
						if (String(partners[i].id) === String(partnerId)) {
							return partners[i];
						}
					}
					return null;
				}

				function deletePartnerById(partnerId) {
					const partners = loadPartners();
					const updated = partners.filter(function (partner) {
						return String(partner.id) !== String(partnerId);
					});
					savePartners(updated);
				}

				async function authorizePartnerAction(partnerId, actionLabel) {
					const partners = loadPartners();
					const targetIndex = partners.findIndex(function (partner) {
						return String(partner.id) === String(partnerId);
					});

					if (targetIndex === -1) {
						showSearchAlert('Partenaire introuvable. Veuillez chercher a nouveau.', 'error');
						return null;
					}

					const partner = partners[targetIndex];
					const promptText = 'Enter the authentication key to ' + actionLabel + ' this partner record:';
					const enteredKey = window.prompt(promptText, '');
					if (enteredKey === null) {
						return null;
					}

					const normalizedKey = enteredKey.trim();
					if (!normalizedKey) {
						showSearchAlert('La cle d''authentification est requise.', 'error');
						return null;
					}

					if (normalizedKey.length < 6) {
						showSearchAlert('La cle d''authentification doit contenir au moins 6 caracteres.', 'error');
						return null;
					}

					const enteredHash = await hashAuthKey(normalizedKey);

					if (partner.auth_key_hash) {
						if (enteredHash !== partner.auth_key_hash) {
							showSearchAlert('Cle d''authentification invalide.', 'error');
							return null;
						}
					} else {
						partner.auth_key_hash = enteredHash;
						partner.updated_at = new Date().toISOString();
						partners[targetIndex] = partner;
						savePartners(partners);
						await syncPartnerToDatabase(partner, 'upsert');
						showSearchAlert('Authentication key set for this existing partner record.', 'success');
					}

					return partner;
				}

				function renderSearchResults(query) {
					const trimmedQuery = String(query || '').trim();
					currentSearchQuery = trimmedQuery;

					if (!trimmedQuery) {
						resultsWrapper.style.display = 'none';
						hidePartnerDetails();
						showSearchAlert('Veuillez entrer un nom d''organisation avant de rechercher.', 'error');
						return;
					}

					const allPartners = loadPartners();
					const normalizedQuery = trimmedQuery.toLowerCase();
					const filteredPartners = allPartners.filter(function (partner) {
						return String(partner.organization_name || '').toLowerCase().includes(normalizedQuery);
					});

					resultsWrapper.style.display = 'block';
					hidePartnerDetails();
					clearSearchAlert();

					if (partnersCountInfo) {
						partnersCountInfo.textContent = filteredPartners.length + ' partner(s) found for "' + trimmedQuery + '".';
					}

					if (!filteredPartners.length) {
						partnersTableBody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:16px;">No partners found for this organization name.</td></tr>';
						return;
					}

					partnersTableBody.innerHTML = filteredPartners.map(function (partner) {
						return '' +
							'<tr>' +
								'<td>' + escapeHtml(partner.organization_name || '-') + '</td>' +
								'<td>' + escapeHtml(partner.partner_type || '-') + '</td>' +
								'<td>' +
									'<div class="contract_row_actions">' +
										'<button type="button" class="contract_row_btn contract_row_btn-view" data-action="view" data-id="' + escapeHtml(partner.id) + '">View</button>' +
										'<button type="button" class="contract_row_btn contract_row_btn-edit" data-action="edit" data-id="' + escapeHtml(partner.id) + '">Modify</button>' +
										'<button type="button" class="contract_row_btn contract_row_btn-delete" data-action="delete" data-id="' + escapeHtml(partner.id) + '">Delete</button>' +
									'</div>' +
								'</td>' +
							'</tr>';
					}).join('');
				}

				Object.values(fields).forEach(function (field) {
					if (!field) {
						return;
					}

					const eventName = field.tagName === 'SELECT' || field.type === 'file' ? 'change' : 'input';
					field.addEventListener(eventName, function () {
						window.validateField(field);
						clearFormAlert();
					});
				});

				if (searchBtn) {
					searchBtn.addEventListener('click', function () {
						renderSearchResults(searchInput ? searchInput.value : '');
					});
				}

				if (searchInput) {
					searchInput.addEventListener('keydown', function (event) {
						if (event.key === 'Enter') {
							event.preventDefault();
							renderSearchResults(searchInput.value);
						}
					});
				}

				if (clearSearchBtn) {
					clearSearchBtn.addEventListener('click', function () {
						if (searchInput) {
							searchInput.value = '';
						}
						currentSearchQuery = '';
						resultsWrapper.style.display = 'none';
						hidePartnerDetails();
						clearSearchAlert();
					});
				}

				if (partnersTableBody) {
					partnersTableBody.addEventListener('click', async function (event) {
						const target = event.target;
						if (!(target instanceof HTMLElement)) {
							return;
						}

						const button = target.closest('button[data-action]');
						if (!button) {
							return;
						}

						const partnerId = button.getAttribute('data-id');
						const action = button.getAttribute('data-action');
						if (!partnerId || !action) {
							return;
						}

						if (action === 'Voir') {
							const authorizedPartner = await authorizePartnerAction(partnerId, 'Voir');
							if (!authorizedPartner) {
								return;
							}
							showPartnerDetails(authorizedPartner);
							return;
						}

						if (action === 'edit') {
							const authorizedPartner = await authorizePartnerAction(partnerId, 'Modifier');
							if (!authorizedPartner) {
								return;
							}
							startEditMode(authorizedPartner);
							return;
						}

						if (action === 'Supprimer') {
							const authorizedPartner = await authorizePartnerAction(partnerId, 'Supprimer');
							if (!authorizedPartner) {
								return;
							}

							if (!confirm('Voulez-vous vraiment supprimer ce partenaire ?')) {
								return;
							}

							deletePartnerById(partnerId);
							await syncPartnerToDatabase(authorizedPartner, 'Supprimer');
							if (String(editingPartnerId) === String(partnerId)) {
								form.reset();
								Object.values(fields).forEach(function (field) {
									if (field) {
										setFieldState(field, '');
									}
								});
								resetEditMode();
							}
							renderSearchResults(currentSearchQuery);
							showSearchAlert('Partenaire supprime avec succes.', 'success');
						}
					});
				}

				if (cancelEditBtn) {
					cancelEditBtn.addEventListener('click', function () {
						form.reset();
						Object.values(fields).forEach(function (field) {
							if (field) {
								setFieldState(field, '');
							}
						});
						resetEditMode();
						showFormAlert('Edition annulee.', 'success');
					});
				}
				// Check status button handler
if (checkStatusBtn) {
    checkStatusBtn.addEventListener('click', function () {
        const orgName = checkOrgNameInput ? checkOrgNameInput.value.trim() : '';
        if (!orgName) {
            showToast('Veuillez entrer un nom d''organisation', 'warning');
            return;
        }
        displayStatusResult(orgName);
    });
}

if (checkOrgNameInput) {
    checkOrgNameInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const orgName = checkOrgNameInput.value.trim();
            if (orgName) {
                displayStatusResult(orgName);
            } else {
                showToast('Veuillez entrer un nom d''organisation', 'warning');
            }
        }
    });
}

				form.addEventListener('submit', async function (event) {
					event.preventDefault();
					const isFormValid = validatePartnerForm(true);
					if (!isFormValid) {
						return;
					}

					const payload = collectFormData();
					const partners = loadPartners();

					if (editingPartnerId) {
						const targetIndex = partners.findIndex(function (partner) {
							return String(partner.id) === String(editingPartnerId);
						});
						if (targetIndex === -1) {
							showFormAlert('Le partenaire a modifier est introuvable.', 'error');
							return;
						}

						const existingPartner = partners[targetIndex];
						const nextAuthHash = payload.auth_key_plain
							? await hashAuthKey(payload.auth_key_plain)
							: (existingPartner.auth_key_hash || '');

						if (!isSha256Hash(nextAuthHash)) {
							showFormAlert('Une cle d''authentification valide est requise pour securiser cet enregistrement.', 'error');
							return;
						}

						partners[targetIndex] = {
							...existingPartner,
							...payload,
							auth_key_hash: nextAuthHash,
							logo_file_name: payload.logo_file_name || existingPartner.logo_file_name || null,
							updated_at: new Date().toISOString()
						};
						delete partners[targetIndex].auth_key_plain;
						savePartners(partners);
						await syncPartnerToDatabase(partners[targetIndex], 'upsert');
						showFormAlert('Partenaire mis a jour avec succes.', 'success');
						resetEditMode();
					} else {
						const authHash = await hashAuthKey(payload.auth_key_plain);
						if (!isSha256Hash(authHash)) {
							showFormAlert('Une cle d''authentification valide est requise avant l''enregistrement.', 'error');
							return;
						}

						const createdPartner = {
							id: getNextSequentialId(partners),
							...payload,
							auth_key_hash: authHash,
							created_at: new Date().toISOString()
						};
						delete createdPartner.auth_key_plain;
						partners.unshift({
							...createdPartner
						});
						savePartners(partners);
						await syncPartnerToDatabase(createdPartner, 'upsert');
						showFormAlert('Demande de partenariat enregistree avec succes.', 'success');
					}

					form.reset();
					Object.values(fields).forEach(function (field) {
						if (field) {
							setFieldState(field, '');
						}
					});

					if (currentSearchQuery) {
						renderSearchResults(currentSearchQuery);
					}
				});

				resetEditMode();
				if (resultsWrapper) {
					resultsWrapper.style.display = 'none';
				}
				hidePartnerDetails();
			})();
		</script>
    <!-- Note : le chatbot EduMatch est desormais inclus dans _navbar.php (charge en haut de page).
         L'ancien fetch('chatbot.html') a ete retire pour eviter le doublon. -->
    </body>
</html>