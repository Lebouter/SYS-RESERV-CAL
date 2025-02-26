<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $birth_date = $_POST['birth_date'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, birth_date, address, phone, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$first_name, $last_name, $birth_date, $address, $phone, $email, $password])) {
        echo "Inscription réussie!";
    } else {
        echo "Erreur lors de l'inscription.";
    }
}
?>
