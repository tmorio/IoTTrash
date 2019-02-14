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
	$query = "SELECT COUNT(*) FROM StatusData WHERE Owner = :UserID OR GroupID = :usergroup";
}else{
	$query = "SELECT COUNT(*) FROM StatusData WHERE Owner = :UserID";
}

$stmt = $dbh->prepare($query);
$stmt->bindParam(':UserID', $_SESSION['userNo'], PDO::PARAM_INT);
if(!empty($_SESSION['userGroup'])){
	$stmt->bindParam(':usergroup', $_SESSION['userGroup'], PDO::PARAM_STR);
}
$stmt->execute();
$DeviceCount = $stmt->fetchColumn();

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
	
	<!-- 表示画面 （Google Mapみたいに2画面分割で左にリスト、右にマップ?)-->
	<div class="mapBoard">
	
		<!-- ゴミ箱一覧表示 -->
		<ul class="collection with-header trashList">
			<li class="collection-header">
				<form action="doBoxget.php" method="POST">
                                <a class="waves-effect waves-light btn" href="./dashboard.php"><i class="material-icons left">keyboard_arrow_left</i>ホームに戻る</a>
				&nbsp;
                                <a class="waves-effect waves-light btn" href="./boxmap.php"><i class="material-icons left">loop</i>更新</a>
				&nbsp;
				<button class="btn waves-effect waves-light" type="submit" name="action">回収依頼送信<i class="material-icons right">send</i></button>
				<br><br>
				<span class="infoTitle">デバイス一覧</span>
			</li>

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
				
				// red     green
				// 0<=(h)<=130
				$max=120; //cm
				$min=0; //cm
				$h=map($data['Dis'],$min,$max,0,130);
				$s=130;
				$v=255;
				$color=hsv2code($h,$s,$v);

		  echo '<li class="DeviceInfo collection-item" style="background-color: #'.$color.';">'; //各デバイスの情報が入るブロック
		  echo '<div class="clearfix valign-wrapper">';
		  echo "" .  $data['NickName'] . "&nbsp;";
		  echo "(" .  $data['DeviceID'] . ")";
		  echo '<div class="right">';
                  echo '<button class="waves-effect waves-light btn right marg" onclick="buttonClick('.$data['Latitude'].','.$data['Longitude'].');return false;"><i class="material-icons left">location_on</i>表示</button>&thinsp;';
		  if(!empty($data['Temp'])){
			if($_SESSION['userService'] != 1){
                  		echo '<a class="waves-effect waves-light btn right marg" href="makeGraph.php?DeviceID='.$data['DeviceID'].'&from=1"><i class="material-icons left">timeline</i>分析</a>&thinsp;';
			}
		  	//echo '<a class="waves-effect waves-light btn" href="#?DeviceID='.$data['DeviceID'].'"><i class="material-icons left">check</i>回収済みにする</a>';
		  }else{
			if($_SESSION['userService'] != 1){
				echo '<a class="waves-effect waves-light btn right marg disabled"><i class="material-icons left">timeline</i>分析</a>&thinsp;';
			}
			//echo '<a class="waves-effect waves-light btn disabled"><i class="material-icons left">check</i>回収済みにする</a>';
		  }
		  echo '</div></div>';
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
                  if(!empty($data['Dis'])){
                          echo "<br>";
                  }
		  if($data['OrderStatus'] == 0){
			if($_SESSION['userService'] != 1){
                                if(!empty($data['Temp'])){
		  			echo '<label class="waves-effect waves-light btn cyan lighten-1"><input type="checkbox" value="#"><span>回収対象にする</span></label>';
				}
			}
                        if(!empty($data['LastReset'])){
                                //echo '&nbsp;最終回収 :&nbsp;' . $data['LastReset'];
                        }
		  }else{
			echo '<label class="waves-effect waves-light btn blue"><input type="checkbox" checked="checked" disabled="disabled"><span>回収依頼済み</span></label>';
			if(!empty($data['LastReset'])){
				//echo '&nbsp;最終回収 :&nbsp;' . $data['LastReset'];
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

