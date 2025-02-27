<?php
session_start();
require 'db.php';
require 'functions.php';

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("Erreur de validation CSRF.");
        }
    

    // Suppression du compte
    if (isset($_POST['delete_account'])) {
        // Suppression des données de l'utilisateur
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        // Suppression des rendez-vous associés
        $stmt = $conn->prepare("DELETE FROM appointments WHERE user_id = ?");
        $stmt->execute([$user_id]);

        session_destroy();
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Utilisateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Système de Réservation</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>                    
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="register.php">Inscription</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2>Mon Profil</h2>

    <!-- Informations du compte -->
    <div class="mb-3">
        <strong>Nom:</strong> <?php echo htmlspecialchars($user['first_name']); ?><br>
        <strong>Prénom:</strong> <?php echo htmlspecialchars($user['last_name']); ?><br>
        <strong>Date de naissance:</strong> <?php echo htmlspecialchars($user['birth_date']); ?><br>
        <strong>Adresse:</strong> <?php echo htmlspecialchars($user['address']); ?><br>
        <strong>Numéro de téléphone:</strong> <?php echo htmlspecialchars($user['phone']); ?><br>
        <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?><br>
    </div>

    <!-- Boutons pour modifier le profil, voir les réservations et supprimer le compte -->
    <div class="mb-3">
        <a href="modif.php" class="btn btn-warning">Modifier mon profil</a>
        <a href="appointments.php" class="btn btn-primary">Mes Rendez-vous</a>
    </div>

    <!-- Formulaire pour supprimer le compte -->
    <form method="POST" action="profil.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.');">
        <button type="submit" name="delete_account" class="btn btn-danger">Supprimer mon compte</button>
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    </form>
</div>

</body>
</html>
