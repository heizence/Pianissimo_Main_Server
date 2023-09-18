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
                $lessonId = (int)$_POST['lessonId'];
                $rateNumber = (int)$_POST['rateNumber'];

                // 해당 레슨을 진행한 강사 이름 및 평점 조회
                $selectQuery = $db->query("SELECT LR_Id FROM LESSON_REGISTERED WHERE LR_Id = $lessonId;");

                if ($selectQuery->num_rows > 0){
                    while($row = $selectQuery->fetch_assoc()) {                                                                                           
                        // 진행한 레슨 평점 갱신 query
                        $updateQuery = $db->query("UPDATE LESSON_REGISTERED SET LR_Rate = '$rateNumber' WHERE LR_Id = $lessonId;");

                        if ($updateQuery) {
                            echo sendResponse('rate success!', "", 200);
                        }
                        else {
                            echo sendResponse('rate failed!', "", 500);
                        }
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