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

$query = "SELECT * FROM StatusData WHERE DeviceID = :devid AND Owner = :usernum";

$stmt = $dbh->prepare($query);
$stmt->bindParam(':devid', $_GET['DeviceID'], PDO::PARAM_STR);
$stmt->bindParam(':usernum', $_SESSION['userNo'], PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch();

?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MyBox Cloud - BoxAdmin</title>
		<link rel="stylesheet" type="text/css" href="css/materialize.min.css">
		<link rel="stylesheet" type="text/css" href="css/style.css?Ver=2">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
		<script type="text/javascript" src="js/materialize.min.js"></script>
		<script type="text/javascript" src="js/footerFixed.js"></script>
		<link rel="stylesheet" href="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.css">
		<script src="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.js"></script>
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
					<li><a class="waves-effect waves-light btn" href="./logout.php">ログアウト</a></li>
				</ul>
			</div>
		</nav>
	</div>
	<div class="deviceListBoard">
		<?php
		if($_GET['from'] != 1){
			echo '
        		<a class="waves-effect waves-light btn" href="./deviceList.php">
        			<i class="material-icons left">keyboard_arrow_left</i>一覧・分析に戻る
        		</a>
			';
		}else{
			echo '
                        <a class="waves-effect waves-light btn" href="./boxmap.php">
                                <i class="material-icons left">keyboard_arrow_left</i>地図に戻る
                        </a>
			';
		}
		?>
		<span class="listTitle">ID:<?php echo $_GET['DeviceID'] . 'のデータ履歴'; ?></span>
                <a class="waves-effect waves-light btn" href="#">
                        <i class="material-icons left">vertical_align_bottom</i>全データ書き出し (CSV)
                </a>
                <a class="waves-effect waves-light btn red" href="#">
                        <i class="material-icons left">highlight_off</i>データ履歴の削除
                </a>

		<div class="graphArea">
			<a class='dropdown-trigger btn' href='#' data-target='dropdown1'><i class="material-icons left">keyboard_arrow_down</i>時間の間隔 
			<?php
				switch($_GET['time']){
					default:
						echo '(15分毎)';
						break;
					case 1:
						echo '(30分毎)';
						break;
					case 2:
						echo '(1時間毎)';
						break;
					case 3:
						echo '(3時間毎)';
						break;
					case 4:
						echo '(6時間毎)';
						break;
					case 5:
						echo '(8時間毎)';
						break;
				}
			?>

			</a>
			<ul id='dropdown1' class='dropdown-content'>
			<?php
				echo '<li><a href="?DeviceID=' . $_GET['DeviceID']  . '&time=0">15分毎</a></li>';
				echo '<li><a href="?DeviceID=' . $_GET['DeviceID']  . '&time=1">30分毎</a></li>';
				echo '<li><a href="?DeviceID=' . $_GET['DeviceID']  . '&time=2">1時間毎</a></li>';
                                echo '<li><a href="?DeviceID=' . $_GET['DeviceID']  . '&time=3">3時間毎</a></li>';
                                echo '<li><a href="?DeviceID=' . $_GET['DeviceID']  . '&time=4">6時間毎</a></li>';
                                echo '<li><a href="?DeviceID=' . $_GET['DeviceID']  . '&time=5">8時間毎</a></li>';
			?>
			</ul>
			<br><br>
			<?php
                                echo "<h5>" . $result['NickName'] . "</h5><br>縦:空き容量[cm]&nbsp;横:時間";
                                echo '<br><br>';

                                switch($_GET['time']){
                                        default:
						$dataCounter = 1;
                                                $stopCounter = 10;
						$passNum = 1;
                                                break;
					case 1:
						$dataCounter = 2;
						$stopCounter = 22;
						$passNum = 2;
						break;
                                        case 2:
                                                $dataCounter = 4;
                                                $stopCounter = 40;
                                                $passNum = 4;
                                                break;
                                        case 3:
                                                $dataCounter = 12;
                                                $stopCounter = 120;
                                                $passNum = 12;
                                                break;
                                        case 4:
                                                $dataCounter = 24;
                                                $stopCounter = 240;
                                                $passNum = 24;
                                                break;
                                        case 5:
                                                $dataCounter = 32;
                                                $stopCounter = 320;
                                                $passNum = 32;
                                                break;
                                }

                                $query = "select * from `History` WHERE DeviceID = :devid order by `Time` desc limit 0,:getNum";

                                $stmt = $dbh->prepare($query);
                                $stmt->bindParam(':devid', $_GET['DeviceID'], PDO::PARAM_STR);
                                $stmt->bindParam(':getNum', $stopCounter, PDO::PARAM_INT);
                                $stmt->execute();
                                $result = $stmt->fetchAll();
                                $result = array_reverse($result, true);

                                $maxNum = 0;
                                $minNum = 999;
                                $graphData = '[';
                                $label = '[';

                                foreach($result as $data){
					if(($dataCounter % $passNum) == 0){
                                        	$label = $label . "'" . $data['Time'] . "'";
                                        	$graphData = $graphData . $data['Dis'];
						$addData = 1;
						if($minNum > $data['Dis']){ $minNum = $data['Dis']; }
						if($maxNum < $data['Dis']){ $maxNum = $data['Dis']; }
					}

                                        $dataCounter++;

                                        if($dataCounter == ($stopCounter + $passNum)){
                                                break;
                                        }else{
						if($addData == 1){
                                                $label = $label . ",";
                                                $graphData = $graphData . ",";
						}
                                        }

					$addData = 0;

                                }
                                $label = $label . "]";
                                $graphData = $graphData . "]";

			?>
			<div class="ct-chart"></div>
			<script>
			var data = {
				labels: <?php echo $label; ?>,
				series: [<?php echo $graphData; ?>]
			};

			var options = {
				height: 500,
				high: <?php echo $maxNum + 5; ?>,
				low: <?php echo $minNum - 5; ?>
			};

			new Chartist.Line('.ct-chart', data, options);
		</script>

		</div>
	</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
		<script>
			$('.dropdown-trigger').dropdown();
		</script>
	</body>
</html>

