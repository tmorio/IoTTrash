<?php
session_start();

if(empty($_SESSION['userName'])){
	header("Location: login.php");
}

require_once('./myid.php');

$strcode = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8mb4'");
try {
		$dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_ID, DB_PASS, $strcode);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	} catch (PDOException $e) {
		echo $e->getMessage();
		exit;
}

$query = "SELECT * FROM StatusData WHERE Owner = :UserID AND DeviceID = :deviceid";

$stmt = $dbh->prepare($query);
$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
$stmt->bindParam(':deviceid', $_GET['DeviceID'], PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch();

if(empty($result['DeviceID'])){
        echo '権限がありません。';
        exit(0);
}

$query = "SELECT * FROM History WHERE DeviceID = :deviceid";

$stmt = $dbh->prepare($query);
$stmt->bindParam(':deviceid', $_GET['DeviceID'], PDO::PARAM_STR);
$stmt->execute();

$filenameData = $_GET['DeviceID'] . '('  . date("YmdHis") . ')';

$fileinput = "DateTime,BoxAvailable,Temperature,Humidity\r\n";
foreach($stmt as $data){
	$fileinput = $fileinput . $data['Time'] . "," . $data['Dis'] . "," . $data['Temp'] . "," . $data['Hum'] . "\r\n";
}

$fpath = './export/' . $filenameData . '.csv';
file_put_contents($fpath, $fileinput);

$fname = $_GET['DeviceID'] . '.csv';

header('Content-Type: application/force-download');
header('Content-Length: '.filesize($fpath));
header('Content-disposition: attachment; filename="'.$fname.'"');
readfile($fpath);
unlink($fpath);

?>
