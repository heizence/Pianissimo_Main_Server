<?php

/* php 서버 시작 시 다음 명령어로 서버 실행.
php -S {ip_address}:{port} -t /Users/doheon/dev_workplace/Pianissimo\ Application/Main_server

android OS 에서는 임의의 ip 주소나 localhost 로는 http 요청을 사용할 수 없음.
따라서 ip 주소는 PHP 서버가 호스팅되는 기기(macbook) 의 ip 주소를 사용해야 함. 
이 ip 주소는 macos 기준 terminal 에서 ifconfig 명령어로 찾을 수 있음. en0 section 에 있는 "inet" 주소에 해당함.
*/

include './secretKeys.php';

// Database 설정 및 연결
$db = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME) or die ("Can't access DB");

// for blob file transfer or some other special cases.
try {
    $dsn = "mysql:host=.$DB_HOST.;dbname=.$DB_NAME.";
    $pdo = new PDO($dsn, $DB_USERNAME, $DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $e->getMessage();
}

date_default_timezone_set('Asia/Seoul');

// 응답 형식 자동 생성 모듈
require 'sendHTTPResponse.php';
?>
