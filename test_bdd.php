<?php
$host = 'xu7m6l.myd.infomaniak.com';
$db   = 'xu7m6l_db';
$user = 'xu7m6l_famako';
$pass = 'F@makoFamak0';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    echo "✅ Connexion réussie !";
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>