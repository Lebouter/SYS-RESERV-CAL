<?php
$servername = "mysql-sys-reserv-cal.alwaysdata.net";
$username = "409823_nicolas";
$password = "Kocelo86";
$dbname = "sys-reserv-cal_sql";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connexion échouée: " . $e->getMessage());
}
?>
