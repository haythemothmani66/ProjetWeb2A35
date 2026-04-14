<?php
require_once __DIR__ . '/config/Database.php';

try {
    $db = Database::connection();
    echo "Connected successfully to Database.\n";

    $sql = "
    CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        image VARCHAR(255) DEFAULT NULL,

        level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
        status ENUM('draft', 'published', 'archived') DEFAULT 'draft',

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

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

    CREATE TABLE IF NOT EXISTS questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        quiz_id INT NOT NULL,

        question_text TEXT NOT NULL,

        question_type ENUM(
            'single_choice',
            'multiple_choice',
            'true_false'
        ) DEFAULT 'single_choice',

        points DECIMAL(5,2) DEFAULT 1.00,
        question_order INT DEFAULT 1,

        explanation TEXT DEFAULT NULL,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        CONSTRAINT fk_question_quiz
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
            ON DELETE CASCADE
    );

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
    ";

    $db->exec($sql);
    echo "Tables created successfully.\n";

} catch (PDOException $e) {
    echo "Connection or Execution failed: " . $e->getMessage() . "\n";
}
