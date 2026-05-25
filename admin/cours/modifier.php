<?php
// admin/cours/modifier.php — inclut ajouter.php avec $editId
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$editId) { header('Location: index.php'); exit; }
include __DIR__ . '/ajouter.php';
