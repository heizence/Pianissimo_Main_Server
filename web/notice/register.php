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
                $title = $data['title'];
                $contents = $data['contents'];
                $writtenDate = date("Y-m-d");

                $insertQuery = $db->query("INSERT INTO NOTICES (N_Title, N_Contents, N_WrittenDate) VALUES('$title', '$contents', '$writtenDate')");
                if($insertQuery === true){
                    //$appUserId = $db->insert_id; // for test                
                    echo sendResponse('register notice success!', "", 200);
                }
                else {
                    echo sendResponse('register notice failed!', "", 500);
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