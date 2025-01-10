<?php
// Database configuration
$host = 'localhost';
$dbname = 'ataax_game';
$username = 'ataax_fotis';
$password = '3x8*lrns0dr3@cvs';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>