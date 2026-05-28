<?php
$host = 'xu7m6f.myd.infomaniak.com';
$db   = 'xu7m6f_db';
$user = 'xu7m6f_famako';
$pass = 'F@makoFamak0';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents(__DIR__ . '/sql/famako_final.sql');
    $pdo->exec($sql);
    
    echo "✅ Import réussi ! Tables créées.";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>
