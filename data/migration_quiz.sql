-- =====================================================
-- Migration Module Quiz / Formations / Certificats
-- =====================================================
-- 6 tables : courses, lessons, quizzes, questions, responses, certificates
-- FK courses.user_id   -> user(id)  ON DELETE SET NULL  (createur encadrant)
-- FK certificates.user_id -> user(id)  ON DELETE SET NULL  (etudiant qui a passe le quiz)
-- =====================================================

USE edumatch;

-- 1. Cours (createur = encadrant + admin)
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_course_user
        FOREIGN KEY (user_id) REFERENCES user(id)
        ON DELETE SET NULL
);

-- 2. Quiz lie a un cours
CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    duration_minutes INT NOT NULL DEFAULT 30,
    passing_score DECIMAL(5,2) DEFAULT 50.00,
    max_attempts INT DEFAULT 1,
    is_timed TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_quiz_course
        FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE
);

-- 3. Lecons d'un cours
CREATE TABLE IF NOT EXISTS lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    summary TEXT DEFAULT NULL,
    content LONGTEXT DEFAULT NULL,
    duration_minutes INT NOT NULL DEFAULT 10,
    lesson_order INT NOT NULL DEFAULT 1,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_lesson_course
        FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE
);

-- 4. Questions d'un quiz
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('single_choice', 'multiple_choice', 'true_false') DEFAULT 'single_choice',
    points DECIMAL(5,2) DEFAULT 1.00,
    question_order INT DEFAULT 1,
    explanation TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_question_quiz
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
        ON DELETE CASCADE
);

-- 5. Reponses possibles d'une question
CREATE TABLE IF NOT EXISTS responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    response_text TEXT NOT NULL,
    is_correct TINYINT(1) DEFAULT 0,
    response_order INT DEFAULT 1,
    explanation TEXT DEFAULT NULL,
    CONSTRAINT fk_response_question
        FOREIGN KEY (question_id) REFERENCES questions(id)
        ON DELETE CASCADE
);

-- 6. Certificats delivres aux etudiants
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    course_id INT NOT NULL,
    quiz_id INT NOT NULL,
    student_name VARCHAR(255) NOT NULL,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cert_user
        FOREIGN KEY (user_id) REFERENCES user(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_cert_course
        FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_cert_quiz
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
        ON DELETE CASCADE
);

-- =====================================================
-- Donnees de demo (1 cours publie + 1 quiz + 3 questions)
-- =====================================================

INSERT INTO courses (user_id, title, description, level, status) VALUES
    (NULL, 'Introduction au HTML', 'Apprenez les bases du HTML5 pour creer vos premieres pages web. Structure, balises, semantique et bonnes pratiques.', 'beginner', 'published'),
    (NULL, 'CSS Avance & Flexbox', 'Maitrisez le CSS moderne avec Flexbox, Grid et animations. Concevez des interfaces responsive professionnelles.', 'intermediate', 'published'),
    (NULL, 'JavaScript ES6+', 'Le JavaScript moderne : let/const, arrow functions, promises, async/await, modules. Programmation orientee objet et fonctionnelle.', 'advanced', 'draft');

-- Quiz attache au cours HTML (id=1)
INSERT INTO quizzes (course_id, title, description, duration_minutes, passing_score) VALUES
    (1, 'Quiz HTML - Niveau debutant', 'Verifiez vos connaissances de base en HTML.', 15, 60.00);

-- 3 lecons pour HTML
INSERT INTO lessons (course_id, title, summary, content, lesson_order, status) VALUES
    (1, 'Structure d''une page HTML', 'Decouvrez la structure de base d''un document HTML.', '<h2>Document HTML5</h2><p>Tout document HTML commence par <code>&lt;!DOCTYPE html&gt;</code>...</p>', 1, 'published'),
    (1, 'Les balises essentielles', 'Apprenez les balises les plus utilisees : div, p, h1-h6, a, img.', '<h2>Balises de base</h2><p>Le HTML offre plus de 100 balises differentes...</p>', 2, 'published'),
    (1, 'Formulaires HTML', 'Creez vos premiers formulaires avec input, label, select, textarea.', '<h2>Formulaires</h2><p>Les formulaires permettent de collecter des donnees utilisateur...</p>', 3, 'published');

-- 3 questions pour le quiz HTML (id=1)
INSERT INTO questions (quiz_id, question_text, question_type, points, question_order, explanation) VALUES
    (1, 'Quelle balise HTML est utilisee pour le titre principal d''une page ?', 'single_choice', 1.00, 1, 'La balise <h1> represente le titre de niveau 1, le plus important.'),
    (1, 'Quel attribut HTML est utilise pour rendre un champ obligatoire ?', 'single_choice', 1.00, 2, 'L''attribut required force la saisie du champ avant soumission.'),
    (1, 'Le HTML est un langage de programmation.', 'true_false', 1.00, 3, 'Le HTML est un langage de balisage (markup), pas de programmation.');

-- Reponses pour question 1
INSERT INTO responses (question_id, response_text, is_correct, response_order) VALUES
    (1, '<title>', 0, 1),
    (1, '<h1>', 1, 2),
    (1, '<header>', 0, 3),
    (1, '<head>', 0, 4);

-- Reponses pour question 2
INSERT INTO responses (question_id, response_text, is_correct, response_order) VALUES
    (2, 'mandatory', 0, 1),
    (2, 'required', 1, 2),
    (2, 'obligatory', 0, 3),
    (2, 'needed', 0, 4);

-- Reponses pour question 3 (vrai/faux)
INSERT INTO responses (question_id, response_text, is_correct, response_order) VALUES
    (3, 'Vrai', 0, 1),
    (3, 'Faux', 1, 2);
