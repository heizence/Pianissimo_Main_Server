<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    /* 
    데이터를 불러올 시작 날짜
    데이터를 1주일 간격으로 불러옴. 시작 날짜는 월요일 날짜여야 함.
    날짜 형식은 "YYYY-MM-DD"
    */    
    $startDate = $_GET['startDate'];    
    $endDate = $_GET['endDate'];

    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
                
            if ($tokenData) {                 
                $startDateSplited = explode("-", $startDate);   // 시작 날짜 split 하기               
                $selectQuery = $db->query("SELECT * FROM LESSON_REGISTERED WHERE LR_Date >= '$startDate' AND LR_Date <= '$endDate'"); 
                
                $lessonRegistered = []; // 레슨 데이터를 넣어줄 단일 객체
                if ($selectQuery->num_rows > 0){       
                    while($row = $selectQuery->fetch_assoc()) {      
                        $key = $row['LR_BlockId'];
                        
                        /* 
                        key 에 해당하는 value 가 없을 때만 value 값 배열 생성해서 할당해 주기
                        하나의 key 에 배열 형식으로 2개 이상의 데이터가 들어갈 수 있음
                        */
                        if (!isset($lessonRegistered[$key])) $lessonRegistered[$key] = array(); 
                        $value = $row['LR_InstructorName']." - ".$row['LR_Status']."&".$row['LR_Id'];
                        array_push($lessonRegistered[$key], $value);
                    }

                    // 전체 데이터 갯수 구하기
                    $getCountquery = $db->query("SELECT COUNT(LR_Id) as counts FROM LESSON_REGISTERED WHERE LR_Date >= '$startDate' AND LR_Date <= '$endDate'");
                    $selRow  = mysqli_fetch_array($getCountquery);
                    $totalRows = $selRow[0];

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