<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Inclure la connexion à la base de données
require_once '../includes/db.php';

// Vérifier si l'ID de l'utilisateur à supprimer est passé en paramètre
if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$user_id = $_GET['id'];

// Supprimer l'utilisateur
try {
    // Préparer la requête de suppression
    $query = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $query->bindParam(':id', $user_id);
    $query->execute();

    // Définir un message de succès dans la session
    $_SESSION['message'] = "L'utilisateur a été supprimé avec succès.";

    // Rediriger vers le tableau de bord
    header("Location: admin_dashboard.php");
    exit();
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>