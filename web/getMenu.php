<?php
session_start();

if(empty($_SESSION['userName'])){
	header("Location: login.php");
}

if($_SESSION['userGroup'] == 1){
        header("Location: dashboard.php");
        exit(0);
}

if($_SESSION['userService'] == 1){
        header("Location: missions.php");
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

if(!empty($_SESSION['userGroup'])){
        $query = "SELECT * FROM OrderInfo WHERE Owner = :UserID OR GroupID = :usergroup";
}else{
        $query = "SELECT * FROM OrderInfo WHERE Owner = :UserID";
}

$stmt = $dbh->prepare($query);
$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
if(!empty($_SESSION['userGroup'])){
        $stmt->bindParam(':usergroup', $_SESSION['userGroup'], PDO::PARAM_INT);
}
$stmt->execute();

?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MyBox Cloud - Missions</title>
		<link rel="stylesheet" type="text/css" href="css/materialize.min.css">
		<link rel="stylesheet" type="text/css" href="css/style.css?Ver=2">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
		<script type="text/javascript" src="js/materialize.min.js"></script>
		<script type="text/javascript" src="js/footerFixed.js"></script>
		<!-- <link rel="stylesheet" type="text/css" href="style.css"> -->
	</head>
	<body>

<script type="text/javascript">
        var Count = 0;
        function checkValue(check){
                var btn = document.getElementById('mapGet');

                if (check.checked) {
                        btn.removeAttribute('disabled');
                        Count++;
                } else {
                        Count--;
                        if(Count == 0){
                                btn.setAttribute('disabled', 'disabled');
                        }
                }
        }
</script>

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
                <form action="routePreCalc.php" method="POST">
        	<a class="waves-effect waves-light btn" href="./dashboard.php">
        		<i class="material-icons left">keyboard_arrow_left</i>ホームに戻る
        	</a>
		<span class="listTitle">回収管理</span>
                <a class="waves-effect waves-light btn" href="#">
                        <i class="material-icons left">search</i>検索
                </a>
                &ensp;
                <button id="mapGet" class="btn waves-effect waves-light" type="submit" name="action" disabled="disabled">
			ルート探索 (位置情報取得権限が必要)
			<i class="material-icons right">navigation</i>
		</button>

		<div class="listOutput">
		<?php
			$counter = 0;
			foreach($stmt as $data){
				if(empty($data['DevName'])){
					break;
				}
				if($counter == 0){
					echo '<ul class="collapsible">';
				}
				echo '<li><div class="collapsible-header">';
				echo $data['DevName'] . '&nbsp;(' . $data['DeviceID'] . ')';

                                echo '<div class="listButton">';
				echo '<label class="waves-effect waves-light btn yellow darken-3"><input type="checkbox" name="Boxes[]" value="' . $data['DeviceID'] .  '" onclick="checkValue(this)"><span>回収する</span></label>&nbsp;';
				echo '<a class="waves-effect waves-light btn" href="completeCheck.php?OrderID=' . $data['DeviceID'] . '"><i class="material-icons left">check</i>完了済みにする</a>&nbsp;';
				echo '<a class="waves-effect waves-light btn red" href="deleteMission.php?OrderID=' . $data['DeviceID'] . '"><i class="material-icons left">highlight_off</i>依頼取消</a>';
				echo '</div>';
				echo '</div></li>';
				$counter++;
			}
		if($counter != 0){
			echo '</ul>';
		}else{
			echo '<br><span class="listTitle">回収依頼中のゴミ箱がありません。</span>';
		}
		?>
		</div>
		</form>

	</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
	</body>
</html>

