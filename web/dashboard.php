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
						<!-- 設定,ログアウトボタン -->
                                                <li><a class="waves-effect waves-light btn" href="./settings.php"><i class="material-icons left">build</i>設定</a></li>
						<li><a class="waves-effect waves-light btn" href="./logout.php"><i class="material-icons left">vpn_key</i>ログアウト</a></li>
					</ul>
				</div>
			</nav>
		</div>
		<!-- メニュー -->
		<div class="dashDisplay">
		<?php
			if($_SESSION['userService'] == 0){
				echo '
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
							<a href="getMenu.php">
								<div class="menu-content blue-grey lighten-5 hoverable center-align z-depth-1">
									<i class="material-icons center large">local_shipping</i>
									<h6>回収管理</h6>
								</div>
							</a>
						</div>
						<!-- 設定 -->
                                                <div class="boxAdmin col s12 m4 menu-card">
                                                        <a href="boxtool.php">
                                                                <div class="menu-content blue-grey lighten-5 hoverable center-align z-depth-1">
                                                                        <i class="material-icons center large">trending_up</i>
                                                                        <h6>分析・管理</h6>
                                                                </div>
                                                        </a>
                                                </div>
					</div>
				</div>
			';
			}else{
			echo '
                                <div class="container">
                                        <div class="dashboardTitle">MyBox Cloud&nbsp;へようこそ</div>
                                        <div class="boardMenu row center-align">
                                                <!-- マップ表示 -->
                                                <div class="trashMap col s12 m4 menu-card">
                                                        <a href="boxmap.php">
                                                                <div class="menu-content blue-grey lighten-5 hoverable center-align z-depth-1">
                                                                        <i class="material-icons center large">local_shipping</i>
                                                                        <h6>回収作業</h6>
                                                                </div>
                                                        </a>
                                                </div>
                                                <!-- 依頼一覧 -->
                                                <div class="trashMap col s12 m4 menu-card">
                                                        <a href="missions.php">
                                                                <div class="menu-content blue-grey lighten-5 hoverable center-align z-depth-1">
                                                                        <i class="material-icons center large">record_voice_over</i>
                                                                        <h6>依頼確認</h6>
                                                                </div>
                                                        </a>
                                                </div>
                                                <!-- 設定 -->
                                                <div class="boxAdmin col s12 m4 menu-card">
                                                        <a href="settings.php">
                                                                <div class="menu-content blue-grey lighten-5 hoverable center-align z-depth-1">
                                                                        <i class="material-icons center large">build</i>
                                                                        <h6>設定</h6>
                                                                </div>
                                                        </a>
                                                </div>
					</div>
				</div>
			';
			}
		?>
		</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
	</body>
</html>

