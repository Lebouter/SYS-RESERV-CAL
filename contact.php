<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

// Initialisation des variables
$name = $email = $subject = $message = "";
$nameErr = $emailErr = $subjectErr = $messageErr = $success = "";

// Traitement du formulaire lors de la soumission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validation CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur de validation CSRF.");
    }

    // Validation des champs
    if (empty($_POST["name"])) {
        $nameErr = "Le nom est requis";
    } else {
        $name = test_input($_POST["name"]);
        // Vérification que le nom ne contient que des lettres et des espaces
        if (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $name)) {
            $nameErr = "Seules les lettres et les espaces blancs sont autorisés";
        }
    }

    if (empty($_POST["email"])) {
        $emailErr = "L'email est requis";
    } else {
        $email = test_input($_POST["email"]);
        // Vérification de la validité de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Format d'email invalide";
        }
    }

    if (empty($_POST["subject"])) {
        $subjectErr = "Le sujet est requis";
    } else {
        $subject = test_input($_POST["subject"]);
    }

    if (empty($_POST["message"])) {
        $messageErr = "Le message est requis";
    } else {
        $message = test_input($_POST["message"]);
    }

    // Si pas d'erreurs, enregistrer dans la base de données
    if (empty($nameErr) && empty($emailErr) && empty($subjectErr) && empty($messageErr)) {
        try {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$name, $email, $subject, $message]);
            
            if ($result) {
                $success = "Votre message a été envoyé avec succès. Nous vous contacterons bientôt.";
                // Réinitialiser les champs du formulaire
                $name = $email = $subject = $message = "";
            } else {
                $error = "Une erreur est survenue lors de l'envoi de votre message.";
            }
        } catch (PDOException $e) {
            $error = "Erreur de base de données: " . $e->getMessage();
        }
    }
}

// Fonction pour nettoyer les données d'entrée
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Générer un token CSRF si non existant
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generate_csrf_token();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Contact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
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
                    <li class="nav-item"><a class="nav-link" href="register.php">Inscription</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2>Contactez-nous</h2>
    <p>Utilisez ce formulaire pour toute demande de renseignements. Nous vous répondrons dans les plus brefs délais.</p>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Nom complet</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>">
            <span class="error"><?php echo $nameErr; ?></span>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>">
            <span class="error"><?php echo $emailErr; ?></span>
        </div>
        
        <div class="mb-3">
            <label for="subject" class="form-label">Sujet</label>
            <input type="text" class="form-control" id="subject" name="subject" value="<?php echo $subject; ?>">
            <span class="error"><?php echo $subjectErr; ?></span>
        </div>
        
        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea class="form-control" id="message" name="message" rows="5"><?php echo $message; ?></textarea>
            <span class="error"><?php echo $messageErr; ?></span>
        </div>
        
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <button type="submit" class="btn btn-primary">Envoyer le message</button>
    </form>
</div>

<footer class="bg-dark text-white mt-5 py-3">
    <div class="container text-center">
        <p>&copy; <?php echo date('Y'); ?> Système de Réservation. Tous droits réservés.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>