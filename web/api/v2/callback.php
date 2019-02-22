<?php
require_once('../../myid.php');
//文字コード設定(絵文字対策のためにUTF8MB4)
$strcode = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8mb4'");
//DB接続試行
try {
        $dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_ID, DB_PASS, $strcode);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
        echo $e->getMessage();
        exit;
        }
$json_string = file_get_contents('php://input');
$data = json_decode($json_string, true);

if (empty($data['APIKEY'])) {
        http_response_code(406);
        header('HTTP/1.1 406 Not Acceptable');
        die('406 Not Acceptable');
        exit(0);
}

$ApiKey = $data['APIKEY'];
$ApiSecret = $data['APISECRET'];

$query = "SELECT * FROM API WHERE API_Key = :APIKEY";
$stmt = $dbh->prepare($query);
$stmt->bindParam(':APIKEY', $ApiKey, PDO::PARAM_STR);
$stmt->execute();

foreach ($stmt as $row) {
	$row['API_Secret'];
}

if($ApiSecret != $row['API_Secret']){
	http_response_code(406);
	echo "406 Not Acceptable";
	exit(0);
}

$deviceid = $data['device'];
$sensorAll = $data['data'];
$temp = $data['temp'];
$hum = $data['hum'];
$distance = $data['distance'];
$Intime = date('Y-m-d H:i:s', $data['time']);

$sensor = str_split($data['data'], 4);
for ($i = 0; $i < 3; $i++){
	$sensor[$i] = hexdec($sensor[$i]);
}

//THIS IS Ver3.0-Beta API
$query = "SELECT * FROM StatusData WHERE DeviceID = :deviceid";
$stmt = $dbh->prepare($query);
$stmt->bindParam(':deviceid', $deviceid, PDO::PARAM_STR);
$stmt->execute();
$firstCheck = $stmt->fetchAll();
foreach($firstCheck as $data){
	if(emtpy($data['MaxADis']){
		$query = "UPDATE StatusData SET MaxADis = :FirstDis WHERE DeviceID = :deviceid";
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(':FirstDis', ($sensor[2] + 5), PDO::PARAM_INT);
		$stmt->bindParam(':deviceid', $deviceid, PDO::PARAM_STR);
		$stmt->execute();
	}
	if((($data['MaxADis'] - $distance) / $data['MaxADis'] * 100) <= 20){
		$query = "SELECT * FROM StatusData WHERE DeviceID = :deviceid";
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(':deviceid', $deviceid, PDO::PARAM_STR);
		$stmt->execute();
		$getInfo = $stmt->fetchAll();

		foreach($getInfo as $setting){
			$DevName = $setting['NickName'];
			$DevID = $setting['DeviceID'];
			$UserIDInfo = $setting['Owner'];
			$query = "SELECT * FROM UserSetting WHERE UserID = :userid";
			$stmt = $dbh->prepare($query);
			$stmt->bindParam(':userid', $UserIDInfo, PDO::PARAM_INT);
			$stmt->execute();
			$MXset = $stmt->fetch(PDO::FETCH_ASSOC);
			if($MXset['MaxNotice'] == 1){
				$query = "SELECT * FROM Users WHERE ID = :userid";
				$stmt = $dbh->prepare($query);
				$stmt->bindParam(':userid', $UserIDInfo, PDO::PARAM_INT);
				$stmt->execute();
				$UserSet = $stmt->fetch(PDO::FETCH_ASSOC);

				$toMail = $UserSet['mailAddress'];
                                $returnMail = 'mybox@moritoworks.com';
                                $name = "MyBox Cloud";
                                $mail = 'mybox@moritoworks.com';
                                $subject = "空き残量が少なくなっています";


$body = <<< EOM
以下のゴミ箱の空き容量が20%以下となりました。
回収を行って下さい。

データ取得日時 : {$Intime}
デバイス名/ID : {$DevName} ({$DevID})

サービスへのログインは以下から行えます。
https://mybox.moritoworks.com/login.php

なお、このメールは送信専用のメールアドレスで送信しているため、返信頂いても対応することができません。
何卒ご了承ください。
------------------------------
MyBox Cloud

Developed by IoT oyama Team.
------------------------------

EOM;

                                mb_language('ja');
                                mb_internal_encoding('UTF-8');
                                $header = 'From: ' . mb_encode_mimeheader($name). ' <' . $mail. '>';
                                mb_send_mail($toMail, $subject, $body, $header, '-f'. $returnMail);

			}
		}
	}

	$query = "SELECT * FROM History WHERE DeviceID = :deviceid ORDER BY ID DESC LIMIT 2";
	$stmt = $dbh->prepare($query);
	$stmt->bindParam(':deviceid', $deviceid, PDO::PARAM_STR);
	$stmt->execute();
	$SMBdata = $stmt->fetch();

	$FSFlag = 0;
	$SSFlag = 0;

	if((abs($SMBdata[0]['Temp'] - $temp) > 2) || (abs($SMBdata[0]['Hum'] - $hum) > 2){
        	$FSFlag = 1;
	}

	if((abs($SMBdata[0]['Temp'] - $SMBdata[1]['Temp']) > 2 || abs($SMBdata[0]['Hum'] - $SMBdata[1]['Hum']) > 2){
        	$SSFlag = 1;
	}

	if(($FSFlag == 1) && ($SSFlag == 1){
        	$query = "UPDATE StatusData SET WarSM = 1 WHERE DeviceID = :deviceid";
        	$stmt = $dbh->prepare($query);
        	$stmt->bindParam(':deviceid', $deviceid, PDO::PARAM_STR);
        	$stmt->execute();

                $query = "SELECT * FROM StatusData WHERE DeviceID = :deviceid";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':deviceid', $deviceid, PDO::PARAM_STR);
                $stmt->execute();
                $getInfo = $stmt->fetchAll();

                foreach($getInfo as $setting){
                        $DevName = $setting['NickName'];
                        $DevID = $setting['DeviceID'];
                        $UserIDInfo = $setting['Owner'];
                        $query = "SELECT * FROM UserSetting WHERE UserID = :userid";
                        $stmt = $dbh->prepare($query);
                        $stmt->bindParam(':userid', $UserIDInfo, PDO::PARAM_INT);
                        $stmt->execute();
                        $SMset = $stmt->fetch(PDO::FETCH_ASSOC);
                        if($SMset['SMNotice'] == 1){
                                $query = "SELECT * FROM Users WHERE ID = :userid";
                                $stmt = $dbh->prepare($query);
                                $stmt->bindParam(':userid', $UserIDInfo, PDO::PARAM_INT);
                                $stmt->execute();
                                $UserSet = $stmt->fetch(PDO::FETCH_ASSOC);

                                $toMail = $UserSet['mailAddress'];
                                $returnMail = 'mybox@moritoworks.com';
                                $name = "MyBox Cloud";
                                $mail = 'mybox@moritoworks.com';
                                $subject = "臭いに関する注意のお知らせ";


$body = <<< EOM
以下のゴミ箱から不快な臭いを発生することを予測しました。
確認、回収を行って下さい。

データ取得日時 : {$Intime}
デバイス名/ID : {$DevName} ({$DevID})

サービスへのログインは以下から行えます。
https://mybox.moritoworks.com/login.php

なお、このメールは送信専用のメールアドレスで送信しているため、返信頂いても対応することができません。
何卒ご了承ください。
------------------------------
MyBox Cloud

Developed by IoT oyama Team.
------------------------------

EOM;

                                mb_language('ja');
                                mb_internal_encoding('UTF-8');
                                $header = 'From: ' . mb_encode_mimeheader($name). ' <' . $mail. '>';
                                mb_send_mail($toMail, $subject, $body, $header, '-f'. $returnMail);

                        }
		}
	}
}


//END

$query = "INSERT INTO History (DeviceID, Time, Sensor, Temp, Hum, Dis) VALUES (:deviceid, :intime, :sensor, :sensor1, :sensor2, :sensor3)";

$stmt = $dbh->prepare($query);
$stmt->bindParam(':deviceid', $deviceid, PDO::PARAM_STR);
$stmt->bindParam(':intime', $Intime, PDO::PARAM_STR);
$stmt->bindParam(':sensor', $sensorAll, PDO::PARAM_STR);
$stmt->bindParam(':sensor1', $sensor[0], PDO::PARAM_INT);
$stmt->bindParam(':sensor2', $sensor[1], PDO::PARAM_INT);
$stmt->bindParam(':sensor3', $sensor[2], PDO::PARAM_INT);
$stmt->execute();

$query = "UPDATE StatusData SET Time = :intime, Sensor = :sensor, Temp = :sensor1, Hum = :sensor2, Dis = :sensor3, DevInfo = 0 WHERE DeviceID = :deviceid";
$stmt = $dbh->prepare($query);
$stmt->bindParam(':deviceid', $deviceid, PDO::PARAM_STR);
$stmt->bindParam(':intime', $Intime, PDO::PARAM_STR);
$stmt->bindParam(':sensor', $sensorAll, PDO::PARAM_STR);
$stmt->bindParam(':sensor1', $sensor[0], PDO::PARAM_INT);
$stmt->bindParam(':sensor2', $sensor[1], PDO::PARAM_INT);
$stmt->bindParam(':sensor3', $sensor[2], PDO::PARAM_INT);
$stmt->execute();

?>
