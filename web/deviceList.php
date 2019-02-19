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

if(!empty($_POST['searchKey'])){
        $SearchWord = "%" . $_POST['searchKey'] . "%";
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
        $stmt->bindParam(':searchWordA', $SearchWord, PDO::PARAM_STR);
        $stmt->bindParam(':searchWordB', $SearchWord, PDO::PARAM_STR);

}

$stmt->execute();

?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MyBox Cloud - DeviceList</title>
		<link rel="stylesheet" type="text/css" href="css/materialize.min.css">
		<link rel="stylesheet" type="text/css" href="css/style.css?Ver=2">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
		<script type="text/javascript" src="js/materialize.min.js"></script>
		<script type="text/javascript" src="js/footerFixed.js"></script>
		<!-- <link rel="stylesheet" type="text/css" href="style.css"> -->
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
                <?php
                        echo '<span class="listTitle">';
                        if(!empty($_POST['searchKey'])){
                                echo "検索結果&nbsp;:&nbsp;" . htmlspecialchars($_POST['searchKey'], ENT_QUOTES, 'UTF-8');
                        }else{
                                echo "一覧・分析";
                        }
                        echo '</span>';

                ?>
                <a class="waves-effect waves-light btn" href="./addDevice.php">
                        <i class="material-icons left">add</i>デバイス追加
                </a>
                <a class="waves-effect waves-light btn" href="./editDevice.php">
			<i class="material-icons left">edit</i>編集・削除
		</a>
                <a class="waves-effect waves-light btn modal-trigger" href="#modal1">
                        <i class="material-icons left">search</i>検索
                </a>
                <?php
                        if(!empty($_POST['searchKey'])){
                                echo '<a class="waves-effect waves-light btn modal-trigger red" href="deviceList.php"><i class="material-icons left">clear_all</i>全デバイスを表示</a>';
                        }
                ?>

		<div class="listOutput">
		<ul class="collapsible">
		<?php
			foreach($stmt as $data){
				echo '<li><div class="collapsible-header">';
				echo $data['NickName'] ;
				echo "&nbsp;(" .  $data['DeviceID'] . ")<br>";

				if(empty($data['Time'])){
					echo "更新日時: 未取得";
				}else{
					echo "更新日時: " . $data['Time'];
				}
                                echo '<div class="listButton">';
				if(!empty($data['Time'])){
                                	echo '<a class="waves-effect waves-light btn" href="makeGraph.php?DeviceID=' . $data['DeviceID'] . '"><i class="material-icons left">timeline</i>グラフ表示</a>';
				}else{
					echo '<a class="waves-effect waves-light btn disabled"><i class="material-icons left">timeline</i>グラフ表示</a>';
				}
				echo '</div>';
				echo '</div></li>';
			}
		?>
		</ul>
		</div>
        <div id="modal1" class="modal">
                <form action="deviceList.php" method="POST">
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
                        <script>
                                $(document).ready(function(){
                                        $('.modal').modal();
                                });
                        </script>
		</footer>
	</body>
</html>

