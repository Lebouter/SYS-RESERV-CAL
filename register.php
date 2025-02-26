<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'db.php';

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $birth_date = $_POST['birth_date'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $verification_code = md5(uniqid(rand(), true));

    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        echo "Erreur : cet email est déjà utilisé. Veuillez en choisir un autre.";
        exit;
    }


    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, birth_date, address, phone, email, password, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
    
    if ($stmt->execute([$first_name, $last_name, $birth_date, $address, $phone, $email, $password])) {
        $user_id = $conn->lastInsertId();
        $stmt = $conn->prepare("INSERT INTO email_verification (user_id, verification_code) VALUES (?, ?)");
        $stmt->execute([$user_id, $verification_code]);

        $verification_link = "http://localhost/SYS-RESERV-CAL/verify.php?code=" . $verification_code;
        $subject = "Vérification de votre email";
        $message = "Cliquez sur ce lien pour vérifier votre compte : " . $verification_link;
        $headers = "From: no-reply@votre-site.com";

        if (mail($email, $subject, $message, $headers)) {
            echo "Email envoyé avec succès !";
        } else {
            echo "Échec de l'envoi de l'email.";
        }
    } else {
        echo "Erreur lors de l'inscription.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription</title>
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
    <h2 class="text-center">Inscription</h2>
    <form method="POST" action="register.php">
        <div class="mb-3">
            <label class="form-label">Prénom</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="last_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Date de naissance</label>
            <input type="date" name="birth_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Adresse</label>
            <input type="text" name="address" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Téléphone</label>
            <input type="text" name="phone" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">S'inscrire</button>
    </form>
</div>
</body>
</html>
