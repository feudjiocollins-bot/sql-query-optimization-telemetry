<?php
// 1. Connexion à la base de données
$host = '127.0.0.1';
$db   = 'télémétrie_logs';
$user = 'root';
$pass = ''; // Mets ton mot de passe si tu en as un sur XAMPP
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "⚡ Connexion réussie à la base de données.\n<br>";
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// 2. Insertion de fausses applications (si elles n'existent pas)
$pdo->exec("INSERT IGNORE INTO applications (id, app_name, environment) VALUES 
(1, 'E-Commerce API', 'production'),
(2, 'Payment Gateway', 'production'),
(3, 'Auth Service', 'staging')");

echo "📱 Applications prêtes.\n<br>";

// 3. Configuration pour l'injection de masse
set_time_limit(0); // Désactive la limite de temps d'exécution PHP
ini_set('memory_limit', '512M');

$totalRows = 100000; // Nombre de logs à générer
$batchSize = 5000;   // On insère par paquets de 5000 pour aller super vite

$endpoints = ['/api/v1/products', '/api/v1/checkout', '/api/v1/login', '/api/v1/user/profile', '/api/v1/payment'];
$levels = ['INFO', 'INFO', 'INFO', 'WARNING', 'ERROR', 'CRITICAL']; // Plus d'INFO que d'ERROR pour faire réaliste

$sql = "INSERT INTO system_logs (app_id, level, endpoint, response_time_ms, message, created_at) VALUES ";

echo "⏳ Début de la génération de $totalRows logs... Veuillez patienter.\n<br>";
flush();

$pdo->beginTransaction(); // Utilisation d'une transaction pour accélérer l'écriture

for ($i = 0; $i < $totalRows; $i += $batchSize) {
    $rows = [];
    $queryParts = [];
    
    for ($j = 0; $j < $batchSize; $j++) {
        $appId = rand(1, 3);
        $level = $levels[array_rand($levels)];
        $endpoint = $endpoints[array_rand($endpoints)];
        
        // Simuler des ralentissements : si c'est un paiement ou une erreur, le temps est plus long
        $responseTime = ($endpoint === '/api/v1/payment' || $level === 'ERROR') ? rand(800, 3500) : rand(10, 150);
        
        $message = "System event triggered with status " . ($level === 'ERROR' ? '500 Internal Server Error' : '200 OK');
        
        // Date aléatoire sur les 30 derniers jours
        $randomTimestamp = time() - rand(0, 30 * 24 * 60 * 60);
        $createdAt = date('Y-m-d H:i:s', $randomTimestamp);

        $queryParts[] = "(?, ?, ?, ?, ?, ?)";
        $rows[] = $appId;
        $rows[] = $level;
        $rows[] = $endpoint;
        $rows[] = $responseTime;
        $rows[] = $message;
        $rows[] = $createdAt;
    }
    
    $stmt = $pdo->prepare($sql . implode(',', $queryParts));
    $stmt->execute($rows);
}

$pdo->commit(); // Validation de la transaction

echo "✅ Terminé ! 100 000 logs injectés avec succès en base de données.\n";
?>
