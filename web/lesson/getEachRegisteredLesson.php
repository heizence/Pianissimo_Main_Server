<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {    
    $blockId = $_GET['blockId'];        

    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
                
            if ($tokenData) {                                  
                $selectQuery = $db->query("SELECT * FROM LESSON_REGISTERED WHERE LR_BlockId = '$blockId'"); 
                
                $lessonRegistered = array(); // 레슨 데이터를 넣어줄 단일 객체
                if ($selectQuery->num_rows > 0){       
                    while($row = $selectQuery->fetch_assoc()) {                                                      
                        $instructor = [];
                        $instructor['instructorId'] = (int)$row['LR_InstructorId'];
                        $instructor['instructorName'] = $row['LR_InstructorName'];                        
                        
                        array_push($lessonRegistered, $instructor);
                    }
                    echo sendResponse('getRegisteredLessons success!', $lessonRegistered, 200);
                }
                else {
                    echo sendResponse('getRegisteredLessons success!', $lessonRegistered, 200);                
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