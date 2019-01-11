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

if($_GET['del'] == 1){
        $query = "DELETE FROM StatusData WHERE Owner = :UserID AND DeviceID = :deviceid";

        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
        $stmt->bindParam(':deviceid', $_GET['Device'], PDO::PARAM_STR);
        $stmt->execute();
	header("Location: editDevice.php");
}else{
	$query = "SELECT * FROM StatusData WHERE Owner = :UserID AND DeviceID = :deviceid";

	$stmt = $dbh->prepare($query);
	$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
	$stmt->bindParam(':deviceid', $_GET['Device'], PDO::PARAM_STR);
	$stmt->execute();
}
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MyBox Cloud - BoxAdmin</title>
		<link rel="stylesheet" type="text/css" href="css/materialize.min.css">
		<link rel="stylesheet" type="text/css" href="css/style.css?Ver=2">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
		<!-- <script type="text/javascript" src="js/materialize.min.js"></script> --><!-- ローカルにあるjsだと動作不良? -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script><!-- 0.98.0のCDNからだと動作 -->
		<script type="text/javascript" src="js/footerFixed.js"></script>
		<script src="https://use.fontawesome.com/12725d4110.js"></script>
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
					<li><a class="waves-effect waves-light btn" href="./logout.php">ログアウト</a></li>
				</ul>
			</div>
		</nav>
	</div>
	<div class="deviceListBoard">
		<br><br>
                <div class="listOutput">
                <ul class="collapsible">
                <?php
			//ここに適用したい (jsについては<head>のコメント参照)

                        foreach($stmt as $data){
                                echo '<li><div class="collapsible-header">';
                                echo "デバイス名: " .  $data['NickName'] ;
                                echo "&nbsp;(" .  $data['DeviceID'] . ")<br>";

                                if(empty($data['Time'])){
                                        echo "更新日時: 未取得";
                                }else{
                                        echo "更新日時: " . $data['Time'];
                                }
				echo '</div>';
                                echo '</li>';
                        }
                ?>
                </ul>
                </div>
                <div class="deleteCheck">削除するとこのデバイスのデータ履歴も削除されます。<br>本当に削除しますか?</div><br><br>
		<div class="buttonH">
                	<a class="waves-effect waves-light btn-large listButton" href="editDevice.php"><i class="material-icons right">keyboard_return</i>編集・削除ページへ戻る</a><br><br><br>
                	<a class="waves-effect waves-light btn listButton" href="delete.php?Device=<?php echo $_GET['Device']; ?>&del=1"><i class="material-icons right">delete</i>削除する</a>
		</div>


	</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
	</body>
</html>
