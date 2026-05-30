<?php
// Inclure votre configuration
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getPDO();
    
    // Afficher les événements avant suppression
    echo "<h3>📋 Événements avant correction :</h3>";
    $stmt = $pdo->query("SELECT id, annee, titre_fr FROM historique ORDER BY annee");
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Année</th><th>Titre</th></tr>";
    while ($row = $stmt->fetch()) {
        $style = ($row['id'] == 5) ? "style='background:#ffcccc'" : "";
        echo "<tr $style><td>{$row['id']}</td><td>{$row['annee']}</td><td>{$row['titre_fr']}</td></tr>";
    }
    echo "</table>";
    
    // Supprimer le doublon (id=5)
    echo "<h3>🗑️ Suppression du doublon...</h3>";
    $stmt = $pdo->prepare("DELETE FROM historique WHERE id = 5");
    $stmt->execute();
    
    echo "<p style='color:green; font-weight:bold;'>✅ Doublon supprimé avec succès !</p>";
    
    // Afficher après suppression
    echo "<h3>📋 Événements après correction :</h3>";
    $stmt = $pdo->query("SELECT id, annee, titre_fr FROM historique ORDER BY annee");
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Année</th><th>Titre</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['annee']}</td><td>{$row['titre_fr']}</td></tr>";
    }
    echo "</table>";
    
    echo "<br><a href='javascript:history.back()'>← Retour au site</a>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>