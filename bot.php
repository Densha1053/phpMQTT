<?php

require(“phpMQTT.php”);
$host = "www.km1.io";
$port = 1883;
$username = "Benz1053";
$password = "benz1053";


$mqtt = new phpMQTT($host, $port, "ClientID".rand());

$token = "c//eUJe6lMKtCicCrC9eCSE5pHZvRiCgavKE5bI6Jd8ujPcvCubtGWhUloHHixBOumFO6IRkKD+q9+AYcU/0tcylBJcaZpWUhotRTPJbQpLkjbzjjl8Q1UwTw60olaqh0fRR7qi3AEYzFej6zDDoyQdB04t89/1O/w1cDnyilFU="; //นำ token ที่มาจาก line developer account ของเรามาใส่ครับ


$jsonStr = file_get_contents(‘php://input’);
$jsonObj = json_decode($jsonStr);
print_r($jsonStr);
                             
foreach ($jsonObj['events'] as $event) {
	if($event['type'] == 'message' && $event['message']['type'] == 'text'){
		$text = $event['message']['text'];
		$replyToken = $event['replyToken'];

		if (preg_match(“/สวัสดี/”, $text)) {
			$text = “มีอะไรให้Denshaรับใช้ครับ”;
			$messages = [
				'type' => 'text',
				'text' => $text
			];
		}

		if (preg_match(“/เปิดทีวี/”, $text)) {     //หากในแชตที่ส่งมามีคำว่า เปิดทีวี ก็ให้ส่ง mqtt ไปแจ้ง server เราครับ
			if ($mqtt->connect(true,NULL,$username,$password)) {
				$mqtt->publish(“/Benz1053/room1”,”TV”,0); // ตัวอย่างคำสั่งเปิดทีวีที่จะส่งไปยัง mqtt server
				$mqtt->close();
			}
			$text = “เปิดทีวีให้แล้วคร้าบบบบ”;
			$messages = [
				'type' => 'text',
				'text' => $text
			];
		}
		if (preg_match(“/ปิดทีวี/”, $text) and !preg_match(“/เปิดทีวี/”, $text)) {
			if ($mqtt->connect(true,NULL,$username,$password)) {
				$mqtt->publish(“/Benz1053/room1”,”TV”,0);
				$mqtt->close();
			}
			$text = "ปิดทีวีให้แล้วนะครับ!!”;
			$messages = [
				'type' => 'text',
				'text' => $text
			];
		}
		$url = 'https://api.line.me/v2/bot/message/reply';
		$data = [
			'replyToken' => $replyToken,
			'messages' => [$messages],
		];
		$post = json_encode($data);
		$headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $token);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$result = curl_exec($ch);
		curl_close($ch);

		echo $result . "\r\n";  
	}
}
