<?php
session_start();

if(empty($_SESSION['userName'])){
	header("Location: login.php");
}

require_once('./myid.php');
require_once('./siteInfo.php');
require_once('./hsv2code.php');

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
		<script type="text/javascript" src="js/progressbar.min.js"></script>
		<!-- <link rel="stylesheet" type="text/css" href="style.css"> -->
	</head>
	<body>

	<?php require_once('./header.php'); ?>

<script type="text/javascript">
	var Count = 0;
	function checkValue(check){
		var btn = document.getElementById('sendB');

		if (check.checked) {
			btn.removeAttribute('disabled');
			Count++;
		} else {
			Count--;
			if(Count == 0){
				btn.setAttribute('disabled', 'disabled');
			}
		}
	}
</script>

	<!-- 表示画面 （Google Mapみたいに2画面分割で左にリスト、右にマップ?)-->
	<div class="mapBoard">
	
		<!-- ゴミ箱一覧表示 -->
		<ul class="collection with-header trashList">
			<li class="collection-header">
				<form action="doBoxGet.php" method="POST">
                                <a class="waves-effect waves-light btn" href="./dashboard.php"><i class="material-icons left">keyboard_arrow_left</i>ホームに戻る</a>
				&nbsp;
                                <a class="waves-effect waves-light btn" href="./boxmap.php"><i class="material-icons left">loop</i>更新</a>
				&nbsp;
				<?php
					if($_SESSION['userService'] != 1){
						echo '<button id="sendB" class="btn waves-effect waves-light" type="submit" name="action" disabled="disabled">回収依頼送信<i class="material-icons right">send</i></button>';
					}
				?>
				<br><br>
				<span class="infoTitle">デバイス一覧</span>
				<br>
				デバイス名を選択すると詳細情報を見ることができます。
			</li>

			<?php
				$DeviceCounter = 0;
				$PinData = "[";
				echo '<ul class="collapsible">';
				$num=0;
				foreach($stmt as $data){ //データ件数だけ反復される
					//ピン緯度経度データ生成処理
					$PinData = $PinData . "{name:'" . $data['DeviceID'] . "',lat:" . $data['Latitude'] . ",lng:" . $data['Longitude'] . "}";
					if(($DeviceCount - 1) != $DeviceCounter){
						$PinData = $PinData . ",";
						$DeviceCounter = $DeviceCounter + 1;
					}
					$capList[$num] = ($data['MaxADis'] - $data['Dis']) / $data['MaxADis']; // ここゴミ箱の最大値が必要
					if(empty($data['Time'])){
						$capList[$num]=null;
					}
					if($capList[$num]<0){
						$capList[$num]=1.0;
					}
					echo '<li>';
						echo '<div class="collapsible-header">';
							echo '<span>';
								echo "" .  htmlspecialchars($data['NickName'], ENT_QUOTES, 'UTF-8') . "&nbsp;" . "(" .  htmlspecialchars($data['DeviceID'], ENT_QUOTES, 'UTF-8') . ")";
							echo '</span>';
							echo '<span class="badge">';
								//echo '<span class="prog" id="progress'.$num.'"></span>';
								//$num++;
								if($data['OrderStatus'] == 1){
									echo '<span class="new badge blue coltag" data-badge-caption="">回収依頼済み</span>';
								}else{
									if(((($data['MaxADis'] - $data['Dis']) * 100 / $data['MaxADis']) >= 80) && !empty($data['Time']) && ($data['DevInfo'] != 1)){
										echo '<span class="new badge red coltag" data-badge-caption="">空き残量少</span>';
									}
									if(($data['WarSM'] == 1) && !empty($data['Time']) && !empty($data['Dis'])){
										echo '<span class="new badge orange coltag" data-badge-caption="">臭い警告</span>';
									}
									if(($data['DevInfo'] == 1) && !empty($data['Time']) && empty($data['Dis'])){
										echo '<span class="new badge coltag" data-badge-caption="">回収完了</span>';
									}
								}
								if(!empty($data['Dis'])){
									echo '<span class="new prog" id="progress'.$num.'"></span>';
									$num++;
									echo '</span>';
								}
						echo '</div>';
						echo '<div class="collapsible-body">';
							if(empty($data['Time'])){
								echo "更新日時: 未取得";
							}else{
								echo "更新日時: " . $data['Time'];
							}

							echo '<br>';

		  					if(empty($data['Temp']) || empty($data['Hum'])){
								echo "データ取得待ち";
		  					}else{
		  						echo "温度: " . $data['Temp'] . "°C" . " 湿度: " . $data['Hum'] . "%";
		  					}

							echo '<br>';

		  					if(!empty($data['Dis'])){
			  					echo "空き容量: " . $data['Dis'] . " cm";
		  					}else{
								echo "空き容量: 再取得待ち";
							}
							echo '<br><br>';
							echo '<button class="waves-effect waves-light btn" onclick="buttonClick('.$data['Latitude'].','.$data['Longitude'].');return false;"><i class="material-icons left">location_on</i>表示</button>&thinsp;';

		  					if(!empty($data['Temp'])){
                        					if($_SESSION['userService'] != 1){
                                					echo '<a class="waves-effect waves-light btn" href="makeGraph.php?DeviceID='.$data['DeviceID'].'&from=1"><i class="material-icons left">timeline</i>分析</a>&thinsp;';
                        					}
                  					}

		  					echo '&nbsp;';
		  					if($data['OrderStatus'] == 0){
								if($_SESSION['userService'] != 1){
                                					if(!empty($data['Temp']) && !empty($data['Dis'])){
		  								echo '<label class="waves-effect waves-light btn cyan lighten-1"><input type="checkbox" name="Devices[]" value="' . htmlspecialchars($data['DeviceID'], ENT_QUOTES, 'UTF-8') . '" onclick="checkValue(this)"><span>回収対象にする</span></label>';
									}else{
										echo '<label class="waves-effect waves-light btn blue"><input type="checkbox" checked="checked" disabled="disabled"><span>データ取得待ち</span></label>';
									}
								}
		  					}else{
									echo '<label class="waves-effect waves-light btn blue"><input type="checkbox" checked="checked" disabled="disabled"><span>回収依頼済み</span></label>';
		  					}

							if(!empty($data['LastReset'])){
								echo '<br><br>';
								echo '最終回収日時&thinsp;:&thinsp;' . $data['LastReset'];
							}
						echo '</div>';
					echo '</li>';
				}
				echo '</ul>';
				$PinData = $PinData . "]";
			?>
			</form>
		</ul>

		<script>
		<?php
			$num=0;
			foreach($capList as $data){
				if($data==null){
					$num++;
					continue;
				}
				$h=(int)map(1.0-$data, 0,1, 0,180);
				$s=190;
				$v=160;
				$color=hsv2code($h,$s,$v);
				?>
				var progress<?=$num?> = new ProgressBar.Circle('#progress<?=$num?>',{
					color:'#<?=$color?>',
					fill:'#eee',
					trailcolor:'#f75555',
					easing:'easeOut',
					strokeWidth:5,
					svgStyle:{
						width:'40px',
						height:'40px'
					}
				});
				progress<?=$num?>.setText(Math.round(<?=$data?>*100));
				progress<?=$num?>.animate(<?=$data?>);
		<?php 	$num++; ?>
		<?php } ?>
		</script>
		
		
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
		<script src="https://maps.googleapis.com/maps/api/js?key=
		<?php echo MAP_API_KEY; ?>
		&callback=initMap"></script>
	</div>
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

