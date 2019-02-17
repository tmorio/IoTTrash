<?php
session_start();

if(empty($_SESSION['userName'])){
	header("Location: login.php");
}

require_once('./myid.php');
require_once('./siteInfo.php');

$strcode = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8mb4'");
try {
		$dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_ID, DB_PASS, $strcode);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	} catch (PDOException $e) {
		echo $e->getMessage();
		exit;
}

$doID = $_GET['id'];

$Intime = date('Y-m-d H:i:s', time());

$query = "SELECT * FROM OrderInfo WHERE (Owner = :UserID OR GroupID = :GroupsID) AND DeviceID = :orderNo";
$stmt = $dbh->prepare($query);
$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
$stmt->bindParam(':GroupsID', $_SESSION['userGroup'], PDO::PARAM_INT);
$stmt->bindParam(':orderNo', $_SESSION['PostData'][$doID]['DeviceID'], PDO::PARAM_STR);
$stmt->execute();
$data = $stmt->fetch();
if(empty($data['DeviceID'])){
	echo '権限がありません。';
	exit(0);
}

$query = "UPDATE StatusData SET DevInfo = 1, OrderStatus = 0, WarSM = 0, Dis = NULL, LastReset = :nowTime WHERE DeviceID = :orderNo";
$stmt = $dbh->prepare($query);
$stmt->bindParam(':nowTime', $Intime, PDO::PARAM_STR);
$stmt->bindParam(':orderNo', $_SESSION['PostData'][$doID]['DeviceID'], PDO::PARAM_STR);
$stmt->execute();

$query = "DELETE FROM OrderInfo WHERE DeviceID = :orderNo";
$stmt = $dbh->prepare($query);
$stmt->bindParam(':orderNo', $_SESSION['PostData'][$doID]['DeviceID'], PDO::PARAM_STR);
$stmt->execute();

unset($_SESSION['PostData'][$doID]);
$_SESSION['PostData'] = array_values($_SESSION['PostData']);

header("Location: routePreCalc.php?ReAC=1");
?>
