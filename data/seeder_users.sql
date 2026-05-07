-- ============================================================
-- EduMatch — Seeder : 15 utilisateurs de test
-- Mot de passe pour tous : Test1234!
-- 12 vérifiés (token_verif = NULL), 3 non vérifiés
-- 2 bloqués (statut = 0)
-- ============================================================

SET @pwd = '$2y$10$Wtv8h2apotsQLxK15tt3kuH6LUzZ3k8P5HAcR4vD5I9rrpHajf6uu';

-- ============================================================
-- 1. Ahmed Ben Ali — etudiant, vérifié
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Ben Ali', 'Ahmed', 'ahmed.benali@esprit.tn', @pwd, '20123456', 'etudiant', 1, 'default.png', NULL, '2026-04-01 09:15:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), 'Etudiant en informatique passionne par le web.', '2eme annee', NULL, '2026-04-01 09:15:00');

-- ============================================================
-- 2. Sarra Trabelsi — etudiant, vérifiée
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Trabelsi', 'Sarra', 'sarra.trabelsi@esprit.tn', @pwd, '25987654', 'etudiant', 1, 'default.png', NULL, '2026-04-02 10:30:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), 'Etudiante en genie logiciel.', '3eme annee', NULL, '2026-04-02 10:30:00');

-- ============================================================
-- 3. Mohamed Bouazizi — etudiant, vérifié
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Bouazizi', 'Mohamed', 'mohamed.bouazizi@esprit.tn', @pwd, '29456123', 'etudiant', 1, 'default.png', NULL, '2026-04-03 14:00:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), 'Fan de cybersecurite et reseaux.', '1ere annee', NULL, '2026-04-03 14:00:00');

-- ============================================================
-- 4. Fatma Hammami — etudiant, vérifiée
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Hammami', 'Fatma', 'fatma.hammami@esprit.tn', @pwd, '22789456', 'etudiant', 1, 'default.png', NULL, '2026-04-05 08:45:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), 'Etudiante motivee, specialite IA.', '2eme annee', NULL, '2026-04-05 08:45:00');

-- ============================================================
-- 5. Youssef Jebali — etudiant, vérifié
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Jebali', 'Youssef', 'youssef.jebali@esprit.tn', @pwd, '27654321', 'etudiant', 1, 'default.png', NULL, '2026-04-06 11:20:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), 'Passionne par le developpement mobile.', '3eme annee', NULL, '2026-04-06 11:20:00');

-- ============================================================
-- 6. Nabil Gharbi — encadrant, vérifié
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Gharbi', 'Nabil', 'nabil.gharbi@esprit.tn', @pwd, '98123456', 'encadrant', 1, 'default.png', NULL, '2026-04-07 09:00:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), 'Enseignant en developpement web depuis 10 ans.', NULL, 'Developpement Web', '2026-04-07 09:00:00');

-- ============================================================
-- 7. Amira Ksouri — encadrant, vérifiée
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Ksouri', 'Amira', 'amira.ksouri@esprit.tn', @pwd, '97654321', 'encadrant', 1, 'default.png', NULL, '2026-04-08 10:15:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), 'Specialiste en intelligence artificielle et data.', NULL, 'Intelligence Artificielle', '2026-04-08 10:15:00');

-- ============================================================
-- 8. Karim Mejri — encadrant, vérifié
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Mejri', 'Karim', 'karim.mejri@esprit.tn', @pwd, '96789012', 'encadrant', 1, 'default.png', NULL, '2026-04-09 13:30:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), 'Expert en securite informatique et reseaux.', NULL, 'Cybersecurite', '2026-04-09 13:30:00');

-- ============================================================
-- 9. Ines Sfar — encadrant, vérifiée
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Sfar', 'Ines', 'ines.sfar@esprit.tn', @pwd, '95456789', 'encadrant', 1, 'default.png', NULL, '2026-04-10 15:00:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), 'Professeure de bases de donnees et systemes.', NULL, 'Bases de Donnees', '2026-04-10 15:00:00');

-- ============================================================
-- 10. Hichem Abidi — encadrant, vérifié
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Abidi', 'Hichem', 'hichem.abidi@esprit.tn', @pwd, '94321654', 'encadrant', 1, 'default.png', NULL, '2026-04-11 08:30:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), 'Encadrant projets de fin d etude, genie logiciel.', NULL, 'Genie Logiciel', '2026-04-11 08:30:00');

-- ============================================================
-- 11. Rania Mansouri — etudiant, vérifiée, BLOQUÉE (statut=0)
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Mansouri', 'Rania', 'rania.mansouri@esprit.tn', @pwd, '23111222', 'etudiant', 0, 'default.png', NULL, '2026-04-12 09:45:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), 'Etudiante en premiere annee.', '1ere annee', NULL, '2026-04-12 09:45:00');

-- ============================================================
-- 12. Bilel Oueslati — etudiant, vérifié, BLOQUÉ (statut=0)
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Oueslati', 'Bilel', 'bilel.oueslati@esprit.tn', @pwd, '26333444', 'etudiant', 0, 'default.png', NULL, '2026-04-13 14:20:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), 'Etudiant en deuxieme annee informatique.', '2eme annee', NULL, '2026-04-13 14:20:00');

-- ============================================================
-- 13. Aya Chaabane — etudiant, NON VÉRIFIÉE (token_verif rempli)
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Chaabane', 'Aya', 'aya.chaabane@esprit.tn', @pwd, '21555666', 'etudiant', 1, 'default.png', 'a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2', '2026-04-25 16:00:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), NULL, '1ere annee', NULL, '2026-04-25 16:00:00');

-- ============================================================
-- 14. Oussama Bouzid — etudiant, NON VÉRIFIÉ (token_verif rempli)
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Bouzid', 'Oussama', 'oussama.bouzid@esprit.tn', @pwd, '28777888', 'etudiant', 1, 'default.png', 'f6e5d4c3b2a1f6e5d4c3b2a1f6e5d4c3b2a1f6e5d4c3b2a1f6e5d4c3b2a1f6e5', '2026-04-26 10:30:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), NULL, '2eme annee', NULL, '2026-04-26 10:30:00');

-- ============================================================
-- 15. Mariem Riahi — encadrant, NON VÉRIFIÉE (token_verif rempli)
-- ============================================================
INSERT INTO user (nom, prenom, email, password, telephone, role, statut, photo, token_verif, created_at)
VALUES ('Riahi', 'Mariem', 'mariem.riahi@esprit.tn', @pwd, '24999000', 'encadrant', 1, 'default.png', 'deadbeefcafebabe1234567890abcdef1234567890abcdef1234567890abcdef12', '2026-04-27 12:00:00');
INSERT INTO profil (user_id, bio_text, niveau, specialite, created_at)
VALUES (LAST_INSERT_ID(), NULL, NULL, 'Mathematiques Appliquees', '2026-04-27 12:00:00');
