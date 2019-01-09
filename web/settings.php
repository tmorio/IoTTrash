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
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MyBox Cloud - BoxMap</title>
		<link rel="stylesheet" type="text/css" href="css/materialize.min.css">
		<link rel="stylesheet" type="text/css" href="css/style.css?">
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
	
	<!-- 表示画面 （Google Mapみたいに2画面分割で左にリスト、右にマップ?)-->
	<div class="settingBoard">
	
		<!-- 設定分類一覧表示 -->
		<div class="collection with-header settingList">
			<div class="collection-header center-align"><a class="waves-effect waves-light btn" href="./dashboard.php">
				<i class="material-icons left">keyboard_arrow_left</i>ホームに戻る</a></div>
			<div class="collection-header"><h5>サービス設定</h5></div>
			<a href="#" class="collection-item blue-grey-text text-darken-4"><i class="material-icons left">account_circle</i>アカウント設定</a>
			<a href="#" class="collection-item blue-grey-text text-darken-4"><i class="material-icons left">email</i>通知</a>
			<a href="#" class="collection-item blue-grey-text text-darken-4"><i class="material-icons left">local_shipping</i>回収サービス</a>
			<a href="#" class="collection-item blue-grey-text text-darken-4"><i class="material-icons left">group</i>グループ・権限</a>
			<a href="#" class="collection-item blue-grey-text text-darken-4"><i class="material-icons left">apps</i>API</a>
		</div>
		
		<!-- 設定表示 -->
		<div id="settingInfo">
		</div>
	</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
	</body>
</html>
