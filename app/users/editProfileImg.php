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
                $imageFileString = $_POST['imageFileString'];   // base64 로 인코딩된 이미지 파일
                
                $updateQuery = $db->query("UPDATE APP_USERS SET AU_ProfileImg = '$imageFileString' WHERE AU_Id = $studentId");
                if ($updateQuery) {                
                    echo sendResponse('edit profile img success!', $imageFileString, 200);
                }
                else {
                    echo sendResponse('edit profile img failed!', '', 500);                      
                }                
            }
            else {
                error_log("Invalid Token!");
                echo sendResponse('Invalid Token!', '', 401);        
            }
        }
        else {
            error_log("No token in header!");
            echo sendResponse("No token in header!", "", 401);
        }
    }
    else {        
        error_log("No Authorization in header!");
        echo sendResponse("No Authorization in header!", "", 401);
    }
}

?>
