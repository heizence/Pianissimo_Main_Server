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
                $phoneNumber = $data['phoneNumber']; 
                $genre = $data['genre'];        
                $lessonPerformed = 0;
                $rate = "0&0";
                $registeredDate = date("Y-m-d");    // 원생 등록 날짜(현재 날짜)

                /* 
                이미 등록된 강사가 있는지 체크
                동명이인이 있을 수 있으므로 안내 메시지 보내주기
                */
                $checkQuery = $db->query("SELECT * FROM INSTRUCTORS WHERE I_Name = '$name'");
                if($checkQuery->num_rows > 0){
                // for test
                // while($row = $query->fetch_assoc()) {
                // }
                echo sendResponse('해당 이름으로 등록된 강사가 있습니다. 동명이인 등록 시 이름 뒤에 별도의 식별자를 넣어주세요!\n 예) 홍길동_A', "", 404);
                }
                else {
                    $insertQuery = $db->query("INSERT INTO INSTRUCTORS (I_Name, I_PhoneNumber, I_Genre, I_LessonPerformed, I_Rate, I_RegisteredDate) VALUES('$name', '$phoneNumber', '$genre', $lessonPerformed, '$rate', '$registeredDate')");
                    if($insertQuery === true){
                        //$appUserId = $db->insert_id; // for test                
                        echo sendResponse('register instructor success!', "", 200);
                    }
                    else {
                        echo sendResponse('register instructor failed!', "", 500);
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