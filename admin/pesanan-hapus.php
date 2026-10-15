<?php
require "session.php";
require "../config/init.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $stmt = $con->prepare("DELETE FROM pesanan WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("Location: pesanan.php");
exit;
