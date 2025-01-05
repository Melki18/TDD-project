<?php
// Sécurisation des cookies de session (doit être exécuté avant session_start)
ini_set('session.cookie_lifetime', 3600); // Durée de vie du cookie de session (1 heure)
ini_set('session.cookie_secure', '1'); // Active la sécurisation des cookies (HTTPS)
ini_set('session.cookie_httponly', '1'); // Empêche l'accès JavaScript aux cookies
ini_set('session.use_only_cookies', '1'); // Utilisation uniquement des cookies pour les sessions

session_start(); // Démarre la session après la configuration des ini
require_once '../includes/db.php'; // Connexion à la base de données

// Configuration du timeout de session (30 minutes d'inactivité)
$timeout = 30 * 60; // 30 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    // Si le temps d'inactivité dépasse le timeout, on déconnecte l'utilisateur
    session_unset();
    session_destroy();
    header("Location: login.php?error=timeout");
    exit();
}
$_SESSION['last_activity'] = time(); // Met à jour le timestamp de l'activité

// Ajout des en-têtes de sécurité pour protéger contre XSS
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Content-Security-Policy: default-src 'self'; script-src 'self';");

// Vérification du token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: register.php?error=csrf");
    exit();
}

// Suppression du token CSRF pour éviter sa réutilisation
unset($_SESSION['csrf_token']);

// Récupération des données utilisateur avec protection XSS
$username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
$password = htmlspecialchars(trim($_POST['password']), ENT_QUOTES, 'UTF-8');
$confirm_password = htmlspecialchars(trim($_POST['confirm_password']), ENT_QUOTES, 'UTF-8');

// Validation des entrées
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php?error=invalid_email");
    exit();
}

if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    header("Location: register.php?error=invalid_username");
    exit();
}

if (strlen($password) < 8) {
    header("Location: register.php?error=password_too_short");
    exit();
}

if ($password !== $confirm_password) {
    header("Location: register.php?error=password_mismatch");
    exit();
}

// Hashage sécurisé du mot de passe
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Vérification de l'existence de l'email ou du nom d'utilisateur
$query = "SELECT * FROM users WHERE email = :email OR username = :username";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    header("Location: register.php?error=user_exists");
    exit();
}

// Inscription de l'utilisateur
$query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

if ($stmt->execute()) {
    header("Location: login.php?success=1");
    exit();
} else {
    header("Location: register.php?error=db_error");
    exit();
}