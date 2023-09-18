<?php

/* php 서버 시작 시 다음 명령어로 서버 실행.
php -S 172.30.112.55:8000 -t /Users/doheon/dev_workplace/Pianissimo\ Application/Main_server

** home wifi : 172.16.11.213

android OS 에서는 임의의 ip 주소나 localhost 로는 http 요청을 사용할 수 없음.
따라서 ip 주소(172.16.11.213)는 PHP 서버가 호스팅되는 기기(macbook) 의 ip 주소를 사용해야 함. 
이 ip 주소는 macos 기준 terminal 에서 ifconfig 명령어로 찾을 수 있음. en0 section 에 있는 "inet" 주소에 해당함.
*/

// Database 설정 및 연결

$dbHost = "localhost";
$dbUsername = "root";
$dbPassword = "quantum6626*";
$dbName = "Pianissimo";

$db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName) or die ("Can't access DB");

// for blob file transfer or some other special cases.
try {
    $dsn = 'mysql:host=localhost;dbname=Pianissimo';
    $pdo = new PDO($dsn, $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $e->getMessage();
}

date_default_timezone_set('Asia/Seoul');

// 응답 형식 자동 생성 모듈
require 'sendHTTPResponse.php';
?>
