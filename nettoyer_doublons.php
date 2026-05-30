<?php
require_once __DIR__ . '/config/database.php';
$pdo = getPDO();

try {
    // 1. Afficher les doublons
    echo "<h2>🔍 Recherche des doublons...</h2>";
    $stmt = $pdo->query("
        SELECT id, annee, titre_fr, COUNT(*) as count 
        FROM historique 
        GROUP BY annee, titre_fr 
        HAVING count > 1
    ");
    
    $doublons = $stmt->fetchAll();
    if (empty($doublons)) {
        echo "<p style='color:green'>✅ Aucun doublon trouvé !</p>";
    } else {
        echo "<h3>⚠️ Doublons trouvés :</h3>";
        echo "<ul>";
        foreach ($doublons as $d) {
            echo "<li>Année {$d['annee']} : {$d['titre_fr']} ({$d['count']} fois)</li>";
        }
        echo "</ul>";
        
        // 2. Supprimer les doublons (garde le premier)
        echo "<h3>🗑️ Suppression des doublons...</h3>";
        $pdo->exec("
            DELETE h1 FROM historique h1
            INNER JOIN historique h2 
            WHERE h1.id > h2.id 
            AND h1.annee = h2.annee 
            AND h1.titre_fr = h2.titre_fr
        ");
        
        $count = $pdo->exec("
            DELETE FROM historique 
            WHERE id IN (
                SELECT id FROM (
                    SELECT MIN(id) as min_id, annee, titre_fr, COUNT(*) as c
                    FROM historique 
                    GROUP BY annee, titre_fr
                    HAVING c > 1
                ) as duplicates
            )
        ");
        
        echo "<p style='color:green'>✅ Suppression terminée !</p>";
    }
    
    // 3. Afficher les événements restants
    echo "<h2>📋 Événements dans la base :</h2>";
    $events = $pdo->query("SELECT id, annee, titre_fr FROM historique ORDER BY annee");
    echo "<ul>";
    while ($event = $events->fetch()) {
        echo "<li>ID: {$event['id']} - {$event['annee']} - {$event['titre_fr']}</li>";
    }
    echo "</ul>";
    
    echo "<p style='color:blue; margin-top:20px;'>✅ Maintenant, rechargez votre page !</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>