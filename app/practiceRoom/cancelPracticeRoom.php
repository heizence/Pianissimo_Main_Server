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
                $bookStatusId = (int)$_POST['bookStatusId'];   

                // 연습실 예약 정보 삭제
                $deleteQuery = $db->query("DELETE FROM PRACTICE_ROOMS_BOOK_STATUS WHERE PRBS_Id = $bookStatusId;");
                
                if ($deleteQuery) {
                    echo sendResponse('cancel practiceRoom success!', "", 200); 
                }
                else {
                    echo sendResponse('cancel practiceRoom failed!', "", 500); 
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