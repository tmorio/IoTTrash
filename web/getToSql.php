<?php

require_once('./sqlSettings.php');

print "データーベースに接続しています...\n";

//文字コード設定(絵文字対策のためにUTF8MB4)
$strcode = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8mb4'");
//DB接続試行
try {
        $dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_ID, DB_PASS, $strcode);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
        echo $e->getMessage();
        print "データーベースに接続できませんでした。\n";
        exit;
        }

print "データーベースとの接続を確立しました。\n";


print "Sigfox Cloudに接続し最新のデータを取得しています..." . "\n";
$url = 'https://backend.sigfox.com/api/devices/deviceID/messages';
$curl = curl_init();
$login = '**********************';
$pass = '***********************************:';
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_HTTPGET, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($curl, CURLOPT_USERPWD,"$login:$pass");
$data  = curl_exec ($curl);
$fl = fopen('MsgOut.json','w');
{      fwrite($fl, $data);
       fclose($fl);
}

curl_close($curl);

print "データ取得成功しました...データを抜き出しています..." . "\n";

//JSONデコード
$filePass = "MsgOut.json";
$jsonGet = file_get_contents($filePass);
$jsonGet = mb_convert_encoding($jsonGet, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
$Jdata = json_decode($jsonGet, true);

//取得結果出力
print "------------直近1件目-----------" . "\n" . "\n";
print "UNIX時間:";
print $Jdata['data'][0]['time'] . "\n";
print "センサ値:";
print $Jdata['data'][0]['data'] .  "\n" . "\n";

print "------------直近2件目-----------" . "\n" . "\n";
print "UNIX時間:";
print $Jdata['data'][1]['time'] . "\n";
print "センサ値:";
print $Jdata['data'][1]['data'] .  "\n" . "\n";

print "------------直近3件目-----------" . "\n" . "\n";
print "UNIX時間:";
print $Jdata['data'][2]['time'] . "\n";
print "センサ値:";
print $Jdata['data'][2]['data'] .  "\n" . "\n";

$query = "INSERT INTO History (DeviceID, Time, Sensor, Sensor1, Sensor2, Sensor3, Sensor4, Sensor5, Sensor6) VALUES (:deviceid, :intime, :sensor, :sensor1, :sensor2, :sensor3, :sensor4, :sensor5, :sensor6)";

$deviceid = $Jdata['data'][0]['device'];
$Intime = intval($Jdata['data'][0]['time']);

$sensorAll = $Jdata['data'][0]['data'];
$sensor = str_split($Jdata['data'][0]['data'], 4);

$Intime = date('Y-m-d H:i:s', $Intime);

for ($i = 0; $i < 5; $i++){
	$sensor[$i] = hexdec($sensor[$i]);
}

$stmt = $dbh->prepare($query);
$stmt->bindParam(':deviceid', $deviceid, PDO::PARAM_STR);
$stmt->bindParam(':intime', $Intime, PDO::PARAM_STR);
$stmt->bindParam(':sensor', $sensorAll, PDO::PARAM_STR);
$stmt->bindParam(':sensor1', $sensor[0], PDO::PARAM_INT);
$stmt->bindParam(':sensor2', $sensor[1], PDO::PARAM_INT);
$stmt->bindParam(':sensor3', $sensor[2], PDO::PARAM_INT);
$stmt->bindParam(':sensor4', $sensor[3], PDO::PARAM_INT);
$stmt->bindParam(':sensor5', $sensor[4], PDO::PARAM_INT);
$stmt->bindParam(':sensor6', $sensor[5], PDO::PARAM_INT);

try{
	$stmt->execute();
	print "データの格納成功しました。\n";
} catch (PDOException $e){
	print "データの格納に失敗しました。";
}

?>
