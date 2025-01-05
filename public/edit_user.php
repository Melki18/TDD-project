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

// Vérifier si l'ID de l'utilisateur est passé en paramètre
if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$user_id = $_GET['id'];

// Récupérer les informations de l'utilisateur
try {
    $query = $pdo->prepare("SELECT id, username, email FROM users WHERE id = :id");
    $query->bindParam(':id', $user_id);
    $query->execute();
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: admin_dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];

    // Mettre à jour les informations de l'utilisateur
    try {
        $query = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
        $query->bindParam(':username', $new_username);
        $query->bindParam(':email', $new_email);
        $query->bindParam(':id', $user_id);
        $query->execute();

        // Définir un message de confirmation dans la session
        $_SESSION['message'] = "Les informations de l'utilisateur ont été mises à jour avec succès.";

        // Rediriger vers la gestion des utilisateurs
        header("Location: admin_dashboard.php");
        exit();
    } catch (PDOException $e) {
        die("Erreur de base de données : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'utilisateur</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Modifier l'utilisateur</h1>
    <form action="edit_user.php?id=<?= $user['id'] ?>" method="POST">
        <label for="username">Nom d'utilisateur:</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        
        <button type="submit">Mettre à jour</button>
    </form>
</body>
</html>