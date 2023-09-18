<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    /* 데이터를 불러올 시작 범위 index(0부터 시작)
    클라이언트(앱, 웹 모두 포함) 측 page 버튼의 index 에 해당함.
    변수명 변경 시 클라이언트(앱, 웹 모두 포함) 쪽 변수명과 반드시 같이 변경할 것.
    */        
    $pageIndex = $_GET['pageIndex'];  
    $studentId = $_GET['studentId'];  
    $dataStartIndex = $pageIndex * 10;  // 데이터를 불러올 시작 범위 index(0부터 시작)

    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
            $today = date('Y-m-d');

            // 현재 시간 불러오기
            $now = new DateTime();            
            $hour = (int)$now->format('H');   
    
            if ($tokenData) { 
                // 데이터는 LR_Id 값에 따라 내림차순으로 정렬. 최근 날짜순으로 정렬됨.
                $selectQuery = $db->query("SELECT * FROM LESSON_REGISTERED
                WHERE LR_StudentId = $studentId AND LR_Date <= '$today' 
                ORDER BY LR_Id DESC LIMIT $dataStartIndex, 10;"); 

                $resData;   // 응답으로 보내줄 데이터
                if ($selectQuery->num_rows > 0){        
                    $lessonHistories = array();
                    while($row = $selectQuery->fetch_assoc()) {      
                        if ($row['LR_Date'] == $today) {                            
                            $date = $row['LR_Date'];
                            //error_log("today! : $date");

                            if ((int)$row['LR_StartTime'] > $hour) {
                                continue;
                            }
                        }

                        $eachData = [];                 
                        $eachData['id'] = (int)$row['LR_Id'];                    
                        $eachData['instructorName'] = $row['LR_InstructorName'];
                        $eachData['lessonDate'] = $row['LR_Date'];
                        $eachData['lessonTime'] = (int)$row['LR_StartTime'];
                        array_push($lessonHistories, $eachData);
                    }
                    
                    // 응답으로 보내줄 데이터 양식
                    $resData['lessonHistories'] = $lessonHistories;
                    $resData['counts'] = sizeof($lessonHistories);

                    echo sendResponse('get lesson histories success!', $resData, 200);
                }
                else {
                    // 응답으로 보내줄 데이터 양식
                    $resData['lessonHistories'] = [];
                    $resData['counts'] = 0;
                    echo sendResponse('get lesson histories success!', $resData, 200);                
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