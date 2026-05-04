<?php
require_once __DIR__ . '/config/Database.php';

try {
    $db = Database::connection();
    echo "Connected successfully to Database.\n";

    $sql = "
    CREATE TABLE IF NOT EXISTS certificates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        quiz_id INT NOT NULL,
        student_name VARCHAR(255) NOT NULL,
        issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        CONSTRAINT fk_cert_course
            FOREIGN KEY (course_id) REFERENCES courses(id)
            ON DELETE CASCADE,
        CONSTRAINT fk_cert_quiz
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
            ON DELETE CASCADE
    );
    ";

    $db->exec($sql);
    echo "Certificates table created successfully.\n";

} catch (PDOException $e) {
    echo "Connection or Execution failed: " . $e->getMessage() . "\n";
}
