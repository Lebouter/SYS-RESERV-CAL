<?php

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php';
require 'vendor/autoload.php';
require 'functions.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur de validation CSRF.");
    }

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

        // Création du lien de vérification
        $verification_link = "https://sys-reserv-cal.alwaysdata.net/verify.php?code=" . $verification_code;

        // Envoi de l'email avec PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Outlook : smtp.office365.com
            $mail->SMTPAuth = true;
            $mail->Username = 'nicolas.blanchard.nicolas@gmail.com'; // Ton email
            $mail->Password = 'pepl najs jkpe rwzr'; // Mot de passe ou App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Expéditeur et destinataire
            $mail->setFrom('ton_email@gmail.com', 'Verification mail');
            $mail->addAddress($email);

            // Contenu de l'email
            $mail->isHTML(true);
            $mail->Subject = 'Verification de votre email';
            $mail->Body = "Bonjour,<br><br>Merci de vous être inscrit !<br>
                           Veuillez cliquer sur le lien ci-dessous pour vérifier votre adresse email :<br>
                           <a href='$verification_link'>$verification_link</a><br><br>
                           Cordialement, <br> L'équipe du site.";
            $mail->AltBody = "Merci de vous être inscrit ! Veuillez vérifier votre email en cliquant sur ce lien : $verification_link";

            // Envoi de l'email
            if ($mail->send()) {
                echo "Un email de vérification a été envoyé à votre adresse.";
            } else {
                echo "Erreur lors de l'envoi de l'email.";
            }
        } catch (Exception $e) {
            echo "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}";
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
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="profil.php">Mon Profil</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">Mes Rendez-vous</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link active" href="register.php">Inscription</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
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
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    </form>
</div>
</body>
</html>
