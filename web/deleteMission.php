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
        $query = "SELECT * FROM OrderInfo WHERE (Owner = :UserID OR GroupID = :GroupsID) AND DeviceID = :orderNo";
        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
        $stmt->bindParam(':GroupsID', $_SESSION['userGroup'], PDO::PARAM_INT);
	$stmt->bindParam(':orderNo', $_GET['OrderID'], PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        if(empty($data['DeviceID'])){
                echo '権限がありません。';
                exit(0);
        }

        $query = "DELETE FROM OrderInfo WHERE DeviceID = :orderNo";
        $stmt = $dbh->prepare($query);
	$stmt->bindParam(':orderNo', $_GET['OrderID'], PDO::PARAM_STR);
        $stmt->execute();

        $query = "UPDATE StatusData SET OrderStatus = 0 WHERE DeviceID = :orderNo";
        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':orderNo', $_GET['OrderID'], PDO::PARAM_STR);
        $stmt->execute();

	if($_SESSION['userService'] == 1){
		header("Location: missions.php");
	}else{
		header("Location: getMenu.php");
	}
}else{
        $query = "SELECT * FROM OrderInfo WHERE (Owner = :UserID OR GroupID = :GroupsID) AND DeviceID = :orderNo";

        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
        $stmt->bindParam(':GroupsID', $_SESSION['userGroup'], PDO::PARAM_INT);
        $stmt->bindParam(':orderNo', $_GET['OrderID'], PDO::PARAM_STR);
	$stmt->execute();
        $data = $stmt->fetch();
        if(empty($data['DeviceID'])){
                echo '権限がありません。';
                exit(0);
        }
}
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MyBox Cloud - Delete</title>
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
		<br><br>
                <div class="listOutput">
                <ul class="collapsible">
                <?php
			 echo '<li><div class="collapsible-header">';
                                echo $data['OrderName'] ;
                                echo "<br>";

				echo '</div>';
        	                echo '</li>';
                ?>
                </ul>
                </div>
                <div class="deleteCheck">この依頼を削除します。<br>よろしいですか?</div><br><br>
		<div class="buttonH">
			<?php
				if($_SESSION['userService'] == 1){
                			echo '<a class="waves-effect waves-light btn-large listButton" href="missions.php"><i class="material-icons right">keyboard_return</i>依頼一覧へ戻る</a><br><br><br>';
				}else{
					echo '<a class="waves-effect waves-light btn-large listButton" href="getMenu.php"><i class="material-icons right">keyboard_return</i>依頼一覧へ戻る</a><br><br><br>';
				}
			?>
                	<a class="waves-effect waves-light btn listButton red" href="deleteMission.php?OrderID=<?php echo $_GET['OrderID']; ?>&del=1"><i class="material-icons right">delete</i>削除する</a>
		</div>


	</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
	</body>
</html>
