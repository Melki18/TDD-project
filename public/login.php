<?php

// Sécurisation des cookies de session
ini_set('session.cookie_lifetime', 3600); // Durée de vie du cookie de session (1 heure)
ini_set('session.cookie_secure', '1'); // Active la sécurisation des cookies (HTTPS)
ini_set('session.cookie_httponly', '1'); // Empêche l'accès JavaScript aux cookies
ini_set('session.use_only_cookies', '1'); // Utilisation uniquement des cookies pour les sessions

// Démarrer la session
session_start();



// Générer un token CSRF si nécessaire
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF sécurisé
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>

    <h2>Page de Connexion</h2>

    <?php
    // Afficher un message d'erreur si la connexion échoue
    if (isset($_GET['error'])) {
        echo "<p style='color:red;'>Identifiants incorrects. Essayez encore.</p>";
    }
    ?>

    <form action="login_process.php" method="POST">
        <div>
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
        </div>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <button type="submit">Se connecter</button>
    </form>

</body>

</html>