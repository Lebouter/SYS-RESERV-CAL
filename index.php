<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accueil - Réservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Système de Réservation</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">Mes Rendez-vous</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                    <li class="nav-item"><a class="nav-link" href="modif.php">Modifier le compte</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="register.php">Inscription</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="text-center">Bienvenue sur le Système de Réservation</h1>
    <p class="text-center">Prenez facilement vos rendez-vous en ligne.</p>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="text-center">
            <a href="appointments.php" class="btn btn-primary">Voir mes Rendez-vous</a>
            <a href="logout.php" class="btn btn-danger">Déconnexion</a>
        </div>
    <?php else: ?>
        <div class="text-center">
            <a href="register.php" class="btn btn-success">Inscription</a>
            <a href="login.php" class="btn btn-primary">Connexion</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
