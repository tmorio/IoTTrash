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

$query = "SELECT * FROM Users WHERE ID = :UserID AND Name = :Username";
$stmt = $dbh->prepare($query);
$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_STR);
$stmt->bindParam(':Username', $_SESSION['userName'], PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch();

$query = "SELECT * FROM UserSetting WHERE UserID = :UserID";
$stmt = $dbh->prepare($query);
$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_STR);
$stmt->execute();
$Rsettings = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MyBox Cloud - Settings</title>
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
	<!-- 表示画面 （Google Mapみたいに2画面分割で左にリスト、右にマップ?)-->
	<div class="settingBoard">
		<!-- 設定分類一覧表示 -->
		<div class="collection with-header settingList">
			<div class="collection-header center-align"><a class="waves-effect waves-light btn" href="./dashboard.php">
				<i class="material-icons left">keyboard_arrow_left</i>ホームに戻る</a></div>
			<div class="collection-header"><h5>サービス設定</h5></div>
			<a href="?page=account" class="collection-item blue-grey-text text-darken-4"><i class="material-icons left">account_circle</i>アカウント設定</a>
			<a href="?page=notice" class="collection-item blue-grey-text text-darken-4"><i class="material-icons left">email</i>通知・自動化</a>
			<a href="?page=group" class="collection-item blue-grey-text text-darken-4"><i class="material-icons left">group</i>組織設定</a>
			<a href="?page=credit" class="collection-item blue-grey-text text-darken-4"><i class="material-icons left">copyright</i>クレジット</a>
		</div>
		
		<!-- 設定表示 -->
		<div class="settingInfo">
<?php
switch ($_GET['mes']) {
case 1:
	echo 'パスワードが違います。';
	break;

case 2:
	echo '設定を更新しました。';
	break;

case 3:
	echo '設定を更新しました。メールアドレスの変更を完了するには、届いたメールにあるURLを30分以内にクリックしてください。';
	break;
}

switch ($_GET['page']) {
default:
	echo '
						<h3>アカウント設定</h3><br />
                				<form action="doSetting.php?Setup=account" method="POST">
                        				MyBox ID (ログインID)<br />
                        	        		<input type="text" name="newUserID" id="newUserID" pattern="^[0-9A-Za-z]+$" value="' . htmlspecialchars($result['UserID'], ENT_QUOTES, 'UTF-8') . '" required>
                                			名前<br />
                                			<input type="text" name="newUsername" id="newUsername"  value="' . htmlspecialchars($result['Name'], ENT_QUOTES, 'UTF-8') . '" required>
                                                        メールアドレス<br />
                                                        <input type="email" name="newmail" id="newmail"  value="' . htmlspecialchars($result['mailAddress'], ENT_QUOTES, 'UTF-8') . '" required>
                                                        新しいパスワード (変更する場合は入力して下さい)<br />
                                                        <input type="password" name="newPassword" id="newPassword">
                                			<br /><br /><br />
                                                        現在のパスワード (必須)<br />
                                                        <input type="password" name="nowPassword" id="nowPassword" required><br />
                                			<button class="btn waves-effect waves-light" type="submit"><i class="material-icons right">check</i>変更を適用する</button>
						</form>
					';
	break;

case notice:
	echo '
						<h3>通知・自動化設定</h3><br />
						<form action="doSetting.php?Setup=notice" method="POST">
							<h4>通知設定</h4>
							<label>
								<input type="checkbox" name="Notice[MX]" class="filled-in" value="1"';
	if ($Rsettings['MaxNotice'] == 1) {
		echo ' checked="checked"';
	}

	echo '			/>
								<span>満杯になりそうな時に通知</span>
							</label>
                                                        <br />
                                                        <label>
                                                                <input type="checkbox"  name="Notice[SM]" class="filled-in" value="1"';
	if ($Rsettings['SMNotice'] == 1) {
		echo ' checked="checked"';
	}

	echo '			 />
                                                                <span>異臭の発生予測を通知</span>
                                                        </label>
							<br />
                                                        <label>
                                                                <input type="checkbox"  name="Notice[GS]" class="filled-in" value="1"';
	if ($Rsettings['GetSendNotice'] == 1) {
		echo ' checked="checked"';
	}

	echo '/>
                                                                <span>回収作業が完了した時に通知</span>
                                                        </label><br />

							<h4>自動化設定</h4>
							<label>
                                                                <input type="checkbox" name="Notice[ATMS]" class="filled-in" value="1"';
	if ($Rsettings['AutoSendMin'] == 1) {
		echo ' checked="checked"';
	}

	echo '                  />
                                                                <span>空き容量が少なくなったら自動で回収依頼を行う</span>
                                                        </label>
							<br />
							<label>
                                                                <input type="checkbox" name="Notice[ATSS]" class="filled-in" value="1"';
	if ($Rsettings['AutoSendSM'] == 1) {
		echo ' checked="checked"';
	}

	echo '                  />
                                                                <span>臭いが発生すると予測したら自動で回収依頼を行う</span>
                                                        </label>
							<br /><br />
							<button class="btn waves-effect waves-light" type="submit"><i class="material-icons right">check</i>変更を適用する</button>
						</form>
					';
	break;

case group:
	if ($_GET['tab'] != "invite") {
		echo '<h3>組織設定</h3>';
		if (empty($_SESSION['userGroup'])) {
			echo '現在組織に所属していません。<br />組織のセットアップは以下から行えます。<br />※組織への参加は組織管理者から招待メールを送信して頂く必要があります。<br /><br />';
			echo '<h5>組織の管理者になる</h5><a class="waves-effect waves-light btn modal-trigger" href="#addGroup"><i class="material-icons left">group_add</i>組織を作成する</a>';
		}else {
			$query = "SELECT * FROM Groups WHERE ID = :GroupID";
			$stmt = $dbh->prepare($query);
			$stmt->bindParam(':GroupID', $_SESSION['userGroup'], PDO::PARAM_INT);
			$stmt->execute();
			$groupResult = $stmt->fetch();
			$query = "SELECT * FROM Users WHERE GroupID = :GroupID";
			$stmt = $dbh->prepare($query);
			$stmt->bindParam(':GroupID', $_SESSION['userGroup'], PDO::PARAM_INT);
			$stmt->execute();
			echo '<h5>' . $groupResult['GroupName'] . 'のメンバー</h5>';
			if ($groupResult['AdminID'] == $_SESSION['userNo']) {
				echo '<a class="waves-effect waves-light btn modal-trigger" href="#addUser"><i class="material-icons left">group_add</i>メンバーを追加</a>';
				echo '&nbsp;<a class="waves-effect waves-light btn" href="?page=group&tab=invite"><i class="material-icons left">edit</i>招待の管理</a>';
				echo '<br /><br />';
			}

			echo '<ul class="collection">';
			$query = "SELECT * FROM Users WHERE GroupID = :GroupID AND ID = :AdminID";
			$stmt = $dbh->prepare($query);
			$stmt->bindParam(':GroupID', $_SESSION['userGroup'], PDO::PARAM_INT);
			$stmt->bindParam(':AdminID', $groupResult['AdminID'], PDO::PARAM_INT);
			$stmt->execute();
			$adminInfo = $stmt->fetch();
			echo '<li class="collection-item avatar">';
			if (empty($adminInfo['PhotoID'])) {
				echo '<img src="img/default.jpg" alt="" class="circle">';
			}
			else {
				echo '<img src="img/users/' . $adminInfo['PhotoID'] . '.jpg" alt="" class="circle">';
			}

			echo '<span class="title">' . htmlspecialchars($adminInfo['Name'], ENT_QUOTES, 'UTF-8') . '</span>';
			echo '<p>組織管理者</p>';
			echo '</li>';
			unset($adminInfo);
			$query = "SELECT * FROM Users WHERE (GroupID = :GroupID AND Service = 0) AND ID != :AdminID";
			$stmt = $dbh->prepare($query);
			$stmt->bindParam(':GroupID', $_SESSION['userGroup'], PDO::PARAM_INT);
			$stmt->bindParam(':AdminID', $groupResult['AdminID'], PDO::PARAM_INT);
			$stmt->execute();
			foreach($stmt as $uData) {
				echo '<li class="collection-item avatar">';
				if (empty($uData['PhotoID'])) {
					echo '<img src="img/default.jpg" alt="" class="circle">';
				}
				else {
					echo '<img src="img/users/' . $uData['PhotoID'] . '.jpg" alt="" class="circle">';
				}

				echo '<span class="title">' . htmlspecialchars($uData['Name'], ENT_QUOTES, 'UTF-8') . '</span>';
				if ($groupResult['AdminID'] == $_SESSION['userNo']) {
					echo '<span class="right">';
					echo '<a class="waves-effect waves-light btn modal-trigger blue" href="#changePermission" onclick="changeUserPermission(' . $uData['ID'] . ', 1)"><i class="material-icons left">edit</i>回収担当者に変更</a>';
					echo '&nbsp;';
					echo '<a class="waves-effect waves-light btn modal-trigger red" href="#delUser" onclick="deleteUserGroup(' . $uData['ID'] . ')"><i class="material-icons left">close</i>削除</a>';
					echo '</span>';
				}

				echo '<p>';
				echo '一般 - オペレータ';
				echo '</li>';
			}

			$query = "SELECT * FROM Users WHERE (GroupID = :GroupID AND Service = 1) AND ID != :AdminID";
			$stmt = $dbh->prepare($query);
			$stmt->bindParam(':GroupID', $_SESSION['userGroup'], PDO::PARAM_INT);
			$stmt->bindParam(':AdminID', $groupResult['AdminID'], PDO::PARAM_INT);
			$stmt->execute();
			foreach($stmt as $uData) {
				echo '<li class="collection-item avatar">';
				if (empty($uData['PhotoID'])) {
					echo '<img src="img/default.jpg" alt="" class="circle">';
				}
				else {
					echo '<img src="img/users/' . $uData['PhotoID'] . '.jpg" alt="" class="circle">';
				}

				echo '<span class="title">' . htmlspecialchars($uData['Name'], ENT_QUOTES, 'UTF-8') . '</span>';
				if ($groupResult['AdminID'] == $_SESSION['userNo']) {
					echo '<span class="right">';
					echo '<a class="waves-effect waves-light btn modal-trigger blue" href="#changePermission" onclick="changeUserPermission(' . $uData['ID'] . ', 0)"><i class="material-icons left">edit</i>オペレータに変更</a>';
					echo '&nbsp;';
					echo '<a class="waves-effect waves-light btn modal-trigger red" href="#delUser" onclick="deleteUserGroup(' . $uData['ID'] . ')"><i class="material-icons left">close</i>削除</a>';
					echo '</span>';
				}

				echo '<p>';
				echo '一般 - 回収担当者';
				echo '</li>';
			}

			echo '</ul>';
			}

		}else{
			echo '<h3>組織設定 - 招待一覧</h3>';
			echo '<a class="waves-effect waves-light btn blue" href="?page=group"><i class="material-icons left">chevron_left</i>メンバー一覧へ</a>';
			echo '&nbsp;<a class="waves-effect waves-light btn modal-trigger" href="#addUser"><i class="material-icons left">group_add</i>メンバーを追加</a><br><br>';

			$query = "SELECT * FROM GroupCode WHERE GroupID = :GroupID";
			$stmt = $dbh->prepare($query);
			$stmt->bindParam(':GroupID', $_SESSION['userGroup'], PDO::PARAM_INT);
			$stmt->execute();
			$inviteList = $stmt->fetchAll();

			if(count($inviteList) != 0){
				echo '<ul class="collection">';
				foreach($inviteList as $uData) {
					echo '<li class="collection-item avatar">';
					echo '<img src="img/default.jpg" alt="" class="circle">';
					echo '<span class="title">' . htmlspecialchars($uData['Email'], ENT_QUOTES, 'UTF-8') . '</span>';
					echo '<span class="right">';
					echo '<a class="waves-effect waves-light btn modal-trigger red" href="#deleteInvite" onclick="deleteInvite(' . $uData['ID'] . ')"><i class="material-icons left">close</i>招待取り消し</a>';
					echo '</span>';
					echo '<p>';
					echo '有効期限 : ' . $uData['expireTime'];
					echo '</li>';
				}

				echo '</ul>';
			}else{
				echo '現在招待しているユーザーはありません。';
			}
		}

	break;

case credit:
	require_once ('./credit.php');
	break;
}

?>

		</div>
	</div>

        <div id="addGroup" class="modal">
		<form action="doSetting.php?Setup=addGroup" method="POST">
                	<div class="modal-content">
                        	 <h4>組織の作成</h4>
                        	 <p>組織名を入力してください。</p>
				 <br>組織名<br>
				 <input type="text" name="newGroup" id="newGroup" required>
                	</div>
                	<div class="modal-footer">
                        	 <a class="waves-effect waves-light modal-close btn red"><i class="material-icons left">close</i>キャンセル</a>
				 <button class="btn waves-effect waves-light btn blue" type="submit"><i class="material-icons right">check</i>組織の作成</button>
                	</div>
		</form>
        </div>

        <div id="addUser" class="modal">
                <form action="doSetting.php?Setup=addUser" method="POST">
                        <div class="modal-content">
                                 <h4>ユーザーの追加</h4>
                                 招待したいユーザーのメールアドレスを入力して下さい。<br>
				 MyBox IDを持っていないユーザーも可能です。<br>
                                 <br>メールアドレス<br>
                                 <input type="email" name="newUserMail" id="newUserMail" required>
                        </div>
                        <div class="modal-footer">
                                 <a class="waves-effect waves-light modal-close btn red"><i class="material-icons left">close</i>キャンセル</a>
                                 <button class="btn waves-effect waves-light btn blue" type="submit"><i class="material-icons right">send</i>招待の送信</button>
                        </div>
                </form>
        </div>

        <div id="delUser" class="modal">
                        <div class="modal-content">
                                 <h4>組織から削除する</h4>
                                 選択したアカウントを組織から削除します。<br>
				 本当によろしいですか?
                        </div>
                        <div class="modal-footer">
                                 <a class="waves-effect waves-light modal-close btn left"><i class="material-icons left">close</i>キャンセル</a>
                                 <a id="delUserFG" class="btn waves-effect waves-light btn red right" href=""><i class="material-icons right">check</i>削除する</a>
                        </div>
        </div>
        <div id="changePermission" class="modal">
                        <div class="modal-content">
                                 <h4>権限の変更</h4>
                                 選択したアカウントの権限を変更します。<br>
                                 本当によろしいですか?
                        </div>
                        <div class="modal-footer">
                                 <a class="waves-effect waves-light modal-close btn left"><i class="material-icons left">close</i>キャンセル</a>
                                 <a id="chUserP" class="btn waves-effect waves-light btn blue right" href=""><i class="material-icons right">check</i>変更する</a>
                        </div>
        </div>
        <div id="deleteInvite" class="modal">
                        <div class="modal-content">
                                 <h4>招待の取り消し</h4>
                                 選択した招待を無効にします。<br>
                                 本当によろしいですか?
                        </div>
                        <div class="modal-footer">
                                 <a class="waves-effect waves-light modal-close btn left"><i class="material-icons left">close</i>キャンセル</a>
                                 <a id="delInvite" class="btn waves-effect waves-light btn red right" href=""><i class="material-icons right">check</i>招待を取り消す</a>
                        </div>
        </div>


		<!-- フッター -->
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
                                function deleteInvite(userID){
                                        var target = document.getElementById("delInvite");
                                        target.href = "doSetting.php?Setup=delInvite&id=" + userID;
                                }

			</script>
		</footer>
	</body>
</html>

