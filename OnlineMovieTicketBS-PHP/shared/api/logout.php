<?php
session_start();
session_destroy();
// Fallback to public index if referer unknown
header('location: ../../index.php');
?>

