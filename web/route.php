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
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MyBox Cloud - Route</title>
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
                                <a class="waves-effect waves-light btn" href="./getMenu.php"><i class="material-icons left">keyboard_arrow_left</i>依頼一覧に戻る</a>
				<br><br>
				<span class="infoTitle">目的地一覧</span><br>
				デバイス名を選択すると操作を行えます。
			</li>

			<?php
				$DeviceCount = count($_SESSION['PostData']);
				$DeviceCounter = 0;
				$ListCount = 1;
				$PinData = "[";
				$Waypoint = "[";
				echo '<ul class="collapsible">';
				foreach($_SESSION['PostData'] as $data){ //データ件数だけ反復される
					//ピン緯度経度データ生成処理
					$PinData = $PinData . "{name:'" . $data['DeviceID'] . "',lat:" . $data['Lat'] . ",lng:" . $data['Lng'] . "}";
					$Waypoint = $Waypoint . "{location: new google.maps.LatLng(" . $data['Lat'] . "," . $data['Lng']  . ")}";
					if(($DeviceCount - 1) != $DeviceCounter){
						$PinData = $PinData . ",";
						$Waypoint = $Waypoint . ",";
						$DeviceCounter = $DeviceCounter + 1;
					}
		  			echo '<li>';
		  				echo '<div class="collapsible-header">';
		  					echo '<div class="clearfix valign-wrapper">';
		  						echo $ListCount . ".&nbsp;" .  $data['Name'] . "&nbsp;" . "(" .  $data['DeviceID'] . ")";
		  					echo '</div>';
                                                        if($ListCount == 1){
                                                                echo '<span class="new badge blue" data-badge-caption="">次の目的地</span>';
                                                        }
						echo '</div>';
		 				echo '<div class="collapsible-body">';
							echo '<button class="waves-effect waves-light btn" onclick="buttonClick('.$data['Lat'].','.$data['Lng'].');return false;"><i class="material-icons left">location_on</i>中央に表示</button>&thinsp;';
                                			echo '<a class="waves-effect waves-light btn blue right" href="#"><i class="material-icons left">check</i>回収済みにする</a>';
						echo '</div>';
					echo '</li>';
					$ListCount++;
				}
				echo '</ul>';
				$PinData = $PinData . "]";
				$Waypoint = $Waypoint . "]";
			?>
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
	<?php echo "Debug: " . "JavaScript PinData : " . $PinData; ?> -->
        <!-- デバッグ
        <?php echo "Debug: " . "JavaScript PinData : " . $Waypoint; ?> -->
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
			<script>
                        	$(document).ready(function() {
                                	$('.collapsible').collapsible();
                        	});
                	</script>
		</footer>
	</body>
</html>

