 //  Send sample SIGFOX messages with UnaBiz UnaShield V2S Arduino Shield.
//  This sketch includes diagnostics functions in the UnaShield.
//  For a simpler sample sketch, see examples/send-light-level.
#include "SIGFOX.h"
#include <Wire.h>
#include <SPI.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME280.h>

#define BME_SCK 13
#define BME_MISO 12
#define BME_MOSI 11
#define BME_CS 10
#define SEALEVELPRESSURE_HPA (1013.25)

Adafruit_BME280 bme; // I2C

//  IMPORTANT: Check these settings with UnaBiz to use the SIGFOX library correctly.
static const String device = "NOTUSED";  //  Set this to your device name if you're using UnaBiz Emulator.
static const bool useEmulator = false;  //  Set to true if using UnaBiz Emulator.
static const bool echo = true;  //  Set to true if the SIGFOX library should display the executed commands.
static const Country country = COUNTRY_JP;  //  Set this to your country to configure the SIGFOX transmission frequencies.
static UnaShieldV2S transceiver(country, useEmulator, device, echo);  //  Uncomment this for UnaBiz UnaShield V2S Dev Kit
static String response;  //  Will store the downlink response from SIGFOX.

int temp,hum;
int distance;
//int successCount = 0;
int trig = 2;
int echoD = 3;


void setup() 
{
  pinMode(trig,OUTPUT);
  pinMode(echoD,INPUT);
  Serial.begin(9600);
    if (!bme.begin(0x76))
    {  ////  NOTE: Must use 0x76 for UnaShield V2S
      Serial.println("Could not find a valid BME280 sensor, check wiring!");
      while (1);
      {
      }
    } 
  //  Initialize console so we can see debug messages (9600 bits per second).
  Serial.println(F("Running setup..."));  
  //  Check whether the SIGFOX module is functioning.
  if (!transceiver.begin())stop(F("Unable to init SIGFOX module, may be missing"));  //  Will never return.

  //  Delay 10 seconds before sending next message.
  Serial.println("-----------------------------");
  Serial.println("MyBox Client Ver1.1");
  Serial.println("Developed by IoT oyama Team");
  Serial.println("-----------------------------");
  Serial.println("Client >> Hello MyBox Client!");
  Serial.println("Client >> Starting System...");
  delay(500);
  Serial.println("Client >> Please setting to trashbox...(Wait for 30 second)");
  delay(3000);
}

  

void loop() 
{
  // 超音波の出力終了
  
    hum = bme.readHumidity();
    temp = bme.readTemperature();

    if(hum > 100 || hum < 10 || temp > 100){
      Serial.println("Client >> Hum or Temp Error. Waiting 30 sec.");
      Serial.print("Error Data >> Temp:");
      Serial.print(temp);
      Serial.print(" Hum:");
      Serial.println(hum);
      delay(30000); 
    }else{
      digitalWrite(trig,LOW);
      delayMicroseconds(1);
      // 超音波を出力
      digitalWrite(trig,HIGH);
      
      delayMicroseconds(11);
      // 超音波を出力終了
      digitalWrite(trig,LOW);
      // 出力した超音波が返って来る時間を計測
      int t = pulseIn(echoD,HIGH);
      // 計測した時間と音速から反射物までの距離を計算
      distance = t*((331.5+(0.6*temp))*0.005);
  
      //Serial.println(t);
      Serial.print("Sensor >> Available:");
      Serial.print(distance / 100);
      Serial.println(" cm");
      Serial.print("Sensor >> Temp:");
      Serial.print(temp);
      Serial.println(" °C");
      Serial.print("Sensor >> Hum:");
      Serial.print(hum);
      Serial.println(" %");
  
      word bitTemp = temp * 1.0;
      word bitHum = hum * 1.0;
      word bitDis = distance / 100;
  
      char HexDataTemp[16] = "";
      char HexDataHum[16] = "";
      char HexDataDis[16] = "";
      sprintf(HexDataTemp, "%04x", bitTemp);
      sprintf(HexDataHum, "%04x", bitHum);
      sprintf(HexDataDis, "%04x", bitDis);
  
      String StrTemp = HexDataTemp;
      String StrHum = HexDataHum;
      String StrDis = HexDataDis;
  
      
      String msg = StrTemp + StrHum + StrDis;

      Serial.print("SendData >> Data:");
      Serial.println(msg);

      Serial.println("SIGFOX >> Sending data...");
      
      if(transceiver.sendMessage(msg))
      {
        Serial.println("SIGFOX >> Success!");
        //successCount++;
        }
      //Serial.println(successCount);
      delay(900000); 
      //delay(30000); //当日用
    }

}
