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
	$Intime = date('Y-m-d H:i:s', time());

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

        $query = "UPDATE StatusData SET DevInfo = 1, OrderStatus = 0, WarSM = 0, Dis = NULL, LastReset = :nowTime WHERE DeviceID = :orderNo";
        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':nowTime', $Intime, PDO::PARAM_STR);
        $stmt->bindParam(':orderNo', $_GET['OrderID'], PDO::PARAM_STR);
        $stmt->execute();

        $query = "DELETE FROM OrderInfo WHERE DeviceID = :orderNo";
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
	<?php require_once('./header.php'); ?>
	<div class="deviceListBoard">
		<br><br>
                <div class="listOutput">
                <ul class="collapsible">
                <?php
			 echo '<li><div class="collapsible-header">';
                                echo htmlspecialchars($data['DevName'], ENT_QUOTES, 'UTF-8') . "&thinsp;(" . htmlspecialchars($data['DeviceID'], ENT_QUOTES, 'UTF-8') . ")" ;
                                echo "<br>";

				echo '</div>';
        	                echo '</li>';
                ?>
                </ul>
                </div>
                <div class="deleteCheck">この依頼を完了済みにします。<br>よろしいですか?</div><br><br>
		<div class="buttonH">
			<?php
				if($_SESSION['userService'] == 1){
                			echo '<a class="waves-effect waves-light btn-large listButton" href="missions.php"><i class="material-icons right">keyboard_return</i>依頼一覧へ戻る</a><br><br><br>';
				}else{
					echo '<a class="waves-effect waves-light btn-large listButton" href="getMenu.php"><i class="material-icons right">keyboard_return</i>依頼一覧へ戻る</a><br><br><br>';
				}
			?>
                	<a class="waves-effect waves-light btn listButton red" href="completeCheck.php?OrderID=<?php echo $_GET['OrderID']; ?>&del=1"><i class="material-icons right">done</i>完了済みにする</a>
		</div>


	</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
	</body>
</html>
