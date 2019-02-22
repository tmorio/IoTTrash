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
                                <a class="waves-effect waves-light btn" href="./getMenu.php"><i class="material-icons left">keyboard_arrow_left</i>依頼一覧に戻る</a>&thinsp;
				<button id="gpsEnable" class="btn waves-effect waves-light" disable="" onclick="GetRealTimePosition();"><i class="material-icons left">gps_fixed</i>現在地を追尾</button>
				<button id="gpsEnableC" class="btn waves-effect waves-light" onclick="nowPositionCheck();" disabled><i class="material-icons left">navigation</i>現在地を表示</button>
				<br><br>
				<span class="infoTitle">目的地一覧</span><br>
				マップ上のピンを選択すると回収完了操作を行えます。
			</li>

			<?php
				$DeviceCount = count($_SESSION['PostData']);
				$DeviceCounter = 0;
				$postID = 0;
				$PinData = "[";
				$Waypoint = "[";
				echo '<ul class="collapsible">';
				foreach($_SESSION['PostData'] as $data){ //データ件数だけ反復される
					//ピン緯度経度データ生成処理
					$PinData = $PinData . "{name:" . '\'<p>' . $data['Name'] . '</p><br><a class="waves-effect waves-light btn modal-trigger blue right" href="#modal1" onclick="CompleteGo(' . $postID. ')"><i class="material-icons left">check</i>回収済みにする</a>'  . '\''  . ",lat:" . $data['Lat'] . ",lng:" . $data['Lng'] . "}";
					$Waypoint = $Waypoint . "{location: new google.maps.LatLng(" . $data['Lat'] . "," . $data['Lng']  . ")}";
					if(($DeviceCount - 1) != $DeviceCounter){
						$PinData = $PinData . ",";
						$Waypoint = $Waypoint . ",";
						$DeviceCounter = $DeviceCounter + 1;
					}
		  			echo '<li>';
						if(!empty($data['Name'])){
		  						echo '<div class="collapsible-header">';
		  						echo $data['Name'] . "&nbsp;" . "(" .  $data['DeviceID'] . ")";
								echo '<div class="listButton">';
									echo '<a class="waves-effect waves-light btn right" onclick="buttonClick('.$data['Lat'].','.$data['Lng'].');return false;"><i class="material-icons left">location_on</i>表示</a>';
								echo '</div>';
						}
					echo '</li>';
					$postID++;
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
			var infoWindow = [];
			var pinData = <?php echo $PinData;?>;

			function initMap() {
				var request = {
					origin: new google.maps.LatLng(<?php echo $_GET['lat'] . "," . $_GET['lng']; ?>),
					destination: new google.maps.LatLng(<?php echo $_SESSION['endLat'] . "," . $_SESSION['endLng']; ?>),
					waypoints: <?php echo $Waypoint; ?>,
					travelMode: google.maps.DirectionsTravelMode.DRIVING,
					optimizeWaypoints: true,
					drivingOptions: {
						departureTime: new Date('<?php echo date('Y-m-d H:i:s', time()) ?>'),
						trafficModel: google.maps.TrafficModel.BEST_GUESS
					}
				};

                                map = new google.maps.Map(document.getElementById('boxMap'), {
                                        center: {<?php echo "lat:" . $_GET['lat'] . ",lng:" . $_GET['lng']; ?>},
                                        zoom: 18
                                });

				for (var i = 0; i < pinData.length; i++) {
					markerLatLng = new google.maps.LatLng({lat: pinData[i]['lat'], lng: pinData[i]['lng']});
					marker[i] = new google.maps.Marker({
						position: markerLatLng,
						map: map
					});

					infoWindow[i] = new google.maps.InfoWindow({
						content: '<div class="boxMap">' + pinData[i]['name'] + '</div>'
					});

					markerEvent(i);
				}

				function markerEvent(i) {
					marker[i].addListener('click', function() {
					infoWindow[i].open(map, marker[i]);
					});
				}

				var postInfo = new google.maps.DirectionsService();
				var searchRoute = new google.maps.DirectionsRenderer({
					map: map,
					preserveViewport: false,
					suppressMarkers: true,
					//draggable: true,
				});
				var callbackNum = postInfo.route(request, function(result, status){
					if (status == google.maps.DirectionsStatus.OK) {
						searchRoute.setDirections(result);
					}
					console.log(result);
				});

			}
			var nowPosition = null;
			var nowLat = null;
			var nowLng = null;

			function create_marker(options){
				var nowAreaPos =  new google.maps.Marker(options);
				return nowAreaPos;
			}

			function deleteNowPosition() {
		  		if(nowPosition != null){
		  			nowPosition.setMap(null);
		  		}
				nowPosition = null;
			}

			function GetRealTimePosition(){
				var btn = document.getElementById('gpsEnable');
				var btnC = document.getElementById('gpsEnableC');
				btn.setAttribute('disabled', 'disabled');
				btnC.removeAttribute('disabled');
				btn.innerHTML = '<i class="material-icons left">gps_fixed</i>追尾中';
				navigator.geolocation.watchPosition(
					function(position) {
						nowLat = position.coords.latitude;
						nowLng = position.coords.longitude;
						deleteNowPosition();
						map.panTo(new google.maps.LatLng(position.coords.latitude, position.coords.longitude));
						map.setZoom(19);
						nowPosition = create_marker({
							map: map,
							position: new google.maps.LatLng(position.coords.latitude, position.coords.longitude),
							icon: {
								path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
								scale: 8,
								rotation: position.coords.heading,
								fillColor: '#ff0000',
								fillOpacity: 1,
								strokeWeight: 0
							}
						});
					},
					function(error) {
						switch(error.code) {
							case 1:
								alert("位置情報の利用が許可されていません。\n権限をご確認下さい。");
								break;
							case 2:
								alert("現在位置が取得できませんでした。\n時間を空けてから再度お試し下さい。");
								break;
							case 3:
								alert("タイムアウトしました。\n時間を空けてから再度お試し下さい。");
								break;
							default:
								alert("原因不明エラーが発生しました。(Error Code:"+error.code+")");
								break;
						}
						btn.innerHTML = '<i class="material-icons left">gps_fixed</i>現在地を追尾';
						btn.removeAttribute('disabled');
					}
				);
			}

			function nowPositionCheck(){
				map.panTo(new google.maps.LatLng(nowLat,nowLng));
				map.setZoom(19);
			}

			function buttonClick(lat,lng) {
				map.panTo(new google.maps.LatLng(lat,lng));
				map.setZoom(19);
			}
			function CompleteGo(devID){
				var target = document.getElementById("postComplete");
				target.href = "doComplete.php?id=" + devID;
			}
		</script>
		<script src="https://maps.googleapis.com/maps/api/js?key=
		<?php echo MAP_API_KEY; ?>
		&callback=initMap"></script>

		<div id="modal1" class="modal">
			<div class="modal-content">
				<h4>回収確認</h4>
				<p>選択したデバイスを回収済みにします。よろしいですか?</p>
			</div>
			<div class="modal-footer">
                        	<a class="waves-effect waves-light modal-close btn red"><i class="material-icons left">close</i>キャンセル</a>
                        	<a id="postComplete" class="waves-effect waves-light btn blue" href=""><i class="material-icons left">check</i>回収済みにする</a>
			</div>
		</div>


	</div>
	<!-- デバッグ
	<?php echo "Debug: " . "JavaScript PinData : " . $PinData; ?> -->
        <!-- デバッグ
        <?php echo "Debug: " . "JavaScript PinData : " . $Waypoint; ?> -->
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

