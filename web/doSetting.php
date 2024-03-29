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

switch($_GET['Setup']){
	default:
		exit(0);
		break;
	case account:
		$query = "SELECT * FROM Users WHERE ID = :UserID AND Name = :Username";
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_STR);
		$stmt->bindParam(':Username', $_SESSION['userName'], PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();

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

			if($_POST['newmail'] != $result['mailAddress']){
				$expire = date('Y-m-d H:i:s', (time() + 1800));
				$verifyCode = $urltoken = hash('sha256',uniqid(rand(),1));
				$query = "INSERT INTO EmailChange (UserID, newMail, verifyCode, expireTime) VALUES (:userid, :newmail, :verifycode, :expiretime)";
				$stmt = $dbh->prepare($query);
				$stmt->bindParam(':userid', $_SESSION['userNo'], PDO::PARAM_INT);
				$stmt->bindParam(':newmail', $_POST['newmail'], PDO::PARAM_STR);
				$stmt->bindParam(':verifycode', $verifyCode, PDO::PARAM_STR);
				$stmt->bindParam(':expiretime', $expire, PDO::PARAM_STR);
				$stmt->execute();

				$toMail = $_POST['newmail'];
				$returnMail = 'mybox@moritoworks.com';
				$name = "MyBox Cloud";
				$mail = 'mybox@moritoworks.com';
				$subject = "メールアドレスを確認して下さい。";
				$url = "https://" . SERVER_DOMAIN . "/verify.php?token=".$verifyCode;


$body = <<< EOM
MyBox IDのメールアドレスの変更を適用するには、30分以内に以下のURLにアクセスして下さい。
{$url}

この操作に心当たりがない場合は、他のユーザーが間違えてメールアドレスを入力した可能性があります。
大変お手数をおかけしますが、メールの破棄をお願い致します。

なお、このメールは送信専用のメールアドレスで送信しているため、返信頂いても対応することができません。
何卒ご了承ください。
------------------------------
MyBox Cloud

Developed by IoT oyama Team.
------------------------------

EOM;

				mb_language('ja');
				mb_internal_encoding('UTF-8');
				$header = 'From: ' . mb_encode_mimeheader($name). ' <' . $mail. '>';
				mb_send_mail($toMail, $subject, $body, $header, '-f'. $returnMail);
				header("Location: ./settings.php?mes=3");
				exit(0);
			}
			header("Location: ./settings.php?mes=2");
		}else{
			header("Location: ./settings.php?mes=1");
		}
		break;

        case notice:
		$MXSet = intval($_POST['Notice']['MX']);
		$SMSet = intval($_POST['Notice']['SM']);
		$GSSet = intval($_POST['Notice']['GS']);
		$ATMSSet = intval($_POST['Notice']['ATMS']);
		$ATSSSet = intval($_POST['Notice']['ATSS']);
                $query = "UPDATE UserSetting SET MaxNotice = :MaxSetting, SMNotice = :SMSetting, GetSendNotice = :GSetting, AutoSendMin = :ASMSetting, AutoSendSM = :ASSMSetting WHERE UserID = :UserID";
                $stmt = $dbh->prepare($query);
		$stmt->bindParam(':MaxSetting', $MXSet, PDO::PARAM_INT);
		$stmt->bindParam(':SMSetting', $SMSet, PDO::PARAM_INT);
		$stmt->bindParam(':GSetting', $GSSet, PDO::PARAM_INT);
		$stmt->bindParam(':ASMSetting', $ATMSSet, PDO::PARAM_INT);
		$stmt->bindParam(':ASSMSetting', $ATSSSet, PDO::PARAM_INT);
                $stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
                $stmt->execute();
		header("Location: ./settings.php?page=notice&mes=2");
		exit(0);
                break;
	case addGroup:
		$query = "INSERT INTO Groups (GroupName, AdminID) VALUES (:gName, :adminID)";
		$stmt = $dbh->prepare($query);
                $stmt->bindParam(':gName', $_POST['newGroup'], PDO::PARAM_STR);
                $stmt->bindParam(':adminID', $_SESSION['userNo'], PDO::PARAM_INT);
                $stmt->execute();

                $query = "SELECT * FROM Groups WHERE AdminID = :adminID";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':adminID', $_SESSION['userNo'], PDO::PARAM_INT);
                $stmt->execute();
		$result = $stmt->fetch();

		if(!empty($result['ID'])){
			$_SESSION['userGroup'] = $result['ID'];

                	$query = "UPDATE Users SET GroupID = :SetGroup WHERE ID = :UserID";
                	$stmt = $dbh->prepare($query);
			$stmt->bindParam(':SetGroup', $result['ID'], PDO::PARAM_INT);
                	$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
                	$stmt->execute();
			echo '<script>alert("組織の作成に成功しました。管理ページに移動します。");</script>';
		}else{
			echo '<script>alert("組織の作成に失敗しました。(E002:Post/Info Update Failed)");</script>';
		}
		header("Location: ./settings.php?page=group");
		break;
	case permission:
                $query = "SELECT * FROM Groups WHERE AdminID = :adminID";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':adminID', $_SESSION['userNo'], PDO::PARAM_INT);
                $stmt->execute();
                $adminResult = $stmt->fetch();

                $query = "SELECT * FROM Users WHERE ID = :UserID";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':UserID', $_GET['id'], PDO::PARAM_INT);
                $stmt->execute();
                $userResult = $stmt->fetch();

		if((empty($adminResult['ID'])) || ($adminResult['ID'] != $userResult['GroupID'])){
			echo '権限がありません。';
                	exit(0);
		}

		switch($_GET['type']){
			case 1:
				$query = "UPDATE Users SET Service = 1 WHERE ID = :UserID";
				break;
			default:
				$query = "UPDATE Users SET Service = 0 WHERE ID = :UserID";
				break;
		}
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(':UserID', $_GET['id'], PDO::PARAM_INT);
		$stmt->execute();
		header("Location: ./settings.php?page=group");
		break;
	case delFG:
                $query = "SELECT * FROM Groups WHERE AdminID = :adminID";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':adminID', $_SESSION['userNo'], PDO::PARAM_INT);
                $stmt->execute();
                $adminResult = $stmt->fetch();

                $query = "SELECT * FROM Users WHERE ID = :UserID";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':UserID', $_GET['id'], PDO::PARAM_INT);
                $stmt->execute();
                $userResult = $stmt->fetch();

                if((empty($adminResult['ID'])) || ($adminResult['ID'] != $userResult['GroupID'])){
                        echo '権限がありません。';
                        exit(0);
                }
		$query = "UPDATE Users SET GroupID = NULL, Service = 0 WHERE ID = :UserID";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':UserID', $_GET['id'], PDO::PARAM_INT);
                $stmt->execute();
                header("Location: ./settings.php?page=group");
		break;
	case addUser:
                $query = "SELECT * FROM Groups WHERE AdminID = :adminID";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':adminID', $_SESSION['userNo'], PDO::PARAM_INT);
                $stmt->execute();
                $groupResult = $stmt->fetch();

                if(empty($groupResult['ID'])){
                        echo '権限がありません。';
                        exit(0);
                }

		$expire = date('Y-m-d H:i:s', (time() + 86400));
                $verifyCode = hash('sha256',uniqid(rand(),1));
                $query = "INSERT INTO GroupCode (GroupID, AuthCode, expireTime, Email) VALUES (:groupID, :authcode, :expireTime, :userEmail)";
		$stmt = $dbh->prepare($query);
                $stmt->bindParam(':groupID', $groupResult['ID'], PDO::PARAM_INT);
                $stmt->bindParam(':authcode', $verifyCode, PDO::PARAM_STR);
                $stmt->bindParam(':expireTime', $expire, PDO::PARAM_STR);
                $stmt->bindParam(':userEmail', $_POST['newUserMail'], PDO::PARAM_STR);
                $stmt->execute();

                $toMail = $_POST['newUserMail'];
                $returnMail = 'mybox@moritoworks.com';
                $name = "MyBox Cloud";
                $mail = 'mybox@moritoworks.com';
                $subject = "組織に招待されました";
                $url = "https://" . SERVER_DOMAIN . "/verify.php?type=group&token=".$verifyCode;
		$GroupName = $groupResult['GroupName'];

$body = <<< EOM
次の組織への招待がありました。

組織名:{$GroupName}

組織に参加する場合は以下のリンクから手続きを行ってください。
{$url}


なお、このメールは送信専用のメールアドレスで送信しているため、返信頂いても対応することができません。
何卒ご了承ください。
------------------------------
MyBox Cloud

Developed by IoT oyama Team.
------------------------------

EOM;

                mb_language('ja');
                mb_internal_encoding('UTF-8');
                $header = 'From: ' . mb_encode_mimeheader($name). ' <' . $mail. '>';
                mb_send_mail($toMail, $subject, $body, $header, '-f'. $returnMail);
                header("Location: ./settings.php?page=group&tab=invite");
                exit(0);
		break;
	case delInvite:
                $query = "SELECT * FROM Groups WHERE AdminID = :adminID";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':adminID', $_SESSION['userNo'], PDO::PARAM_INT);
                $stmt->execute();
                $groupResult = $stmt->fetch();

                if(empty($groupResult['ID'])){
                        echo '権限がありません。';
                        exit(0);
                }
		$query = "DELETE FROM GroupCode WHERE ID = :inviteID AND GroupID = :groupID";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':inviteID', $_GET['id'], PDO::PARAM_INT);
		$stmt->bindParam(':groupID', $groupResult['ID'], PDO::PARAM_INT);
                $stmt->execute();

		header("Location: /settings.php?page=group&tab=invite");
		break;

}
