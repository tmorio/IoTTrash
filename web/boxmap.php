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

$query = "SELECT COUNT(*) FROM StatusData WHERE Owner = :UserID";

$stmt = $dbh->prepare($query);
$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
$stmt->execute();
$DeviceCount = $stmt->fetchColumn();

$query = "SELECT * FROM StatusData WHERE Owner = :UserID";

$stmt = $dbh->prepare($query);
$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
$stmt->execute();
?>
<?php
// 0<=(h)<=360, 0<=(s,v)<=255
// return:string
function hsv2code($h,$s,$v){
	$max = $v;
	$min = ($max-(($s/255)*$max));
	$r=0;
	$g=0;
	$b=0;
	if(0<=$h && $h<60){
		$r=$max;
		$g=(int)(($h/60)*($max-$min)+$min);
		$b=$min;
	}
	if(60<=$h && $h<120){
		$r=(int)(((120-$h)/60)*($max-$min)+$min);
		$g=$max;
		$b=$min;
	}
	if(120<=$h && $h<180){
		$r=$min;
		$g=$max;
		$b=(int)((($h-120)/60)*($max-$min)+$min);
	}
	if(180<=$h && $h<240){
		$r=$min;
		$g=(int)(((240-$h)/60)*($max-$min)+$min);
		$b=$max;
	}
	if(240<=$h && $h<300){
		$r=(int)((($h-240)/60)*($max-$min)+$min);
		$g=$min;
		$b=$max;
	}
	if(300<=$h && $h<=360){
		$r=$max;
		$g=$min;
		$b=(int)(((360-$h)/60)*($max-$min)+$min);
	}
	//echo $h.",".$s.",".$v."<br>\n";
	//echo $r.",".$g.",".$b."<br>\n";
	//echo sprintf('%06x',$r*0x10000+$g*0x100+$b);
	return sprintf('%06x',$r*0x10000+$g*0x100+$b);
}
function map($x, $iMin, $iMax, $oMin, $oMax){
	return (int)(($x-$iMin)*($oMax-$oMin)/($iMax-$iMin)+$oMin);
}
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MyBox Cloud - BoxMap</title>
		<link rel="stylesheet" type="text/css" href="css/materialize.min.css">
		<link rel="stylesheet" type="text/css" href="css/style.css?">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
		<script type="text/javascript" src="js/materialize.min.js"></script>
		<script type="text/javascript" src="js/footerFixed.js"></script>
		<!-- <link rel="stylesheet" type="text/css" href="style.css"> -->
		<script src="https://use.fontawesome.com/12725d4110.js"></script>
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
	
	<!-- 表示画面 （Google Mapみたいに2画面分割で左にリスト、右にマップ?)-->
	<div class="mapBoard">
	
		<!-- ゴミ箱一覧表示 -->
		<ul class="collection with-header trashList">
			<li class="collection-header">
                                <a class="waves-effect waves-light btn" href="./dashboard.php"><i class="material-icons left">keyboard_arrow_left</i>ホームに戻る</a>
				&nbsp;
                                <a class="waves-effect waves-light btn" href="./boxmap.php"><i class="material-icons left">loop</i>最新のデータを読み込む</a>
				<br><br>
				<span class="infoTitle">デバイス一覧</span>
			</li>

				<form action="doBoxget.php" method="POST">

			<?php
				$DeviceCounter = 0;
				$PinData = "[";
				foreach($stmt as $data){ //データ件数だけ反復される
					//ピン緯度経度データ生成処理
					$PinData = $PinData . "{name:'" . $data['DeviceID'] . "',lat:" . $data['Latitude'] . ",lng:" . $data['Longitude'] . "}";
					if(($DeviceCount - 1) != $DeviceCounter){
						$PinData = $PinData . ",";
						$DeviceCounter = $DeviceCounter + 1;
					}
				
				//$Red=255-$data['Dis'];
				//$Green=100+$data['Dis'];
				//$Blue=175;
				//if($Red<0) $Red=0;
				//if($Green>255) $Green=255;
				//$color=$Red*0x10000+$Green*0x100+$Blue;
				
				// red     green
				// 0<=(h)<=130
				$max=120; //cm
				$min=0; //cm
				$h=map($data['Dis'],$min,$max,0,130);
				$s=120;
				$v=255;
				$color=hsv2code($h,$s,$v);

		  echo '<li class="DeviceInfo collection-item" style="background-color: #'.$color.';">'; //各デバイスの情報が入るブロック
		  echo "" .  $data['NickName'] . "&nbsp;";
		  echo "(" .  $data['DeviceID'] . ")";
                  echo '<button class="waves-effect waves-light btn" onclick="buttonClick(' .  $data['Latitude'] . ',' . $data['Longitude'] . ');return false;"><i class="material-icons left">location_on</i>マップに表示</button>';

                  echo '<hr size="1" color="#37474f" noshade>';

		  if(empty($data['Time'])){
			echo "更新日時: 未取得";
		  }else{
		  	echo "更新日時: " . $data['Time'];
		  }

		  echo '<div class="DeviceSensor">';

		  if(empty($data['Temp']) || empty($data['Hum'])){
			echo "データ取得待ち";
		  }else{
		  	echo "温度: " . $data['Temp'] . "°C" . " 湿度: " . $data['Hum'] . "%";
		  }
		  echo '</div>';
		  echo '<div class="BoxAvailable">';

		  if(!empty($data['Dis'])){
			  echo "空き容量: " . $data['Dis'] . " cm";
		  }

		  echo '</div>';
		  echo '<br>';
		  if($data['gettingStatus'] == 0){
		  	echo '<label><input type="checkbox" name="boxes[]" value="' . $data['DeviceID'] . '" class="filled-in" /><span>回収対象にする</span></label>';
                        if(!empty($data['LastReset'])){
                                echo '&nbsp;最終回収 :&nbsp;' . $data['LastReset'];
                        }
		  }else{
			echo '<label><input type="checkbox" checked="checked" disabled="disabled" /><span>回収依頼済み</span></label>';
			if(!empty($data['LastReset'])){
				echo '&nbsp;最終回収 :&nbsp;' . $data['LastReset'];
			}
		  }
		  echo '</li><br>';
				}
				$PinData = $PinData . "]";
			?>
			</form>
		</ul>

		<!-- マップ表示 -->
		<div id="boxMap">
		</div>
		<script>
			var map;
			var marker = [];
			var pinData = <?php echo $PinData;?>;
			function initMap() {
				map = new google.maps.Map(document.getElementById('boxMap'), {
					center: {lat: pinData[0]['lat'], lng: pinData[0]['lng']},
					zoom: 18
				});
				for (var loopCount = 0; loopCount < pinData.length; loopCount++) {
					markerArea = {lat: pinData[loopCount]['lat'], lng: pinData[loopCount]['lng']};
					marker[loopCount] = new google.maps.Marker({
						position: markerArea,
						map: map
					});
				}
			}

			function buttonClick(lat,lng) {
				map.panTo(new google.maps.LatLng(lat,lng));
			}
		</script>
		<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB_PqH61wln7u5GE0ycuekW1ePbjTfcSJE&callback=initMap"></script>
	</div>
	<!-- デバッグ
	<?php echo "Debug: Counter: " . $DeviceCount . " JavaScript PinData : " . $PinData; ?> -->
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
	</body>
</html>

