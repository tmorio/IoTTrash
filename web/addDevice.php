<?php
session_start();

if(empty($_SESSION['userName'])){
	header("Location: login.php");
}

require_once('./myid.php');
require_once('./siteInfo.php');

?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MyBox Cloud - AddDevice</title>
		<link rel="stylesheet" type="text/css" href="css/materialize.min.css">
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
		<script type="text/javascript" src="js/materialize.min.js"></script>
		<script type="text/javascript" src="js/footerFixed.js"></script>
		<script>
			function setActive(n){
				$(".step-item").removeClass("active grey lighten-1");
				switch(n){
					case 0:
						$("#row_step0").addClass("active");
						break;
					case 1:
						$("#row_step0").addClass("grey lighten-1");
						$("#row_step1").addClass("active");
						break;
					case 2:
						$("#row_step0").addClass("grey lighten-1");
						$("#row_step1").addClass("grey lighten-1");
						$("#row_step2").addClass("active");
						break;
					case 3:
						$("#row_step0").addClass("grey lighten-1");
						$("#row_step1").addClass("grey lighten-1");
						$("#row_step2").addClass("grey lighten-1");
						$("#row_step3").addClass("active");
						break;
				}
			}
		</script>
	</head>
	<body>

	<?php require_once('./header.php'); ?>

	<!-- 表示画面 （Google Mapみたいに2画面分割で左にリスト、右にマップ?)-->
	<div class="deviceAdd">
	
		<!-- 設定分類一覧表示 -->
		<div class="collection with-header settingList">
			<div class="collection-header">
				<a class="waves-effect waves-light btn" href="./boxtool.php"><i class="material-icons left">keyboard_arrow_left</i>デバイス管理に戻る</a>
			</div>
			<div class="collection-header">
				<span class="infoTitle">デバイスの追加</span>
			</div>
			
			<a href="?step=0" class="collection-item grey-text text-darken-4 step-item" id="row_step0"> <i class="material-icons left">call_made</i>1. はじめに</a>
			
			<a href="?step=1" class="collection-item grey-text text-darken-4 step-item" id="row_step1"> <i class="material-icons left">edit_location</i>2. 位置情報の登録</a>
			
			<a href="?step=2" class="collection-item grey-text text-darken-4 step-item" id="row_step2"> <i class="material-icons left">edit</i>3. デバイス情報の登録</a>
			
			<div 			  class="collection-item grey-text text-darken-4 step-item" id="row_step3"> <i class="material-icons left">check</i>4. 登録完了</div>
			<div class="collection-item"></div>
		</div>
		<!-- ウィザード -->
		<div class="wizardInfo">
			
			<?php
				switch($_GET['step']){
					default:
						echo '
							<script>setActive(0);</script>
							<h3>1. デバイスをMyBox Cloudに登録する</h3>
							このウィザードでは、対応デバイスをMyBoxアカウントに紐づけることができます。<br>
							紐づけたデバイスは一括で管理・監視することができます。
							<h4>必要なもの</h4>
							
							<!--
							<li>Arduino (プログラムは<a href="">こちら</a>からダウンロードできます</li>
							<li>Sigfox Shield for Arduino</li>
							-->
							
							<li>MyBox サービス端末 (まだ電源は入れないで下さい。)</li>
							<br>
							<a class="waves-effect waves-light btn" href="?step=1">
								<i class="material-icons right">keyboard_arrow_right</i>
								次に進む
							</a>
						';
						break;
					case 1:
						$_SESSION['getStatus'] = 0;
						echo '
							<h3>2. 位置情報の登録</h3>
							
							<script>
								setActive(1);
								if (navigator.geolocation) {
									document.write("端末の位置情報サービスを利用してデバイスの位置情報を登録します。<br>そのため、位置情報の取得の許可をお願い致します。<br>");
								} else {
									document.write("この端末では位置情報の取得ができません。<br>手動で設定を行って下さい。<br>");
								}
								function getPosition() {
									document.getElementById("geoNotice").innerHTML = "取得中...しばらくお待ちください...";
									navigator.geolocation.getCurrentPosition(
										function(position) {
											document.location.href = "?step=2&lat=" + position.coords.latitude + "&lng=" + position.coords.longitude;
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
										}
									);
								}
							</script>
							<br>
							<h4>ご注意</h4>
							セキュリティ上、位置情報はデバイス登録後は変更できません。<br>
							必ず正しい位置を設定して下さい。(次のステップで微調整が行えます。)<br>
							<br>
							下のボタンを押すと位置情報を取得します。<br>
							※取得には時間がかかる場合が御座います。画面が切り替わるまでお待ちください。<br><br>
							<!-- 取得中の時はボタンに処理中って表示したいね -->
							<button class="btn waves-effect waves-light" onclick="getPosition();"><i class="material-icons left">gps_fixed</i>位置情報を取得して進む (自動設定)</button>
							<span id="geoNotice"></span>
							<br><br>
							<a class="waves-effect waves-light btn" href="?step=map&data=0">
								<i class="material-icons left">landscape</i>
								手動で位置を設定して進む
							</a>
							<br><br><br>
							<a class="waves-effect waves-light btn" href="addDevice.php">
								<i class="material-icons left">keyboard_arrow_left</i>
								前に戻る
							</a>
						';
						break;
					case 2:
						if(!empty($_GET['lat']) || !empty($_GET['lng'])){
							$_SESSION['getStatus'] = 1;
							$_SESSION['lat'] = $_GET['lat'];
							$_SESSION['lng'] = $_GET['lng'];
							header("Location: ?step=2") ;
						}
						
						if (isset($_POST["startRegister"])) {
							if(empty($_POST["deviceID"])) {
								echo "デバイスIDが入力されていません。<br>";
							}
							if(empty($_POST["nickname"])){
								echo "デバイスの表示名が入力されていません。<br>";
							}
							if(strlen($_POST["deviceID"]) != 6){
								echo "デバイスIDの値が正しい値ではありません。";
							}
							if(!empty($_POST["deviceID"]) && !empty($_POST["nickname"]) && (strlen($_POST["deviceID"]) == 6)){
								$_SESSION["deviceID"] = $_POST["deviceID"];
								$_SESSION["nickname"] = $_POST["nickname"];
								header("Location: ?step=process") ;
							}
						}
						
						if(empty($_SESSION['lat']) || empty($_SESSION['lng'])){
							header("Location: ?step=1");
						}
						$_SESSION['getStatus'] = 1;
						echo '
							<script>setActive(2);</script>
							<h3>3. デバイス情報の登録</h3>
							続いてデバイス情報の登録を行います。<br>
							SigfoxのデバイスID(QRコードの下にある6桁の英数字)と、サービス上で表示する名前を入力して下さい。<br>
							<br>
							<form id="registerDevice" name="registerDevice" action="" method="POST">
								<div class="cp_iptxt">
									<input type="text" id="deviceID" name="deviceID" pattern="^[0-9A-Za-z]+$" placeholder="Sigfox デバイスID (例:00AA00)" value="';
										if (!empty($_POST["deviceID"])) {echo htmlspecialchars($_POST["deviceID"], ENT_QUOTES);}
										echo '" required>
								</div>
								<div class="cp_iptxt">
									<input type="text" id="nickname" name="nickname" placeholder="デバイスの表示名 (例:A公園前)" value="';
										if (!empty($_POST["nickname"])) {echo htmlspecialchars($_POST["nickname"], ENT_QUOTES);}
										echo '" required>
								</div>
								
								<h4>位置情報</h4>
								<a class="waves-effect waves-light btn" href="?step=map&data=1">
									<i class="material-icons left">landscape</i>
									位置を修正する
								</a>
						';
						echo "緯度:" . $_SESSION['lat'] . " 経度:" . $_SESSION['lng'] . "<br>";
						echo '
								<script>
									var map;
									var marker;
									function initMap() {
										map = new google.maps.Map(document.getElementById("deviceArea"), {
											center: {lat:' .  $_SESSION['lat'] . ',lng:' . $_SESSION['lng'] . '},
											zoom: 19
										});
										markerLatLng = {lat:' . $_SESSION['lat'] . ', lng:' . $_SESSION['lng']  . '};
										marker = new google.maps.Marker({
											position: markerLatLng,
											map: map
										});
									}
								</script>
								<div id="deviceArea"></div>
								<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB_PqH61wln7u5GE0ycuekW1ePbjTfcSJE&callback=initMap"></script>
						';
						echo '
								<br>
								<button class="btn waves-effect waves-light" type="submit" id="startRegister" name="startRegister"><i class="material-icons left">check</i>以上の内容で登録する</button>
							</form>
							<br><br>
										<a class="waves-effect waves-light btn" href="?step=1"><i class="material-icons left">keyboard_arrow_left</i>前に戻る</a>';
						break;
					case 3:
						if(empty($_SESSION['lat']) || empty($_SESSION['lng'])){
							header("Location: ?step=1");
						}
						require_once('./myid.php');
						$strcode = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8mb4'");
						try {
							$dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_ID, DB_PASS, $strcode);
							$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
						} catch (PDOException $e) {
							echo $e->getMessage();
							exit;
						}
						$stmt = $dbh->prepare("INSERT INTO StatusData ( DeviceID, Owner, Latitude, Longitude, NickName ) VALUES (:deviceid, :owner, :latitude, :longitude, :nickname)");
						$stmt->bindParam(':deviceid', $_SESSION['deviceID'], PDO::PARAM_STR);
						$stmt->bindParam(':owner', $_SESSION['userNo'], PDO::PARAM_INT);
						$stmt->bindParam(':latitude', $_SESSION['lat'], PDO::PARAM_STR);
						$stmt->bindParam(':longitude', $_SESSION['lng'], PDO::PARAM_STR);
						$stmt->bindParam(':nickname', $_SESSION['nickname'], PDO::PARAM_STR);
						$stmt->execute();

						unset($_SESSION['lat']);
						unset($_SESSION['lng']);
						unset($_SESSION['getStatus']);
						unset($_SESSION['deviceID']);
						unset($_SESSION['nickname']);
                                                echo '
                                                        <script>setActive(3);</script>
							<h3>4. 完了</h3>
                                                        デバイスの登録が完了しました。<br>
                                                        ゴミ箱の中が空の状態である事をご確認の上、デバイスの電源を入れて設置して下さい。<br>
							データが送信されると情報が確認できるようになります。<br>
                                                        ※電源を入れてから最初のデータが表示されるまで、最大5分程度かかる場合が御座います。<br>
                                                        <br>
                                                        <a class="waves-effect waves-light btn" href="./addDevice.php">
                                                                <i class="material-icons left">add</i>
                                                                別のデバイスを登録する
                                                        </a>
                                                        <a class="waves-effect waves-light btn" href="./boxtool.php">
                                                                <i class="material-icons left">tap_and_play</i>
                                                                デバイス管理メニューに戻る
                                                        </a>
                                                        <a class="waves-effect waves-light btn" href="./dashboard.php">
                                                                <i class="material-icons left">dashboard</i>
                                                                ホームに戻る
                                                        </a>
                                                ';
                                                break;
					case map:
						if($_GET['data'] == 0){
                                                        $_SESSION['lat'] = 0;
                                                        $_SESSION['lng'] = 0;
                                                }
						echo '
							<script>setActive(1);</script>
							<h3>位置情報の手動設定</h3>
							デバイスを設置した位置をクリックしてください。<br>
							緯度: <span id="lat"></span>&nbsp;経度: <span id="lng"></span><br>
							';
						if($_GET['data'] == 0){
						echo '
							<a class="waves-effect waves-light btn" href="?step=1">
                                                                <i class="material-icons left">keyboard_arrow_left</i>
                                                                戻る
                                                        </a>
						';}else{
						echo '
                                                        <a class="waves-effect waves-light btn" href="?step=2">
                                                                <i class="material-icons left">keyboard_arrow_left</i>
                                                                戻る
                                                        </a>
							';
						}
						echo '
							<button class="btn" onclick="selectArea();">選択した位置に設定</button><br><br>
							<script>
								var marker;
								var clickCount = 0;
								var clickLat;
								var clickLng;
								var getLat = 0;
								var getLng = 0;
								function selectArea(){
									if((clickLat == null) &&(clickLng == null)){
										alert("位置が選択されていません。");
									}else{
										document.location.href = "?step=2&lat=" + clickLat + "&lng=" + clickLng;
									}
								}

								function initMap() {

									if(' . $_SESSION['getStatus'] . ' == 1){

										var map = new google.maps.Map(document.getElementById("mapSetting"), {
											zoom: 18,
											center: {lat: ' . $_SESSION['lat'] . ', lng: ' . $_SESSION['lng'] . '}
										});
									}else{
                                                                                var map = new google.maps.Map(document.getElementById("mapSetting"), {
                                                                                        zoom: 5.5,
                                                                                        center: {lat: 37.894788, lng: 135.999802}
                                                                                });
									}

									map.addListener(' . '\'' . "click" . '\'' .', function(e) {
										if((clickCount == 0) && (' . $_SESSION['getStatus'] . '!= 0)){
											marker.setMap(null);
										}
										if(clickCount != 0){
											marker.setMap(null);
										}
										getClickLatLng(e.latLng, map);
										if(clickCount == 0){
											clickCount++;
										}
									});

									if((clickCount == 0) && (' . $_SESSION['getStatus'] . ' == 1)){
                                                                        clickLat = ' . $_SESSION['lat'] . ';
                                                                        clickLng = ' . $_SESSION['lng'] . ';
                                                                        document.getElementById("lat").textContent = clickLat;
                                                                        document.getElementById("lng").textContent = clickLng;

										markerLatLng = {lat:' . $_SESSION['lat'] . ', lng:' . $_SESSION['lng']  . '};
																		marker = new google.maps.Marker({
																				position: markerLatLng,
																				map: map
																		});
									}

								}

								function getClickLatLng(lat_lng, map) {
									clickLat = lat_lng.lat();
									clickLng = lat_lng.lng();
									document.getElementById("lat").textContent = clickLat;
									document.getElementById("lng").textContent = clickLng;
									marker = new google.maps.Marker({
										position: lat_lng,
										map: map
									});
									map.panTo(lat_lng);
								}
							</script>
                                                        <div id="mapSetting"></div>
                                                        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB_PqH61wln7u5GE0ycuekW1ePbjTfcSJE&callback=initMap"></script>
						';
						break;
					case process:
                                                if(empty($_SESSION['lat']) || empty($_SESSION['lng'])){
                                                        header("Location: ?step=1");
                                                }
                                                if(empty($_SESSION['deviceID']) || empty($_SESSION['nickname'])){
                                                        header("Location: ?step=2");
                                                }
						echo '
							<script>setActive(2);</script>
							<h3>登録処理中...</h3>
							現在、アカウントにデバイスを登録しています。画面が切り替わるまでしばらくお待ちください。
							<META http-equiv="Refresh" content="1;URL=?step=3">

						';
						break;
				}
			?>

		</div>
	</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
                        <?php echo FOOTER_INFO; ?>
		</footer>
	</body>
</html>

