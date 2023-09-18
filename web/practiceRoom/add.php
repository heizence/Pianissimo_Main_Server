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
                $name = $data['name'];  // 연습실 이름
                $registeredDate = date('Y-m-d');    // 추가한 날짜(현재 날짜)         

                $insertQuery = $db->query("INSERT INTO PRACTICE_ROOMS (PR_Name, PR_RegisteredDate) VALUES('$name', '$registeredDate');");

                if($insertQuery === true){
                    echo sendResponse('add practice room success!', "", 200);
                }
                else {
                    echo sendResponse('add practice room failed!', "", 200);
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