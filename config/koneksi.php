<?php
// Sesuaikan dengan pengaturan MySQL
$con = mysqli_connect("localhost", "root", "", "winsbeads");

if (mysqli_connect_errno()) {
    error_log("MySQL error: " . mysqli_connect_error());
    http_response_code(500);
    exit("Tidak bisa terhubung ke database.");
}
mysqli_set_charset($con, "utf8mb4");
