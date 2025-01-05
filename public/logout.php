<?php
// Démarrer la session
session_start();

// Supprimer toutes les variables de session
$_SESSION = array();

// Si vous utilisez des cookies de session, les supprimer également
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header("Location: login.php");
exit();