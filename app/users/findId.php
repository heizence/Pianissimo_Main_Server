<?php 
include '../../modules/Config.php';
include '../../modules/JWT.php';
include '../../modules/secretKeys.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {        
    $phoneNumber = $_POST['phoneNumber'];    
    
    // 전화번호에 해당하는 계정 이메일 찾기
    $checkQuery = $db->query("SELECT * FROM APP_USERS WHERE AU_PhoneNumber = '$phoneNumber'");
    if ($checkQuery->num_rows > 0){        
        $appUserEmail;
        while($row = $checkQuery->fetch_assoc()) {
            $appUserEmail = $row['AU_Email'];
        }

        // Naver Cloud SMS service 설정
        $serviceId = $NAVER_CLOUD_SMS_SERVICE_ID; // 서비스 ID
        $smsURL = "https://sens.apigw.ntruss.com/sms/v2/services/".$serviceId."/messages";  // 요청 보내주는 URL 주소
        $smsUri = "/sms/v2/services/".$serviceId."/messages";
        
        $accessKey = $NAVER_CLOUD_SMS_ACCESS_KEY;
        $secretKey = $NAVER_CLOUD_SMS_SECRET_KEY;

        $sTime = floor(microtime(true) * 1000); // timestamp 값

        // 요청 객체 정의
        $postData = array(
            'type' => 'SMS',
            'countryCode' => '82',
            'from' => "$phoneNumber",   // 발신번호. 현재는 테스트용이므로 naver SMS API 에 등록된 번호(01030202168)만 사용할 수 있음.
            'contentType' => 'COMM',
            'content' => "contents!",   // 기본 메시지 컨텐츠 내용
            'messages' => array(
                array(                    
                    'content' => "[Pianissimo 원생용 앱 아이디 찾기]\n 아이디: $appUserEmail", // 실제로 사용자가 수신하는 메시지 내용(정의하지 않으면 기본 메시지 컨텐츠 내용을 사용)
                    'to' => "$phoneNumber"
                    ))
        );
        $postFields = json_encode($postData) ;
    
        // signature 생성
        $hashString = "POST {$smsUri}\n{$sTime}\n{$accessKey}";
        $dHash = base64_encode( hash_hmac('sha256', $hashString, $secretKey, true) );

        // 요청 헤더 설정
        $header = array(
            // "accept: application/json",
            'Content-Type: application/json; charset=utf-8',
            'x-ncp-apigw-timestamp: '.$sTime,
            "x-ncp-iam-access-key: ".$accessKey,
            "x-ncp-apigw-signature-v2: ".$dHash
        );

        // 요청 전송
        $ch = curl_init($smsURL);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => $postFields
        ));

        $json_response = curl_exec($ch);
        error_log("json_response : $json_response");   
        $response_parsed = json_decode($json_response);

        if ($response_parsed->statusCode === '202') {
            echo sendResponse('findId success!', $appUserEmail, 200);
        }
        else {
            echo sendResponse('findId failed!', $json_response, $response_parsed->status);
        }
    }
    else {
        echo sendResponse('Invalid phone number!', '', 404);
    }
}
?>