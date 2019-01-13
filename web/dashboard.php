<?php
session_start();

if(empty($_SESSION['userName'])){
	header("Location: login.php");
}

require_once('./siteInfo.php');

unset($_SESSION['lat']);
unset($_SESSION['lng']);
unset($_SESSION['getStatus']);
unset($_SESSION['deviceID']);
unset($_SESSION['nickname']);

?>

<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MyBox Cloud - Dashboard</title>
		
		<!-- <meta name="viewport" content="width=device-with, initial-scale=1"> -->
		<link rel="stylesheet" type="text/css" href="css/materialize.min.css">
		<link rel="stylesheet" type="text/css" href="css/style.css?">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
		<script type="text/javascript" src="js/materialize.min.js"></script>
		<script type="text/javascript" src="js/footerFixed.js"></script>
		<!-- <link rel="stylesheet" type="text/css" href="style.css"> -->
	</head>

	<body class="grey lighten-5">
		<!-- ヘッダー -->
		<div class="serviceHeader navbar-fixed">
			<nav>
				<div class="nav-wrapper black-text">
					<!-- ロゴ -->
					<img class="logo-image" src="img/logo.png">
					
					<ul class="right">
						<!-- ユーザー名 -->
						<li>ようこそ、<?php print $_SESSION['userName']; ?>さん</li>
						<!-- ログアウトボタン -->
						<li><a class="waves-effect waves-light btn" href="./logout.php">ログアウト</a></li>
					</ul>
				</div>
			</nav>
		</div>
		<!-- メニュー -->
		<div class="dashDisplay">
			<div class="container">
				<div class="dashboardTitle">MyBox Cloud&nbsp;へようこそ</div>
				<div class="boardMenu row center-align">
					<!-- マップ表示 -->
					<div class="trashMap col s12 m4 menu-card">
						<a href="boxmap.php">
							<div class="menu-content blue-grey lighten-5 hoverable center-align z-depth-1">
								<i class="material-icons center large">delete_sweep</i>
								<h6>状態確認・回収</h6>
							</div>
						</a>
					</div>
					<!-- ゴミ箱管理 -->
					<div class="boxAdmin col s12 m4 menu-card">
						<a href="boxtool.php">
							<div class="menu-content blue-grey lighten-5 hoverable center-align z-depth-1">
								<i class="material-icons center large">trending_up</i>
								<h6>分析・管理</h6>
							</div>
						</a>
					</div>
					<!-- 設定 -->
					<div class="settingTool col s12 m4 menu-card">
						<a href="settings.php">
							<div class="menu-content blue-grey lighten-5 hoverable center-align z-depth-1">
								<i class="material-icons center large">build</i>
								<h6>サービス設定</h6>
							</div>
						</a>
					</div>

				</div>
			</div>
		</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
	</body>
</html>

