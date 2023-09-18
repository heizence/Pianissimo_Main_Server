<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    /* 
    데이터를 불러올 시작 날짜
    데이터를 1주일 간격으로 불러옴. 시작 날짜는 월요일 날짜여야 함.
    날짜 형식은 "YYYY-MM-DD"
    */    
    $today = date('Y-m-d');
    $startDate = $today > $_GET['startDate'] ? $today : $_GET['startDate'];    // 현재 주, 또는 다음 주 데이터 불러올 때 오늘보다 이후 데이터만 불러오기    
    //$startDate = $_GET['startDate'];  // for test
    $endDate = $_GET['endDate'];
    $instructorName = $_GET['instructorName'];
    // error_log("startDate : $startDate");
    // error_log("endDate : $endDate");
    // error_log("instructorName : $instructorName");

    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
                
            if ($tokenData) {                                 
                $selectQuery = $db->query("SELECT * FROM LESSON_REGISTERED 
                WHERE LR_InstructorName = '$instructorName' AND LR_Date >= '$startDate' AND LR_Date <= '$endDate' ORDER BY LR_Date"); 
                
                $lessonRegistered = []; // 레슨 데이터를 넣어줄 단일 객체                
                if ($selectQuery->num_rows > 0){                           
                    while($row = $selectQuery->fetch_assoc()) {                 
                        // 데이터 key 값
                        $key = $row['LR_BlockId']; 
                        
                        /* 
                        value 값은 레슨 데이터 고유 id + 레슨 예약한 원생 고유 id
                        레슨이 예약된 상태이면 LR_StudentId 값이 있고 예약되지 않은 상태이면 LR_StudentId 값이 null
                        */
                        $value;
                        if (isset($row['LR_StudentId'])) $value = $row['LR_Id']."&".$row['LR_StudentId'];
                        else $value = $row['LR_Id']."&null";
                        $lessonRegistered[$key] = $value;
                    }

                    $lessonRegistered = json_encode($lessonRegistered);
                    echo sendResponse('getRegisteredLessons success!', $lessonRegistered, 200);
                }
                else {
                    $lessonRegistered[''] = '';
                    $lessonRegistered = json_encode($lessonRegistered);
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