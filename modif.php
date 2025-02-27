<?php
session_start();


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require 'db.php';
require 'vendor/autoload.php'; // Charger PHPMailer
require 'functions.php';

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
    
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $birth_date = $_POST['birth_date'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    

    // Vérification si l'email a changé
    $email_changed = $user['email'] != $email;

    if ($email != $user['email']) {
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->execute([$email, $user_id]);
        
        if ($check_email->fetch()) {
            echo "Cet email est déjà utilisé par un autre utilisateur. Veuillez en choisir un autre.";
            header("refresh:2;url=modif.php");
            exit();
        }
    }

    if ($password) {
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, birth_date = ?, address = ?, phone = ?, email = ?, password = ? WHERE id = ?");
        $success = $stmt->execute([$first_name, $last_name, $birth_date, $address, $phone, $email, $password, $user_id]);
    } else {
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, birth_date = ?, address = ?, phone = ?, email = ? WHERE id = ?");
        $success = $stmt->execute([$first_name, $last_name, $birth_date, $address, $phone, $email, $user_id]);
    }

    if ($success) {
        echo "Mise à jour réussie!";

        // Si l'email a changé, envoyer un email de vérification
        if ($email_changed) {
            // Générer un code de vérification unique
            $verification_code = md5(uniqid(rand(), true));

            // Insérer le code de vérification dans la base de données
            $stmt = $conn->prepare("INSERT INTO email_verification (user_id, verification_code) VALUES (?, ?)");
            $stmt->execute([$user_id, $verification_code]);

            // Créer un lien de vérification
            $verification_link = "http://localhost/SYS-RESERV-CAL/verify.php?code=" . $verification_code;

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
                $mail->Body = "Bonjour,<br><br>Nous avons détecté un changement d'email sur votre compte.<br>
                               Veuillez cliquer sur le lien ci-dessous pour vérifier votre nouvelle adresse email :<br>
                               <a href='$verification_link'>$verification_link</a><br><br>
                               Cordialement, <br> L'équipe du site.";
                $mail->AltBody = "Nous avons détecté un changement d'email. Veuillez vérifier votre email en cliquant sur ce lien : $verification_link";

                // Envoi de l'email
                if ($mail->send()) {
                    echo "Un email de vérification a été envoyé à votre nouvelle adresse.";
                } else {
                    echo "Erreur lors de l'envoi de l'email de vérification.";
                }
            } catch (Exception $e) {
                echo "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}";
            }
        }
    } else {
        echo "Erreur lors de la mise à jour.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modification du Compte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Système de Réservation</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="profil.php">Mon Profil</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">Mes Rendez-vous</a></li>
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
    <h2 class="text-center">Modifier mes informations</h2>
    <form method="POST" action="modif.php">
        <div class="mb-3">
            <label class="form-label">Prénom</label>
            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Date de naissance</label>
            <input type="date" name="birth_date" class="form-control" value="<?php echo htmlspecialchars($user['birth_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Adresse</label>
            <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($user['address']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Téléphone</label>
            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" name="password" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    </form>
</div>
</body>
</html>
