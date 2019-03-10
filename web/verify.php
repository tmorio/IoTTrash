<?php
require_once('./myid.php');
require_once('./siteInfo.php');

session_start();

$strcode = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8mb4'");
try {
                $dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_ID, DB_PASS, $strcode);
                $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
                echo $e->getMessage();
                exit;
}

if(!empty($_SESSION['errorMessage'])){
	unset($_SESSION['errorMessage']);
}

switch($_GET['Setup']){
	default:
		break;
	case login:
		$query = "SELECT * FROM Users WHERE UserID = :UserID";
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(':UserID', $_POST['inUserid'], PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();
		if (password_verify($_POST['ACPassword'], $result['Password'])){
                        $query = "SELECT * FROM GroupCode WHERE AuthCode = :token";
                        $stmt = $dbh->prepare($query);
                        $stmt->bindParam(':token', $_GET['token'], PDO::PARAM_STR);
                        $stmt->execute();
			$result = $stmt->fetch();

			$query = "UPDATE Users SET GroupID = :newGroup WHERE UserID = :UserID";
			$stmt = $dbh->prepare($query);
			$stmt->bindParam(':newGroup', $result['GroupID'], PDO::PARAM_STR);
			$stmt->bindParam(':UserID', $_POST['inUserid'], PDO::PARAM_STR);
			$stmt->execute();

                	$query = "SELECT * FROM Users WHERE UserID = :UserID";
                	$stmt = $dbh->prepare($query);
                	$stmt->bindParam(':UserID', $_POST['inUserid'], PDO::PARAM_STR);
                	$stmt->execute();
                	$result = $stmt->fetch();

			$query = "DELETE FROM GroupCode WHERE AuthCode = :token";
                        $stmt = $dbh->prepare($query);
                        $stmt->bindParam(':token', $_GET['token'], PDO::PARAM_STR);
                        $stmt->execute();

                        $_SESSION['userNo'] = $result['ID'];
                        $_SESSION['userGroup'] = $result['GroupID'];
                        $_SESSION['userName'] = $result['Name'];
                        $_SESSION['userService'] = $result['Service'];
                        $_SESSION['PhotoID'] = $result['PhotoID'];

			session_regenerate_id(true);
			header("Location: verify.php?Status=ACD");
                 }
		break;
	case addUser:
		if(empty($_POST['newPassword'])){
			echo '<META http-equiv="Refresh" content="0;URL=verify.php?type=group&token=' . $_GET['token']  . '">';
			exit(0);
			break;
		}
		if($_POST['newPassword'] != $_POST['newPasswordRe']){
			$_SESSION['errorMessage'] = "パスワードが一致しません。";
			break;
		}

		$query = "SELECT * FROM Users WHERE UserID = :UserID OR mailAddress = :newEmail";
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(':UserID', $_POST['accountID'], PDO::PARAM_STR);
		$stmt->bindParam(':newEmail', $_POST['accountEmail'], PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetchAll();

		if(count($result) != 0){
			$_SESSION['errorMessage'] = "入力されたMyBox IDまたはメールアドレスはご利用頂けません。";
		}

		$query = "SELECT * FROM GroupCode WHERE AuthCode = :token";
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(':token', $_GET['token'], PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();

                if(count($result) == 0){
                        $_SESSION['errorMessage'] = "認証コードが無効です。";
			break;
                }

		$query = "INSERT INTO Users ( UserID, Password, GroupID, Name, mailAddress ) VALUES (:newUserID, :newPassword, :newGroupID, :newName, :newMail)";
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(':newUserID', $_POST['accountID'], PDO::PARAM_STR);
		$stmt->bindParam(':newPassword', password_hash($_POST['newPassword'], PASSWORD_DEFAULT), PDO::PARAM_STR);
		$stmt->bindParam(':newGroupID', $result['GroupID'], PDO::PARAM_STR);
		$stmt->bindParam(':newName', $_POST['userName'], PDO::PARAM_STR);
		$stmt->bindParam(':newMail', $_POST['accountEmail'], PDO::PARAM_STR);
		$stmt->execute();

                $query = "SELECT * FROM Users WHERE UserID = :myID AND mailAddress = :userAddress";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':myID', $_POST['accountID'], PDO::PARAM_STR);
		$stmt->bindParam(':userAddress', $_POST['accountEmail'], PDO::PARAM_STR);
                $stmt->execute();
                $result = $stmt->fetch();

		$query = "INSERT INTO UserSetting (UserID) VALUES (:UserID)";
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(':UserID', $result['ID'], PDO::PARAM_STR);
		$stmt->execute();

                $query = "DELETE FROM GroupCode WHERE AuthCode = :token";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':token', $_GET['token'], PDO::PARAM_STR);
                $stmt->execute();

		header("Location: verify.php?Status=ACC");
		break;
}

?>
<!doctype html>
<html>
        <head>
                <meta charset="UTF-8">
                <title>MyBox Cloud - Welcome</title>
                <link rel="stylesheet" type="text/css" href="css/materialize.min.css">
                <link rel="stylesheet" type="text/css" href="css/style.css?">
                <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
                <script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
                <script type="text/javascript" src="js/materialize.min.js"></script>
                <script type="text/javascript" src="js/footerFixed.js"></script>
                <!-- <link rel="stylesheet" type="text/css" href="style.css"> -->
        </head>
        <body>

        <div class="navbar-fixed">
                <nav>
                        <div class="nav-wrapper">
                                <img class="logo-image" src="img/logo.png">
                        </div>
                </nav>
        </div>

        <!-- 表示画面 （Google Mapみたいに2画面分割で左にリスト、右にマップ?)-->
        <div class="dashDisplay">
		<div class="listTitle">
<?php

switch($_GET['Status']){
	case ACD:
		echo '<h3>Welcome - 参加登録完了</h3>';
		echo '組織への参加登録が完了しました。';
		echo '<br>5秒後にダッシュボードに戻ります。<META http-equiv="Refresh" content="5;URL=dashboard.php">';
		exit(0);
		break;
        case ACC:
                echo '<h3>Welcome - 参加登録完了</h3>';
                echo '組織への参加登録が完了しました。';
                echo '<br>5秒後にログイン画面に移動します。<META http-equiv="Refresh" content="5;URL=login.php">';
                exit(0);
                break;
	default:
		if(!empty($_SESSION['errorMessage'])){
			echo '
			<div class="row">
                		<div class="col s12 m12 pink lighten-5">
                        		<h5 class="valign-wrapper">
                                		<i style="font-size: 2.5rem;" class="material-icons orange-text text-darken-5">warning</i>
                                		<font class="red-text">';
                                			echo htmlspecialchars($_SESSION['errorMessage'], ENT_QUOTES);
							unset($_SESSION['errorMessage']);
                        	        	echo '</font>
                                	</h5>
                        	</div>
                	</div>';
		}
			break;
}

switch($_GET['type']){
	default:
		$query = "SELECT * FROM EmailChange WHERE verifyCode = :token";
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(':token', $_GET['token'], PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();
		if((strtotime($result['expireTime']) >= time()) && !empty($result['expireTime'])){
			$query = "UPDATE Users SET mailAddress = :newmail WHERE ID = :UserID";
			$stmt = $dbh->prepare($query);
			$stmt->bindParam(':UserID', $result['UserID'], PDO::PARAM_INT);
			$stmt->bindParam(':newmail', $result['newMail'], PDO::PARAM_STR);
			$stmt->execute();
			echo '<h3>メールアドレス変更完了</h3>';
			echo 'メールアドレスの変更が完了しました。';
		}else{
			echo '<h3>E004: Code Authentication Error</h3>';
			echo 'URLの期限が切れているか、URLが無効です。再度お手続きください。';
		}
		        $query = "DELETE FROM EmailChange WHERE verifyCode = :token";
	        $stmt = $dbh->prepare($query);
	        $stmt->bindParam(':token', $_GET['token'], PDO::PARAM_STR);
	        $stmt->execute();
		echo '<br>3秒後にダッシュボードに戻ります。<META http-equiv="Refresh" content="3;URL=dashboard.php">';
		break;
	case group:
                $query = "SELECT * FROM GroupCode WHERE AuthCode = :tokenID";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':tokenID', $_GET['token'], PDO::PARAM_INT);
                $stmt->execute();
                $groupResult = $stmt->fetch();

                if(empty($groupResult['ID'])){
                        echo '<h3>E004: Code Authentication Error</h3>';
                        echo 'URLの期限が切れているか、URLが無効です。再度お手続きください。';
			echo '<br>5秒後にダッシュボードに戻ります。<META http-equiv="Refresh" content="5;URL=dashboard.php">';
                }else{
			$query = "SELECT * FROM Groups WHERE ID = :groupID";
                	$stmt = $dbh->prepare($query);
                	$stmt->bindParam(':groupID', $groupResult['GroupID'], PDO::PARAM_INT);
                	$stmt->execute();
                	$infoResult = $stmt->fetch();

			echo '<h3>Welcome! - ' . htmlspecialchars($infoResult['GroupName'], ENT_QUOTES, 'UTF-8')  . 'に参加する</h3>';
			echo '参加方法を選択してください。<br><br>';
			echo '<a class="waves-effect waves-light modal-trigger btn" href="#nowAccount"><i class="material-icons left">exit_to_app</i>既存のアカウントで参加する</a>&nbsp;';
			echo '<a class="waves-effect waves-light modal-trigger btn" href="#newAccount"><i class="material-icons left">add</i>新規アカウントで参加する(初めての方)</a>';
		}
		break;
}
?>
		</div>
	</div>

        <div id="nowAccount" class="modal">
                <form action="verify.php?type=group&token=<?php echo $_GET['token']; ?>&Setup=login" method="POST">
                        <div class="modal-content">
                                <h4>ログイン</h4>
				<br>
				<div class="input-field col s12 m12">
                                	<i class="material-icons prefix">person</i>
                                        <input type="text" id="inUserid" name="inUserid" class="validate" value="" required>
                                        <label for="inUserid" class="active">MyBox ID</label>
                                </div>
                                <div class="input-field col s12 m12">
                                	<i class="material-icons prefix">vpn_key</i>
                                        <input type="password" id="ACPassword" name="ACPassword" value="" required>
                                        <label for="password" class="active">パスワード</label>
                                </div>
                        </div>
                        <div class="modal-footer">
                                 <a class="waves-effect waves-light modal-close btn red"><i class="material-icons left">close</i>キャンセル</a>
                                 <button class="btn waves-effect waves-light btn blue" type="submit" id="login" name="login"><i class="material-icons right">send</i>ログインして参加</button>
                        </div>
                </form>
        </div>

        <div id="newAccount" class="modal">
                <form action="verify.php?type=group&token=<?php echo $_GET['token']; ?>&Setup=addUser" method="POST">
                        <div class="modal-content">
                                <h4>アカウント作成</h4>
				<br>
                                <div class="input-field col s12 m12">
                                        <i class="material-icons prefix">person</i>
                                        <input type="text" id="accountID" name="accountID" pattern="^[0-9A-Za-z]+$"  class="validate" value="" required>
                                        <label for="accountID" class="active">希望のMyBox ID</label>
                                </div>
                                <div class="input-field col s12 m12">
                                        <i class="material-icons prefix">mail</i>
                                        <input type="email" id="accountEmail" name="accountEmail" class="validate" value="" required>
                                        <label for="accountEmail" class="active">メールアドレス</label>
                                </div>
                                <div class="input-field col s12 m12">
                                        <i class="material-icons prefix">edit</i>
                                        <input type="text" id="userName" name="userName" class="validate" value="" required>
                                        <label for="userName" class="active">表示名</label>
                                </div>
                                <div class="input-field col s12 m12">
                                        <i class="material-icons prefix">vpn_key</i>
                                        <input type="password" id="newPassword" name="newPassword" value="" required>
                                        <label for="password" class="active">パスワード</label>
                                </div>
                                <div class="input-field col s12 m12">
                                        <i class="material-icons prefix">vpn_key</i>
                                        <input type="password" id="newPasswordRe" name="newPasswordRe" value="" required>
                                        <label for="password" class="active">パスワード(再入力)</label>
                                </div>

                        </div>
                        <div class="modal-footer">
                                 <a class="waves-effect waves-light modal-close btn red"><i class="material-icons left">close</i>キャンセル</a>
                                 <button class="btn waves-effect waves-light btn blue" type="submit"><i class="material-icons right">send</i>アカウント作成して参加</button>
                        </div>
                </form>
        </div>

                <footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
                        <script>

                                $(document).ready(function(){
                                        $('.modal').modal();
                                });
                                function deleteUserGroup(userID){
                                        var target = document.getElementById("delUserFG");
                                        target.href = "doSetting.php?Setup=delFG&id=" + userID;
                                }
                                function changeUserPermission(userID, uType){
                                        var target = document.getElementById("chUserP");
                                        target.href = "doSetting.php?Setup=permission&id=" + userID + "&type=" + uType;
                                }

                        </script>
                </footer>
        </body>
</html>
