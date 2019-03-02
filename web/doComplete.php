[B<?php
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

//unset($_SESSION['PostData'][$doID]);
//$_SESSION['PostData'] = array_values($_SESSION['PostData']);

$query = "SELECT * FROM StatusData WHERE DeviceID = :deviceid";
$stmt = $dbh->prepare($query);
$stmt->bindParam(':deviceid', $_SESSION['PostData'][$doID]['DeviceID'], PDO::PARAM_STR);
$stmt->execute();
$getInfo = $stmt->fetchAll();

var_dump($getInfo);

foreach($getInfo as $setting){
	$DevName = $setting['NickName'];
        $DevID = $setting['DeviceID'];
        $UserIDInfo = $setting['Owner'];
        $query = "SELECT * FROM UserSetting WHERE UserID = :userid";
        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':userid', $UserIDInfo, PDO::PARAM_INT);
        $stmt->execute();
        $GSset = $stmt->fetch(PDO::FETCH_ASSOC);
	if($GSset['GetSendNotice'] == 1){
        	$query = "SELECT * FROM Users WHERE ID = :userid";
        	$stmt = $dbh->prepare($query);
        	$stmt->bindParam(':userid', $UserIDInfo, PDO::PARAM_INT);
        	$stmt->execute();
        	$UserSet = $stmt->fetch(PDO::FETCH_ASSOC);

        	$toMail = $UserSet['mailAddress'];
        	$returnMail = 'mybox@moritoworks.com';
        	$name = "MyBox Cloud";
        	$mail = 'mybox@moritoworks.com';
        	$subject = "回収完了のお知らせ";
		$mydomain = SERVER_DOMAIN;

$body = <<< EOM
以下のゴミ箱の回収が完了しました。

回収完了日時 : {$Intime}
デバイス名/ID : {$DevName} ({$DevID})

サービスへのログインは以下から行えます。
https://{$mydomain}/login.php

なお、このメールは送信専用のメールアドレスで送信しているため、返信頂いても対応することができません。
何卒ご了承ください。
------------------------------
MyBox Cloud

Developed by IoT oyama Team.
------------------------------

EOM;
         	mb_language('ja');
         	mb_internal_encoding('UTF-8');
         	$header = 'From: ' . mb_encode_mimeheader($name). ' <' . $mail. '>';
         	mb_send_mail($toMail, $subject, $body, $header, '-f'. $returnMail);
	}
}

unset($_SESSION['PostData'][$doID]);
$_SESSION['PostData'] = array_values($_SESSION['PostData']);

header("Location: routePreCalc.php?ReAC=1");
?>
