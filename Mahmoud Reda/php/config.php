<?php
$con = mysqli_connect('localhost', 'root', '', 'xlsx');

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
