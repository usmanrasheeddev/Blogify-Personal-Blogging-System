<?php
$conn = mysqli_connect("sql200.infinityfree.com", "if0_41084179", "00hm647j", "if0_41084179_simple_blog");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
