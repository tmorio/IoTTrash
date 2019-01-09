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

$query = "SELECT * FROM StatusData WHERE Owner = :UserID";

$stmt = $dbh->prepare($query);
$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
$stmt->execute();

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
		<script type="text/javascript" src="js/materialize.min.js"></script>
		<script type="text/javascript" src="js/footerFixed.js"></script>
		<!-- <link rel="stylesheet" type="text/css" href="style.css"> -->
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
        	<a class="waves-effect waves-light btn" href="./boxtool.php">
        		<i class="material-icons left">keyboard_arrow_left</i>デバイス管理に戻る
        	</a>
		&emsp;&emsp;
                <a class="waves-effect waves-light btn" href="./addDevice.php">
                        <i class="material-icons left">add</i>デバイス追加
                </a>
                <a class="waves-effect waves-light btn" href="./deleteDevice.php">
			<i class="material-icons left">highlight_off</i>デバイス削除
		</a>
                <a class="waves-effect waves-light btn" href="#">
                        <i class="material-icons left">search</i>検索
                </a>

		<div class="listOutput">

		<?php
			foreach($stmt as $data){
				echo "デバイス名: " .  $data['NickName'] ;
				echo "&nbsp;(" .  $data['DeviceID'] . ")";

				if(empty($data['Time'])){
					echo "更新日時: 未取得";
				}else{
					echo "更新日時: " . $data['Time'];
				}
                                echo '&nbsp;<a class="waves-effect waves-light btn" href="#"><i class="material-icons left">timeline</i>グラフ表示</a>';
				echo '<hr size="1" color="#37474f" noshade>';
			}
		?>

		</div>

	</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
	</body>
</html>

