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
                $noticeId = $data['noticeId'];
                $title = $data['title'];
                $contents = $data['contents'];                

                $updateQuery = $db->query("UPDATE NOTICES SET N_Title = '$title', N_Contents = '$contents' WHERE N_Id = $noticeId;");
                if($updateQuery){                    
                    echo sendResponse('edit notice success!', "", 200);
                }
                else {
                    echo sendResponse('edit notice failed!', "", 500);
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