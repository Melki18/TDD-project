<?php
// Sécurisation des cookies de session
ini_set('session.cookie_lifetime', 3600); // Durée de vie du cookie de session (1 heure)
ini_set('session.cookie_secure', '1'); // Active la sécurisation des cookies (HTTPS)
ini_set('session.cookie_httponly', '1'); // Empêche l'accès JavaScript aux cookies
ini_set('session.use_only_cookies', '1'); // Utilisation uniquement des cookies pour les sessions

session_start();

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

// Ajouter des en-têtes de sécurité pour prévenir les attaques XSS
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Content-Security-Policy: default-src 'self'; script-src 'self';");

// Générer un token CSRF unique s'il n'existe pas déjà
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- Ton fichier CSS -->
</head>

<body>
    <h1>Inscription</h1>

    <!-- Gestion des messages d'erreur -->
    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;">
            <?php
            if ($_GET['error'] == 1) {
                echo "Nom d'utilisateur ou email déjà utilisé.";
            } elseif ($_GET['error'] == 2) {
                echo "Les mots de passe ne correspondent pas.";
            } elseif ($_GET['error'] == 'timeout') {
                echo "Votre session a expiré. Veuillez vous reconnecter.";
            }
            ?>
        </p>
    <?php endif; ?>

    <!-- Formulaire d'inscription -->
    <form action="register_process.php" method="POST">
        <!-- Champ caché pour le token CSRF -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="username">Nom d'utilisateur :</label>
        <input type="text" name="username" id="username" required>

        <label for="email">Email :</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Mot de passe :</label>
        <input type="password" name="password" id="password" required>

        <label for="confirm_password">Confirmer le mot de passe :</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit">S'inscrire</button>
    </form>
</body>

</html>