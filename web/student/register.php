<?php 
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

        // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);   

            if ($tokenData) {
                $name = $data['name'];
                $email = $data['email'];        
                $phoneNumber = $data['phoneNumber']; 
                $password = hash("sha256", '1234'); // 원생용 앱 계정 초기 비밀번호는 1234 로 지정
                $registeredDate = date("Y-m-d");    // 원생 등록 날짜(현재 날짜)

                // 이미 가입된 계정이 있는지 체크
                $checkQuery = $db->query("SELECT * FROM APP_USERS WHERE AU_Email = '$email'");
                if($checkQuery->num_rows > 0){
                // for test
                // while($row = $query->fetch_assoc()) {
                // }
                echo sendResponse('이미 가입된 아이디입니다.', "", 404);
                }
                else {
                    $insertQuery = $db->query("
                    INSERT INTO APP_USERS (AU_Email, AU_Password, AU_Name, AU_PhoneNumber, AU_Status, AU_PauseDayLeft, AU_RegisteredDate)
                    VALUES('$email', '$password', '$name', '$phoneNumber', '결제내역 없음', 0, '$registeredDate')
                    ");
                    if($insertQuery === true){
                        //$appUserId = $db->insert_id; // for test                
                        echo sendResponse('register student success!', "", 200);
                    }
                    else {
                        echo sendResponse('register student failed!', "", 500);
                    }
                }
            }
            else {
                echo sendResponse('Invalid Token!', '', 401);
            }
        }
        else {
            echo sendResponse("No token in header!", "", 401);
        }
    }
    else {
        echo sendResponse("No Authorization in header!", "", 401);
    }
}
?>