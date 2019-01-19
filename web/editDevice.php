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

if(!empty($_SESSION['userGroup'])){
        $query = "SELECT * FROM StatusData WHERE Owner = :UserID OR GroupID = :usergroup";
}else{
        $query = "SELECT * FROM StatusData WHERE Owner = :UserID";
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
		<title>MyBox Cloud - EditDevice</title>
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
        	<a class="waves-effect waves-light btn" href="./boxtool.php">
        		<i class="material-icons left">keyboard_arrow_left</i>デバイス管理に戻る
        	</a>
                <span class="listTitle">編集・削除</span>
                <a class="waves-effect waves-light btn" href="./addDevice.php">
                        <i class="material-icons left">add</i>デバイス追加
                </a>
                <a class="waves-effect waves-light btn" href="deviceList.php">
                        <i class="material-icons left">equalizer</i>一覧・分析
                </a>
                <a class="waves-effect waves-light btn" href="#">
                        <i class="material-icons left">search</i>検索
                </a>

		<?php if($_GET['Error'] == 1){ echo '<br><br><span class="editError">（編集エラー）入力されたデバイスIDが6文字ではありません。</span>';} ?>
                <div class="listOutput">
                <ul class="collapsible">
                <?php
			//ここに適用したい (jsについては<head>のコメント参照)

                        foreach($stmt as $data){
                                echo '<li><div class="collapsible-header">';
                                echo $data['NickName'] ;
                                echo "&nbsp;(" .  $data['DeviceID'] . ")<br>";

                                if(empty($data['Time'])){
                                        echo "更新日時: 未取得";
                                }else{
                                        echo "更新日時: " . $data['Time'];
                                }
				echo '<span class="badge">クリックで編集</span></div>';
                                //echo '<div class="listButton">';
                                //echo '<a class="waves-effect waves-light btn" href="#"><i class="material-icons left">edit</i>編集・削除</a>';
				//echo '</div>';
                                echo '<div class="collapsible-body listDetail">
					<span class="editDevice">デバイス情報の編集</span><br><br>
					<div>
						<form action="devEdit.php?Device=' . $data['DeviceID'] . '" method="POST">
							デバイス名<br>
							<input type="text" name="NickName" id="NickName" value="' . $data['NickName'] . '" required>
							デバイスID<br>
							<input type="text" name="DeviceID" id="DeviceID" pattern="^[0-9A-Za-z]+$" value="' . $data['DeviceID'] . '" required>
							<br><br>
							<button class="btn waves-effect waves-light" type="submit"><i class="material-icons right">check</i>編集を適用する</button>
							<a class="waves-effect waves-light btn listButton red" href="delete.php?Device=' . $data['DeviceID'] . '"><i class="material-icons right">delete</i>削除</a>
						</form>
					</div>
				';
				echo '</div>';
                                echo '</li>';
                        }
                ?>
                </ul>
                </div>

	</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
		<script>
			$(document).ready(function() {
				$('.collapsible').collapsible();
			});
		</script>
	</body>
</html>
