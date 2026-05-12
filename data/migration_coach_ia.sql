-- =====================================================
-- Migration : Coach IA Adaptatif (metier avance gestion_devoirs)
-- =====================================================
-- Cette table stocke les plans de progression personnalises generes par IA Groq
-- a partir de l'historique des devoirs + corrections de l'etudiant.
-- =====================================================

USE edumatch;

CREATE TABLE IF NOT EXISTS coach_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,

    -- Plan brut (JSON) genere par l'IA
    plan_json LONGTEXT NOT NULL,

    -- Snapshot du bilan (radar competences, types erreurs, sentiment) au moment de la generation
    snapshot_json LONGTEXT DEFAULT NULL,

    -- Stats du context (pour traceabilite + filtre)
    nb_devoirs_analyses INT DEFAULT 0,
    nb_corrections_analysees INT DEFAULT 0,
    note_moyenne DECIMAL(5,2) DEFAULT NULL,

    -- Meta
    status ENUM('active', 'archived', 'completed') DEFAULT 'active',
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,

    -- Indicateurs IA (couleur du score, recommandation_level h/m/l)
    overall_score INT DEFAULT NULL,
    recommendation_level ENUM('high', 'medium', 'low') DEFAULT 'medium',

    CONSTRAINT fk_coach_user
        FOREIGN KEY (user_id) REFERENCES user(id)
        ON DELETE CASCADE,

    INDEX idx_user_status (user_id, status),
    INDEX idx_generated (generated_at DESC)
);
