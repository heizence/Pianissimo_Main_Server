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
                $date = $data['date'];
                $startTime = (int)$data['startTime'];
                $instructors = $data['instructors'];
                $blockId = $date."&".$startTime;

                $json = json_encode($instructors[0]);
                error_log("instructors : $json");

                for ($i=0; $i<sizeof($instructors); $i++) {
                    $instructorId = (int)$instructors[$i]['instructorId'];
                    $instructorName = $instructors[$i]['instructorName'];                    

                    $insertQuery = $db->query("INSERT INTO LESSON_REGISTERED (LR_Date, LR_StartTime, LR_InstructorId, LR_InstructorName, LR_Status, LR_BlockId, LR_Rate) 
                    VALUES('$date', $startTime, $instructorId, '$instructorName', '예약 가능', '$blockId', 0);");

                    // for test
                    if($insertQuery === true){}
                    else {}                    
                }
                echo sendResponse('register lesson success!', "", 200);
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