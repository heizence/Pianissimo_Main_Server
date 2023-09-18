<?php
// 응답 헤더 설정
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: content-type, Authorization");
header('Content-type: application/json');

// GET 요청 시 header 에 있는 토큰 불러올 때 사용
$headers = getallheaders();
$headersJson = json_encode($headers);

if (isset($headers['Authorization'])) $authorization = $headers['Authorization'];
else $authorization = null;

function sendResponse($message, $data, $statusCode) {
    $response['message'] = $message;
    $response['data'] = $data;    
    $response['statusCode'] = $statusCode;
    http_response_code($statusCode);

    return json_encode($response);
}
?>
