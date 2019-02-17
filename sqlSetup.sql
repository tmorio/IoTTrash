CREATE TABLE `API` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Owner` int(11) DEFAULT NULL,
  `Group` int(11) DEFAULT NULL,
  `API_Key` varchar(255) DEFAULT NULL,
  `API_Secret` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `EmailChange` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `newMail` varchar(255) DEFAULT NULL,
  `verifyCode` varchar(255) DEFAULT NULL,
  `expireTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `History` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DeviceID` varchar(10) DEFAULT NULL,
  `Time` datetime DEFAULT NULL,
  `Sensor` varchar(28) DEFAULT NULL,
  `Temp` int(11) DEFAULT NULL,
  `Hum` int(11) DEFAULT NULL,
  `Dis` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=936 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `OrderInfo` (
  `OrderID` int(11) NOT NULL AUTO_INCREMENT,
  `DeviceID` varchar(255) DEFAULT NULL,
  `DevName` varchar(255) DEFAULT NULL,
  `GroupID` int(11) DEFAULT NULL,
  `Lat` decimal(25,20) DEFAULT NULL,
  `Lng` decimal(25,20) DEFAULT NULL,
  `Owner` int(11) DEFAULT NULL,
  PRIMARY KEY (`OrderID`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `StatusData` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DeviceID` varchar(10) DEFAULT NULL,
  `Time` datetime DEFAULT NULL,
  `Sensor` varchar(28) DEFAULT NULL,
  `Temp` int(11) DEFAULT NULL,
  `Hum` int(11) DEFAULT NULL,
  `Dis` int(11) DEFAULT NULL,
  `Owner` int(11) DEFAULT NULL,
  `GroupID` int(11) DEFAULT NULL,
  `Latitude` decimal(25,20) DEFAULT NULL,
  `Longitude` decimal(25,20) DEFAULT NULL,
  `NickName` varchar(255) DEFAULT NULL,
  `LastReset` datetime DEFAULT NULL,
  `OrderStatus` tinyint(4) NOT NULL DEFAULT '0',
  `ServiceUser` int(11) DEFAULT NULL,
  `MaxADis` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `Password` varchar(255) NOT NULL,
  `GroupID` varchar(255) DEFAULT NULL,
  `Name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `Service` tinyint(4) NOT NULL DEFAULT '0',
  `mailAddress` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
