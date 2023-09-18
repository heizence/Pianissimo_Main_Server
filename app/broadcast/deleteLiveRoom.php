<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

// 방송 끝
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
                
            if ($tokenData) { 
                $roomId = $_POST['roomId'];                                
                $deleteQuery = $db->query("DELETE FROM BROADCASTS WHERE B_Id = '$roomId';");

                if ($deleteQuery){
                    echo sendResponse('delete live room success!', "", 200);
                }
                else {
                    echo sendResponse('delete live room failed!', "", 500);
                }  
            }
            else {
                error_log('Invalid Token');
                echo sendResponse('Invalid Token!', '', 401);
            }
          
        } else {
            error_log('No token in header!');
            echo sendResponse("No token in header!", "", 401);
        }
    }
    else {        
        error_log('No Authorization in header!');
        echo sendResponse("No Authorization in header!", "", 401);
    }
}
?>