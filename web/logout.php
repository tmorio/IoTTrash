<?php
require_once('./myid.php');

session_start();
session_destroy();
header("Location: login.php");

?>
