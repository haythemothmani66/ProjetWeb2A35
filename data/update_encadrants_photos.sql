-- =====================================================
-- UPDATE photos encadrants (avatars Unsplash telecharges localement)
-- =====================================================
-- Les 7 encadrants existants recoivent une photo professionnelle
-- Les fichiers sont dans uploads/photos/encadrant_<prenom>_<nom>.jpg
-- =====================================================

USE edumatch;

-- Match par id (plus fiable que prenom/nom qui peuvent avoir des accents/casse)
UPDATE user SET photo = 'encadrant_nabil_gharbi.jpg'         WHERE id = 11; -- Nabil Gharbi
UPDATE user SET photo = 'encadrant_amira_ksouri.jpg'         WHERE id = 12; -- Amira Ksouri
UPDATE user SET photo = 'encadrant_ines_sfar.jpg'            WHERE id = 14; -- Ines Sfar
UPDATE user SET photo = 'encadrant_hichem_abidi.jpg'         WHERE id = 15; -- Hichem Abidi
UPDATE user SET photo = 'encadrant_mariem_riahi.jpg'         WHERE id = 20; -- Mariem Riahi
UPDATE user SET photo = 'encadrant_anas_bjaoui.jpg'          WHERE id = 27; -- Anas Bjaoui
UPDATE user SET photo = 'encadrant_yassmine_abdennadher.jpg' WHERE id = 31; -- Yassmine Abdennadher

-- Verification
SELECT id, prenom, nom, photo FROM user WHERE role='encadrant';
