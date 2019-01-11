<?php

session_start();

if(empty($_SESSION['userName'])){
        header("Location: login.php");
}

if(strlen($_POST['DeviceID']) != 6){
	header("Location: editDevice.php?Error=1");
}else{

	require_once('./myid.php');

	$strcode = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8mb4'");
	try {
                	$dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_ID, DB_PASS, $strcode);
                	$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        	} catch (PDOException $e) {
                	echo $e->getMessage();
                	exit;
	}

	$query = "UPDATE StatusData SET DeviceID = :newId, NickName = :newName WHERE DeviceID = :deviceid AND Owner = :owner";

	$stmt = $dbh->prepare($query);
	$stmt->bindParam(':newId', $_POST['DeviceID'], PDO::PARAM_STR);
	$stmt->bindParam(':newName', $_POST['NickName'], PDO::PARAM_STR);
	$stmt->bindParam(':deviceid', $_GET['Device'], PDO::PARAM_STR);
	$stmt->bindParam(':owner', $_SESSION['userNo'], PDO::PARAM_INT);
	$stmt->execute();

	header("Location: editDevice.php");
}
