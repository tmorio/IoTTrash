<?php

session_start();

require_once('./myid.php');

$strcode = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8mb4'");
try {
                $dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_ID, DB_PASS, $strcode);
                $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
                echo $e->getMessage();
                exit;
}

$query = "SELECT * FROM Users WHERE ID = :UserID AND Name = :Username";
$stmt = $dbh->prepare($query);
$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_STR);
$stmt->bindParam(':Username', $_SESSION['userName'], PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch();

switch($_GET['Setup']){
	default:
		exit(0);
		break;
	case account:
		if (password_verify($_POST['nowPassword'], $result['Password'])){
                        if(!empty($_POST['newPassword'])){
				$stmt = $dbh->prepare("UPDATE Users SET Password = ? WHERE ID = ? AND Name = ?");
				$stmt->execute(array(password_hash($_POST['newPassword'], PASSWORD_DEFAULT), $_SESSION['userNo'], $_SESSION['userName']));

                        }
			$query = "UPDATE Users SET UserID = :newuserid, Name = :newname WHERE ID = :UserID AND Name = :Username";
			$stmt = $dbh->prepare($query);
			$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_STR);
			$stmt->bindParam(':Username', $_SESSION['userName'], PDO::PARAM_STR);
			$stmt->bindParam(':newuserid', $_POST['newUserID'], PDO::PARAM_STR);
			$stmt->bindParam(':newname', $_POST['newUsername'], PDO::PARAM_STR);
			$stmt->execute();
			$_SESSION['userName'] = $_POST['newUsername'];
			header("Location: ./settings.php?mes=2");
		}else{
			echo $_POST['nowPassword'];
			header("Location: ./settings.php?mes=1");
		}
		break;
}
