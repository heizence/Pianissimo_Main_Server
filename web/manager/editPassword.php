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
                $newPassword = $data['newPassword'];

                $parted = explode('.', base64_decode($token));
                $payload = json_decode($parted[1], true);
                $webUserId = base64_decode($payload['webUserId']);  // 회원 고유 id

                $checkQuery = $db->query("SELECT * FROM WEB_USER WHERE WU_Id = '$webUserId'");
                if ($checkQuery->num_rows > 0){
                //    for test
                //    while($row = $query->fetch_assoc()) {
                //    }
                    $updateQuery = $db->query("UPDATE WEB_USER SET WU_Password = '$newPassword' WHERE WU_Id = $webUserId");
                    if ($updateQuery) {                
                        echo sendResponse('editPassword success!', '', 200);
                    }
                    else {
                        echo sendResponse('editPassword failed!', '', 404);
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