<?php
// Démarrer la session
session_start();

// Inclure la connexion à la base de données
require_once '../includes/db.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier la validité du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur CSRF. Veuillez réessayer.");
    }

    // Assainir les données d'entrée pour éviter les attaques XSS
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars(trim($_POST['password']), ENT_QUOTES, 'UTF-8');

    // Vérifier si l'utilisateur existe dans la base de données
    try {
        $query = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE username = :username");
        $query->bindParam(':username', $username);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // L'utilisateur est authentifié, créer une session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];  // On stocke également le rôle de l'utilisateur

            // Régénérer l'ID de session pour prévenir les attaques de fixation de session
            session_regenerate_id(true);

            // Rediriger en fonction du rôle
            if ($user['role'] === 'admin') {
                // Rediriger l'administrateur vers le tableau de bord des administrateurs
                header("Location: admin_dashboard.php");
            } else {
                // Rediriger l'utilisateur simple vers son tableau de bord
                header("Location: http://localhost/authentification/public/index.php");
            }
            exit();
        } else {
            // Si les identifiants sont incorrects
            header("Location: login.php?error=1");
            exit();
        }
    } catch (PDOException $e) {
        // Gérer les erreurs de base de données sans afficher de détails sensibles
        die("Erreur de base de données. Veuillez réessayer plus tard.");
    }
}