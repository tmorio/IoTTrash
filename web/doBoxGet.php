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

if($_GET['send'] == 1){
        foreach($_SESSION['GetData'] as $DevID){
                $query = "SELECT * FROM StatusData WHERE (Owner = :UserID OR GroupID = :GroupsID) AND DeviceID = :orderNo";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
                $stmt->bindParam(':GroupsID', $_SESSION['userGroup'], PDO::PARAM_INT);
                $stmt->bindParam(':orderNo', $DevID['DeviceID'], PDO::PARAM_STR);
                $stmt->execute();
                $data = $stmt->fetch();
                if($data['OrderStatus'] == 1){
			unset($_SESSION['GetData']);
                        echo 'このデバイスは既に依頼済みです。';
                        exit(0);
         	       }
        }

        foreach($_SESSION['GetData'] as $DevID){
                $query = "SELECT * FROM StatusData WHERE (Owner = :UserID OR GroupID = :GroupsID) AND DeviceID = :orderNo";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
                $stmt->bindParam(':GroupsID', $_SESSION['userGroup'], PDO::PARAM_INT);
                $stmt->bindParam(':orderNo', $DevID['DeviceID'], PDO::PARAM_STR);
                $stmt->execute();
                $data = $stmt->fetch();
                if(empty($data['DeviceID'])){
                        echo '権限がありません。';
                        exit(0);
                }
        }

	foreach($_SESSION['GetData'] as $DevID){
		$query = "INSERT INTO OrderInfo (Owner, DeviceID, DevName, GroupID, Lat, Lng) VALUES (:ownerID, :DeviceID, :DevName, :GroupID, :LatNo, :LngNo)";
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(':ownerID', $_SESSION['userNo'], PDO::PARAM_INT);
		$stmt->bindParam(':DeviceID', $DevID['DeviceID'], PDO::PARAM_STR);
		$stmt->bindParam(':DevName', $DevID['Name'], PDO::PARAM_STR);
		$stmt->bindParam(':GroupID', $_SESSION['userGroup'], PDO::PARAM_INT);
		$stmt->bindParam(':LatNo', $DevID['Lat'], PDO::PARAM_STR);
		$stmt->bindParam(':LngNo', $DevID['Lng'], PDO::PARAM_STR);
		$stmt->execute();

		$query = "UPDATE StatusData SET OrderStatus = 1 WHERE DeviceID = :orderNo";
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(':orderNo', $DevID['DeviceID'], PDO::PARAM_STR);
		$stmt->execute();
	}
	unset($_SESSION['GetData']);
	header("Location: boxmapR.php");
}else{
	$PostData = [];
	foreach($_POST['Devices'] as $DevID){
        	$query = "SELECT * FROM StatusData WHERE (Owner = :UserID OR GroupID = :GroupsID) AND DeviceID = :orderNo";
        	$stmt = $dbh->prepare($query);
        	$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
        	$stmt->bindParam(':GroupsID', $_SESSION['userGroup'], PDO::PARAM_INT);
        	$stmt->bindParam(':orderNo', $DevID, PDO::PARAM_STR);
		$stmt->execute();
        	$data = $stmt->fetch();
		$InputData = array('Name'=>$data['NickName'], 'DeviceID'=>$data['DeviceID'], 'Lat'=>$data['Latitude'], 'Lng'=>$data['Longitude']);
		$PostData[] = $InputData;
        	if(empty($data['DeviceID'])){
                	echo '権限がありません。';
                	exit(0);
        	}
	}
	$_SESSION['GetData'] = $PostData;
}
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MyBox Cloud - SendMission</title>
		<link rel="stylesheet" type="text/css" href="css/materialize.min.css">
		<link rel="stylesheet" type="text/css" href="css/style.css?Ver=2">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
		<script type="text/javascript" src="js/materialize.min.js"></script>
		<script type="text/javascript" src="js/footerFixed.js"></script>
	</head>
	<body>
	<!-- ヘッダー -->
	<div class="serviceHeader navbar-fixed">
		<nav>
			<div class="nav-wrapper black-text">
				<!-- ロゴ -->
				<a href="./dashboard.php"><img class="logo-image" src="img/logo.png"></a>
				<ul class="right">
					<!-- ユーザー名 -->
					<li>ようこそ、<?php print $_SESSION['userName']; ?>さん</li>
					<!-- ログアウトボタン -->
					<li><a class="waves-effect waves-light btn" href="./logout.php"><i class="material-icons left">vpn_key</i>ログアウト</a></li>
				</ul>
			</div>
		</nav>
	</div>
	<div class="deviceListBoard">
                <a class="waves-effect waves-light btn" href="./boxmapR.php">
                        <i class="material-icons left">keyboard_arrow_left</i>マップに戻る
                </a>
                <span class="listTitle">回収依頼確認</span>
		<br><br>
                <div class="listOutput">
                <ul class="collapsible">
                <?php
			 echo '<li><div class="collapsible-header">';
                                echo '現在開発なう by森尾<br>DEBUG(OUTPUT_DATA) : ';
				print_r($_SESSION['GetData']);
                                echo "<br>";

				echo '</div>';
        	                echo '</li>';
                ?>
                </ul>
                </div>
                <div class="deleteCheck">選択した端末の回収を依頼します。<br>よろしいですか?</div><br><br>
		<div class="buttonH">
                	<a class="waves-effect waves-light btn-large listButton" href="boxmapR.php"><i class="material-icons right">keyboard_return</i>マップに戻る</a><br><br>
			<a class="waves-effect waves-light btn listButton red" href="?send=1"><i class="material-icons right">send</i>依頼する</a>
		</div>


	</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
	</body>
</html>
