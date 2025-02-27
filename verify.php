<?php
require 'db.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $stmt = $conn->prepare("SELECT user_id FROM email_verification WHERE verification_code = ?");
    $stmt->execute([$code]);
    $user = $stmt->fetch();

    if ($user) {
        $user_id = $user['user_id'];
        $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
        $stmt->execute([$user_id]);

        $stmt = $conn->prepare("DELETE FROM email_verification WHERE verification_code = ?");
        $stmt->execute([$code]);

        echo "Votre email a été vérifié avec succès ! Vous allez être rediriger pour vous connecter";
        header("refresh:2;url=login.php");
        exit();

    } else {
        echo "Lien de vérification invalide.";
    }
}
?>
