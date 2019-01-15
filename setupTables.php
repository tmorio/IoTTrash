<?php
require_once('./web/myid.php');

$strcode = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8mb4'");
try {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_ID, DB_PASS, $strcode);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        echo "DBサーバーとの接続を確立しました。\n";
     } catch (PDOException $e) {
        echo "DBサーバーへの接続に失敗しました。web/myid.phpが正しく記入されているかご確認下さい。\n";
        exit();
     }

try {
	echo "テーブルを初期化しています...\n";
	$stmt = $pdo->query("DROP TABLE IF EXISTS `API`");
        $stmt = $pdo->query("DROP TABLE IF EXISTS `EmailChange`");
        $stmt = $pdo->query("DROP TABLE IF EXISTS `History`");
        $stmt = $pdo->query("DROP TABLE IF EXISTS `StatusData`");
        $stmt = $pdo->query("DROP TABLE IF EXISTS `Users`");

        echo "テーブルの作成を行なっています...\n";
        $stmt = $pdo->query("CREATE TABLE `API` (   `ID` int(11) NOT NULL AUTO_INCREMENT,   `Owner` int(11) DEFAULT NULL,   `Group` int(11) DEFAULT NULL,   `API_Key` varchar(255) DEFAULT NULL,   `API_Secret` varchar(255) DEFAULT NULL,   PRIMARY KEY (`ID`) ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4");
        $stmt = $pdo->query("CREATE TABLE `EmailChange` (   `ID` int(11) NOT NULL AUTO_INCREMENT,   `UserID` int(11) DEFAULT NULL,   `newMail` varchar(255) DEFAULT NULL,   `verifyCode` varchar(255) DEFAULT NULL,   `expireTime` datetime DEFAULT NULL,   PRIMARY KEY (`ID`) ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4");
        $stmt = $pdo->query("CREATE TABLE `History` (   `ID` int(11) NOT NULL AUTO_INCREMENT,   `DeviceID` varchar(10) DEFAULT NULL,   `Time` datetime DEFAULT NULL,   `Sensor` varchar(28) DEFAULT NULL,   `Temp` int(11) DEFAULT NULL,   `Hum` int(11) DEFAULT NULL,   `Dis` int(11) DEFAULT NULL,   PRIMARY KEY (`ID`) ) ENGINE=InnoDB AUTO_INCREMENT=533 DEFAULT CHARSET=utf8mb4");
        $stmt = $pdo->query("CREATE TABLE `StatusData` (   `ID` int(11) NOT NULL AUTO_INCREMENT,   `DeviceID` varchar(10) DEFAULT NULL,   `Time` datetime DEFAULT NULL,   `Sensor` varchar(28) DEFAULT NULL,   `Temp` int(11) DEFAULT NULL,   `Hum` int(11) DEFAULT NULL,   `Dis` int(11) DEFAULT NULL,   `Owner` int(11) DEFAULT NULL,   `GroupID` int(11) DEFAULT NULL,   `Latitude` decimal(25,20) DEFAULT NULL,   `Longitude` decimal(25,20) DEFAULT NULL,   `NickName` varchar(255) DEFAULT NULL,   `LastReset` datetime DEFAULT NULL,   `gettingStatus` tinyint(4) NOT NULL DEFAULT '0',   `ServiceUser` int(11) DEFAULT NULL,   PRIMARY KEY (`ID`) ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4");
        $stmt = $pdo->query("CREATE TABLE `Users` (   `ID` int(11) NOT NULL AUTO_INCREMENT,   `UserID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,   `Password` varchar(255) NOT NULL,   `GroupID` int(11) DEFAULT NULL,   `Name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,   `Service` tinyint(4) NOT NULL DEFAULT '0',   `mailAddress` varchar(255) NOT NULL DEFAULT '',   PRIMARY KEY (`ID`) ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4");

        echo "DBサーバーとの接続を解除しています...\n";
        $pdo = null;
        echo "データベースの準備が完了しました。\n";
    } catch (PDOException $e) {
        echo "エラーが発生しました。データーベースへの書き込み権限があるかご確認の上,再度お試し下さい。\n";
        $pdo = null;
        }

?>
