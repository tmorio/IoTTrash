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
$firstCheck = $stmt->fetch();
foreach($firstCheck as $data){
	if(emtpy($data['MaxADis']){
		$query = "UPDATE StatusData SET MaxADis = :FirstDis WHERE DeviceID = deviceid"
		$stmt = $dbh->prepare($query);
		$stmt->bindParam(':FirstDis', ($sensor[2] + 5), PDO::PARAM_INT);
		$stmt->bindParam(':deviceid', $deviceid, PDO::PARAM_STR);
		$stmt->execute();
		break;
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

$query = "UPDATE StatusData SET Time = :intime, Sensor = :sensor, Temp = :sensor1, Hum = :sensor2, Dis = :sensor3 WHERE DeviceID = :deviceid";
$stmt = $dbh->prepare($query);
$stmt->bindParam(':deviceid', $deviceid, PDO::PARAM_STR);
$stmt->bindParam(':intime', $Intime, PDO::PARAM_STR);
$stmt->bindParam(':sensor', $sensorAll, PDO::PARAM_STR);
$stmt->bindParam(':sensor1', $sensor[0], PDO::PARAM_INT);
$stmt->bindParam(':sensor2', $sensor[1], PDO::PARAM_INT);
$stmt->bindParam(':sensor3', $sensor[2], PDO::PARAM_INT);
$stmt->execute();

?>
