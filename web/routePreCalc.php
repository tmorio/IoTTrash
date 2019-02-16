<?php
session_start();

if(empty($_SESSION['userName'])){
	header("Location: login.php");
}

require_once('./myid.php');
?>
<script>
	function getPosition() {
		if (navigator.geolocation) {
			alert("端末の位置情報サービスを利用して出発地点を取得します。");
		} else {
			alert("この端末では位置情報の取得ができません。依頼一覧に戻ります。");
				document.location.href = "getMenu.php";
		}

		navigator.geolocation.getCurrentPosition(
			function(position) {
				document.location.href = "?GETOK=1&lat=" + position.coords.latitude + "&lng=" + position.coords.longitude;
			},
			function(error) {
				switch(error.code) {
					case 1:
						alert("位置情報の利用が許可されていません。\n権限をご確認下さい。");
						break;
					case 2:
						alert("現在位置が取得できませんでした。\n時間を空けてから再度お試し下さい。");
						break;
					case 3:
						alert("タイムアウトしました。\n時間を空けてから再度お試し下さい。");
						break;
					default:
						alert("原因不明エラーが発生しました。(Error Code:"+error.code+")");
						break;
 				}
			}
		);
	}
</script>

<?php
echo '現在地を取得中です...しばらくお待ちください。<br><br>';

if($_GET['GETOK'] != 1){
	echo "<script>getPosition();</script>";
}

$strcode = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8mb4'");
try {
		$dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_ID, DB_PASS, $strcode);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	} catch (PDOException $e) {
		echo $e->getMessage();
		exit;
}


$PostData = [];

foreach($_POST['Boxes'] as $DevID){
	$query = "SELECT * FROM OrderInfo WHERE Owner = :UserID OR GroupID = :GroupsID AND DeviceID = :orderNo";
	$stmt = $dbh->prepare($query);
	$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
	$stmt->bindParam(':GroupsID', $_SESSION['userGroup'], PDO::PARAM_INT);
	$stmt->bindParam(':orderNo', $DevID, PDO::PARAM_STR);
	$stmt->execute();
	$data = $stmt->fetch();
	$InputData = array('Name'=>$data['NickName'], 'DeviceID'=>$data['DeviceID'], 'Lat'=>$data['Lat'], 'Lng'=>$data['Lng']);
	$PostData[] = $InputData;
	if(empty($data['DeviceID'])){
		echo '権限がありません。';
		exit(0);
	}
}

if($_GET['GETOK'] != 1){
	$_SESSION['Boxes'] = $_POST['Boxes'];
	$_SESSION['PostData'] = $PostData;
}

//DEBUG
echo "----------THIS IS DEBUG (3 SEC)----------" . "<br><br>";
echo "POSTED DATA: ";
print_r($_SESSION['Boxes']);
echo "<br>";
echo "YOUR POSITION (START/END POINT):";

if(!empty($_GET['lat'])){
	echo $_GET['lat'] . ", " . $_GET['lng'];
}else{
	echo "GETTING... PLEASE WAIT...";
}
echo "<br>";
echo "ARRAY DATA:";
print_r($_SESSION['PostData']);

if(!empty($_GET['lat'])){
        echo "<br><br>SUCCESS GET POSITION!";
}

if($_GET['GETOK'] != 0){
        $pushTo = "Location: route.php?lat=" . $_GET['lat'] . "&lng=" . $_GET['lng'];
        //unset($_SESSION['Boxes']);
        //unset($_SESSION['PostData']);
	sleep(3);
        header($pushTo);
}
?>
