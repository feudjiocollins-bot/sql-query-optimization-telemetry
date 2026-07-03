-- Structure de la table 'applications'
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(100) NOT NULL,
    environment VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Structure de la table 'system_logs'
CREATE TABLE system_logs (
    id BIGINT AUTO_INCREMENT,
    level VARCHAR(20) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    response_time_ms INT NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    app_id INT NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (app_id) REFERENCES applications(id)
);

-- Index composite créé pour l'optimisation des requêtes de statistiques
CREATE INDEX idx_logs_performance ON system_logs (level, endpoint, app_id, response_time_ms);
