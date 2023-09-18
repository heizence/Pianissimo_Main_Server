<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
    
            if ($tokenData) {                 
                $selectQuery = $db->query("SELECT I_Id, I_Name FROM INSTRUCTORS;"); 
                $instructorsName = array();   // 응답으로 보내줄 데이터
                if ($selectQuery->num_rows > 0){
                    while($row = $selectQuery->fetch_assoc()) {                                                
                        $instructorName = $row['I_Name'];
                        array_push($instructorsName, $instructorName);
                    }
                    $instructorsName = json_encode($instructorsName);
                    echo sendResponse('get instructor names success!', $instructorsName, 200);
                }
                else {
                    $instructorsName = json_encode([]);
                    echo sendResponse('get instructor names success!!', $instructorsName, 200);                
                }
            }
            else {
                echo sendResponse('Invalid Token!', '', 401);
            }
          
        } else {
            echo sendResponse("No token in header!", "", 401);
        }
    }
    else {        
        echo sendResponse("No Authorization in header!", "", 401);
    }
}
?>