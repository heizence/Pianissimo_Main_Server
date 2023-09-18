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
                $newPassword = $_POST['newPassword'];

                $parted = explode('.', base64_decode($token));
                $payload = json_decode($parted[1], true);
                $appUserId = base64_decode($payload['appUserId']);  // 회원 고유 id
        
                $checkQuery = $db->query("SELECT * FROM APP_USERS WHERE AU_Id = '$appUserId'");
                if ($checkQuery->num_rows > 0){
                //    for test
                //    while($row = $query->fetch_assoc()) {
                //    }
                    $updateQuery = $db->query("UPDATE APP_USERS SET AU_Password = '$newPassword' WHERE AU_Id = $appUserId");
                    if ($updateQuery) {                
                        echo sendResponse('editPassword success!', '', 200);
                    }
                    else {
                        echo sendResponse('editPassword failed!', '', 404);                      
                    }            
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
