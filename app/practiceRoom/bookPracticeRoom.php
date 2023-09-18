<?php 
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {        
    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);   

            if ($tokenData) {
                $studentId = (int)$_POST['studentId'];
                $roomId = (int)$_POST['roomId'];
                $roomUsageDate = $_POST['roomUsageDate'];
                $roomUsageStartTime = (int)$_POST['roomUsageStartTime'];

                // 연습실 예약 데이터 삽입
                $insertQuery = $db->query("INSERT INTO PRACTICE_ROOMS_BOOK_STATUS (PRBS_Date, PRBS_StartTime, PRBS_RoomId, PRBS_StudentId)
                VALUES('$roomUsageDate', $roomUsageStartTime, $roomId, $studentId);
                ");                
                                
                if ($insertQuery){
                    echo sendResponse('book practiceRoom success!', "", 200);
                }
                else {
                    echo sendResponse('book practiceRoom failed!', "", 500);
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