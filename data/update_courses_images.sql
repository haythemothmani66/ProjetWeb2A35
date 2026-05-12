-- =====================================================
-- UPDATE images des courses (URLs Unsplash thematiques)
-- =====================================================
-- Mise a jour ciblee par titre pour matcher visuellement le contenu pedagogique
-- =====================================================

USE edumatch;

-- HTML5 Fondamentaux : code HTML sur ecran
UPDATE courses
SET image = 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=800&h=500&fit=crop'
WHERE title LIKE '%HTML%' OR title LIKE '%html%';

-- CSS3 & Design Moderne : ecran avec design colore
UPDATE courses
SET image = 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=500&fit=crop'
WHERE title LIKE '%CSS%' OR title LIKE '%Design%';

-- JavaScript ES6+ : code JS bleu sur ecran
UPDATE courses
SET image = 'https://images.unsplash.com/photo-1579468118864-1b9ea3c0db4a?w=800&h=500&fit=crop'
WHERE title LIKE '%JavaScript%' OR title LIKE '%JS%';

-- PHP 8 & POO : code PHP / serveur
UPDATE courses
SET image = 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=800&h=500&fit=crop'
WHERE title LIKE '%PHP%';

-- Python pour Data Science : code Python + data viz
UPDATE courses
SET image = 'https://images.unsplash.com/photo-1526379095098-d400fd0bf935?w=800&h=500&fit=crop'
WHERE title LIKE '%Python%';

-- React.js : interface moderne / logo React
UPDATE courses
SET image = 'https://images.unsplash.com/photo-1633356122102-3fe601e05bd2?w=800&h=500&fit=crop'
WHERE title LIKE '%React%';

-- Bases de donnees / SQL : tables, schema
UPDATE courses
SET image = 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=800&h=500&fit=crop'
WHERE title LIKE '%base%donn%' OR title LIKE '%SQL%' OR title LIKE '%database%';

-- Fallback pour tout autre cours sans image : code generique
UPDATE courses
SET image = 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&h=500&fit=crop'
WHERE image IS NULL OR image = '';

-- Verification
SELECT id, title, image FROM courses;
