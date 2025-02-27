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

// Récupérer les rendez-vous déjà pris pour éviter les conflits de créneaux
$stmt = $conn->prepare("SELECT * FROM appointments WHERE user_id = ?");
$stmt->execute([$user_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérification de la disponibilité d'un créneau horaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur de validation CSRF.");
    }else if (isset($_POST['cancel_appointment'])) {
        // Annuler le rendez-vous
        $appointment_id = $_POST['appointment_id'];
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$appointment_id, $user_id])) {
            $success_message = "Le rendez-vous a été annulé avec succès.";
            header("Location: appointments.php"); // Rafraîchir la page après annulation
            exit;
        } else {
            $error_message = "Une erreur est survenue lors de l'annulation du rendez-vous.";
        }
    } else {


        $appointment_date = $_POST['appointment_date'];

        // Vérifier si l'heure est un multiple de 30 minutes
        $date_time = new DateTime($appointment_date);
        $minute = $date_time->format('i');
        

        if ($minute % 30 !== 0) {
            $error_message = "Veuillez choisir un créneau horaire qui est un multiple de 30 minutes.";
        } else {
            // Vérifier si le créneau est déjà pris
            $stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_date = ?");
            $stmt->execute([$appointment_date]);

            if ($stmt->rowCount() > 0) {
                $error_message = "Ce créneau horaire est déjà pris. Veuillez en choisir un autre.";
            } else {
                // Enregistrer le rendez-vous
                $stmt = $conn->prepare("INSERT INTO appointments (user_id, appointment_date) VALUES (?, ?)");
                if ($stmt->execute([$user_id, $appointment_date])) {
                    $success_message = "Votre rendez-vous a été pris avec succès.";
                    header("Location: appointments.php");
                } else {
                    $error_message = "Une erreur est survenue lors de l'enregistrement de votre rendez-vous.";
                }
            }
        }
    }
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prendre un Rendez-vous</title>
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
                    <li class="nav-item"><a class="nav-link active" href="appointments.php">Mes Rendez-vous</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="register.php">Inscription</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2>Prendre un Rendez-vous</h2>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="appointment_date" class="form-label">Date et heure du rendez-vous</label>
            <input type="datetime-local" class="form-control" name="appointment_date" id="appointment_date" step="1800" required>
        </div>
        <button type="submit" class="btn btn-primary">Prendre Rendez-vous</button>
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    </form>

    <h3 class="mt-5">Mes Rendez-vous</h3>
    <ul class="list-group">
        <?php if (empty($appointments)): ?>
            <li class="list-group-item">Vous n'avez aucun rendez-vous.</li>
        <?php else: ?>
            <?php foreach ($appointments as $appointment): ?>
                <li class="list-group-item d-flex justify-content-between">
                    <?php echo date('d/m/Y H:i', strtotime($appointment['appointment_date'])); ?>
                    <form method="POST" action="appointments.php" class="d-inline">
                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                        <button type="submit" name="cancel_appointment" class="btn btn-danger btn-sm">Annuler</button>
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    </form>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

</body>
</html>
