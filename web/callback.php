<?php
require_once('./myid.php');

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

$deviceid = $data['device'];

if (empty($deviceid) || $data['data'] == "b051d200ad22f800") {
        header('HTTP/1.1 403 アクセス権限がありません．');
        die('[403] アクセス権限がありません．');
        exit(0);
}

$sensorAll = $data['data'];
$temp = $data['temp'];
$hum = $data['hum'];
$distance = $data['distance'];
$Intime = date('Y-m-d H:i:s', $data['time']);

$sensor = str_split($data['data'], 4);
for ($i = 0; $i < 3; $i++){
        $sensor[$i] = hexdec($sensor[$i]);
}

$query = "INSERT INTO History (DeviceID, Time, Sensor, Temp, Hum, Dis) VALUES (:deviceid, :intime, :sensor, :sensor1, :sensor2, :sensor3)";

$stmt = $dbh->prepare($query);
$stmt->bindParam(':deviceid', $deviceid, PDO::PARAM_STR);
$stmt->bindParam(':intime', $Intime, PDO::PARAM_STR);
$stmt->bindParam(':sensor', $sensorAll, PDO::PARAM_STR);
$stmt->bindParam(':sensor1', $sensor[0], PDO::PARAM_STR);
$stmt->bindParam(':sensor2', $sensor[1], PDO::PARAM_STR);
$stmt->bindParam(':sensor3', $sensor[2], PDO::PARAM_STR);

try{
        $stmt->execute();
} catch (PDOException $e){
}

?>
