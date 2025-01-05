<?php
// Démarrer la session
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

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Inclure la connexion à la base de données
require_once '../includes/db.php';

// Récupérer tous les utilisateurs
try {
    $query = $pdo->prepare("SELECT id, username, email FROM users");
    $query->execute();
    $users = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    <h1>Tableau de bord - Gestion des utilisateurs</h1>

    <!-- Affichage du message de confirmation -->
    <?php if (isset($_SESSION['message'])): ?>
        <p style="color: green;"><?= htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php unset($_SESSION['message']); ?> <!-- Supprimer le message après l'affichage -->
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom d'utilisateur</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
                        <a href="delete_user.php?id=<?= htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>