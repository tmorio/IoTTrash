<?php
session_start();

if(empty($_SESSION['userName'])){
	header("Location: login.php");
	exit(0);
}

if($_SESSION['userGroup'] == 1){
        header("Location: dashboard.php");
	exit(0);
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
		<title>MyBox Cloud - BoxAdmin</title>
		<link rel="stylesheet" type="text/css" href="css/materialize.min.css">
		<link rel="stylesheet" type="text/css" href="css/style.css?">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
		<script type="text/javascript" src="js/materialize.min.js"></script>
		<script type="text/javascript" src="js/footerFixed.js"></script>
		<!-- <link rel="stylesheet" type="text/css" href="style.css"> -->
	</head>
	<body>
	<?php require_once('./header.php'); ?>
	<div class="dashDisplay">
		<div class="container">
                                <div class="dashboardTitle">
					デバイス管理
					<br>
					<a class="waves-effect waves-light btn" href="./dashboard.php">
						<i class="material-icons left">keyboard_arrow_left</i>ホームに戻る
					</a>
				</div>

                                <div class="boardMenu row center-align">
                                        <!-- 一覧・分析 -->
                                        <div class="menuIcon col s12 m4 menu-card">
                                                <a href="deviceList.php">
                                                        <div class="menu-content blue-grey lighten-5 hoverable center-align z-depth-1">
								<i class="material-icons center large">equalizer</i>
                                                                <h6>一覧・分析</h6>
                                                        </div>
                                                </a>
                                        </div>
                                        <!-- 追加 -->
                                        <div class="menuIcon col s12 m4 menu-card">
                                                <a href="addDevice.php">
                                                        <div class="menu-content blue-grey lighten-5 hoverable center-align z-depth-1">
                                                		<i class="material-icons center large">add</i>
                                                                <h6>デバイス追加</h6>
                                                        </div>
                                                </a>
                                        </div>
                                        <!-- 削除 -->
                                        <div class="menuIcon col s12 m4 menu-card">
                                                <a href="editDevice.php">
                                                        <div class="menu-content blue-grey lighten-5 hoverable center-align z-depth-1">
                                                		<i class="material-icons center large">edit</i>
                                                                <h6>編集・削除</h6>
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

