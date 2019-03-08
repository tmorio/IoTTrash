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
        $query = "SELECT * FROM StatusData WHERE Owner = :UserID AND DeviceID = :deviceid";

        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
        $stmt->bindParam(':deviceid', $_GET['Device'], PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        if(empty($data['DeviceID'])){
                echo '削除権限がありません。';
                exit(0);
        }

        $query = "DELETE FROM StatusData WHERE Owner = :UserID AND DeviceID = :deviceid";

        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
        $stmt->bindParam(':deviceid', $_GET['Device'], PDO::PARAM_STR);
        $stmt->execute();

        $query = "DELETE FROM History WHERE DeviceID = :deviceid";

        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':deviceid', $_GET['Device'], PDO::PARAM_STR);
        $stmt->execute();

	header("Location: editDevice.php");
}else{
	$query = "SELECT * FROM StatusData WHERE Owner = :UserID AND DeviceID = :deviceid";

	$stmt = $dbh->prepare($query);
	$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
	$stmt->bindParam(':deviceid', $_GET['Device'], PDO::PARAM_STR);
	$stmt->execute();
        $data = $stmt->fetch();
        if(empty($data['DeviceID'])){
                echo '削除権限がありません。';
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
                         echo "デバイス名: " .  htmlspecialchars($data['NickName'], ENT_QUOTES, 'UTF-8');
                         echo "&nbsp;(" .  htmlspecialchars($data['DeviceID'], ENT_QUOTES, 'UTF-8') . ")<br>";

                         if(empty($data['Time'])){
                         	echo "更新日時: 未取得";
                         }else{
                                echo "更新日時: " . $data['Time'];
                         }
			echo '</div>';
                        echo '</li>';
                ?>
                </ul>
                </div>
                <div class="deleteCheck">削除するとこのデバイスのデータ履歴も削除されます。<br>本当に削除しますか?</div><br><br>
		<div class="buttonH">
                	<a class="waves-effect waves-light btn-large listButton" href="editDevice.php"><i class="material-icons right">keyboard_return</i>編集・削除ページへ戻る</a><br><br><br>
                	<a class="waves-effect waves-light btn listButton red" href="delete.php?Device=<?php echo htmlspecialchars($_GET['Device'], ENT_QUOTES, 'UTF-8'); ?>&del=1"><i class="material-icons right">delete</i>削除する</a>
		</div>


	</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
	</body>
</html>
