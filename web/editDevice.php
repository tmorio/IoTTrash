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
	if(!empty($_POST['searchKey'])){
		$query = "SELECT * FROM StatusData WHERE (Owner = :UserID OR GroupID = :usergroup) AND ((DeviceID LIKE :searchWordA) OR (NickName LIKE :searchWordB))";
	}else{
        	$query = "SELECT * FROM StatusData WHERE Owner = :UserID OR GroupID = :usergroup";
	}
}else{
	if(!empty($_POST['searchKey'])){
		$query = "SELECT * FROM StatusData WHERE Owner = :UserID AND ((DeviceID LIKE :searchWordA) OR (NickName LIKE :searchWordB))";
	}else{
        	$query = "SELECT * FROM StatusData WHERE Owner = :UserID";
	}
}

$stmt = $dbh->prepare($query);
$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
if(!empty($_SESSION['userGroup'])){
        $stmt->bindParam(':usergroup', $_SESSION['userGroup'], PDO::PARAM_INT);
}

if(!empty($_POST['searchKey'])){
	$SearchWord = "%" . $_POST['searchKey'] . "%";
        $stmt->bindParam(':searchWordA', $SearchWord, PDO::PARAM_STR);
        $stmt->bindParam(':searchWordB', $SearchWord, PDO::PARAM_STR);
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
	<?php require_once('./header.php'); ?>
	<div class="deviceListBoard">
        	<a class="waves-effect waves-light btn" href="./boxtool.php">
        		<i class="material-icons left">keyboard_arrow_left</i>デバイス管理に戻る
        	</a>
                <?php
                        echo '<span class="listTitle">';
                        if(!empty($_POST['searchKey'])){
                                echo "検索結果&nbsp;:&nbsp;" . htmlspecialchars($_POST['searchKey'], ENT_QUOTES, 'UTF-8');
                        }else{
                                echo "編集・削除";
                        }
                        echo '</span>';

                ?>
                <a class="waves-effect waves-light btn" href="./addDevice.php">
                        <i class="material-icons left">add</i>デバイス追加
                </a>
                <a class="waves-effect waves-light btn" href="deviceList.php">
                        <i class="material-icons left">equalizer</i>一覧・分析
                </a>
                <a class="waves-effect waves-light btn modal-trigger" href="#modal1">
                        <i class="material-icons left">search</i>検索
                </a>
                <?php
                        if(!empty($_POST['searchKey'])){
                                echo '<a class="waves-effect waves-light btn modal-trigger red" href="editDevice.php"><i class="material-icons left">clear_all</i>全デバイスを表示</a>';
                        }
                ?>

		<?php if($_GET['Error'] == 1){ echo '<br><br><span class="editError">（編集エラー）入力されたデバイスIDが6文字ではありません。</span>';} ?>
                <div class="listOutput">
                <ul class="collapsible">
                <?php
			//ここに適用したい (jsについては<head>のコメント参照)

                        foreach($stmt as $data){
                                echo '<li><div class="collapsible-header">';
                                echo htmlspecialchars($data['NickName'], ENT_QUOTES, 'UTF-8');
                                echo "&nbsp;(" .  htmlspecialchars($data['DeviceID'], ENT_QUOTES, 'UTF-8') . ")<br>";

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
						<form action="devEdit.php?Device=' . htmlspecialchars($data['DeviceID'], ENT_QUOTES, 'UTF-8') . '" method="POST">
							デバイス名<br>
							<input type="text" name="NickName" id="NickName" value="' . htmlspecialchars($data['NickName'], ENT_QUOTES, 'UTF-8') . '" required>
							デバイスID<br>
							<input type="text" name="DeviceID" id="DeviceID" pattern="^[0-9A-Za-z]+$" value="' . htmlspecialchars($data['DeviceID'], ENT_QUOTES, 'UTF-8') . '" required>
							<br><br>
							<button class="btn waves-effect waves-light" type="submit"><i class="material-icons right">check</i>編集を適用する</button>
							<a class="waves-effect waves-light btn listButton red" href="delete.php?Device=' . htmlspecialchars($data['DeviceID'], ENT_QUOTES, 'UTF-8') . '"><i class="material-icons right">delete</i>削除</a>
						</form>
					</div>
				';
				echo '</div>';
                                echo '</li>';
                        }
                ?>
                </ul>
                </div>
        <div id="modal1" class="modal">
                <form action="editDevice.php" method="POST">
                <div class="modal-content">
                        <h4>デバイス検索</h4>
                        <p>検索したいキーワードを入力して下さい。 (デバイス名やデバイスID)</p>
                        <div class="row">
                                <div class="input-field col s12">
                                        <input id="searchKey" name="searchKey" type="text" class="validate" required>
                                        <label for="searchKey">検索キーワード</label>
                                </div>
                        </div>
                </div>
                <div class="modal-footer">
                        <a class="waves-effect waves-light modal-close btn red"><i class="material-icons left">close</i>キャンセル</a>
                        <button type="submit" class="waves-effect waves-light btn" href=""><i class="material-icons left">search</i>検索</button>
                </div>
                </form>
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
                        $(document).ready(function(){
                        	$('.modal').modal();
                        });
		</script>
	</body>
</html>
