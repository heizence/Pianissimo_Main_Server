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
                $studentId = $data['studentId'];
                $studentName = $data['name'];
                $phoneNumber = $data['phoneNumber'];

                $updateQuery = $db->query("UPDATE APP_USERS SET AU_Name = '$studentName', AU_PhoneNumber = '$phoneNumber' WHERE AU_Id = $studentId;");
                if($updateQuery){                    
                    echo sendResponse('edit student success!', "", 200);
                }
                else {
                    echo sendResponse('edit student failed!', "", 500);
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