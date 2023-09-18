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
    $numberOfDataPerRequest = 20;   // 요청 한 번당 불러올 데이터의 갯수
    $dataStartIndex = $pageIndex * $numberOfDataPerRequest;  // 데이터를 불러올 시작 범위 index(0부터 시작)

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
                ORDER BY LR_Id DESC LIMIT $dataStartIndex, $numberOfDataPerRequest;"); 
                
                if ($selectQuery->num_rows > 0){        
                    $lessonHistories = array();
                    while($row = $selectQuery->fetch_assoc()) {      
                        /* 
                        레슨 예약 날짜가 오늘이고 레슨 시작 시간이 조회하는 시점의 시각보다 늦은 시간이면 조회 대상에서 제외.
                        예) 오늘 날짜와 시간 : 2023년 2월 20일 20시
                        레슨 데이터 1 : 2023년 2월 20일 19시
                        레슨 데이터 2 : 2023년 2월 20일 22시
                        3가지 데이터 모두 날짜는 똑같음. 레슨 데이터 1의 경우 시간이 조회 시점의 시간보다 빠르므로 조회 대상에 포함,
                        레슨 데이터 2의 경우 조회 시점의 시간보다 늦으므로 조회 대상에서 제외
                        */
                        if ($row['LR_Date'] == $today) {                            
                            $date = $row['LR_Date'];
                            //error_log("today! : $date");

                            if ((int)$row['LR_StartTime'] > $hour) {
                                continue;
                            }
                        }

                        // key 값 변수명은 클라이언트에서 받는 데이터의 변수명과 통일하야 함. 변경 시 클라이언트 쪽과 반드시 같이 변경하기
                        $eachData = [];                 
                        $eachData['id'] = (int)$row['LR_Id'];                        
                        $eachData['instructorName'] = $row['LR_InstructorName'];
                        $eachData['date'] = $row['LR_Date'];
                        $eachData['startTime'] = $row['LR_StartTime'];
                        $eachData['rate'] = $row['LR_Rate'];
                        array_push($lessonHistories, $eachData);
                    }            

                    $totalCount = sizeof($lessonHistories); // 조건에 해당되는 레슨 데이터 최종 갯수
                    $isLastPage;    // 현재 페이지가 마지막 페이지인지 아닌지 식별

                    /*
                    총 데이터 갯수와 현재 페이지까지 누적된 데이터 갯수를 비교하여 마지막 페이지인지 아닌지 식별.
                    예) 총 데이터가 25개, 페이지가 0,1,2 총 3개, 한번에 불러오는 데이터가 10개 일 때
                    현재 페이지가 1일 때 -> 25 - 10 * (1+1) = 5 > 0 -> 마지막 페이지 아님.
                    현재 페이지가 2일 때 -> 25 - 10 * (2+1) = -5 < 0 -> 마지막 페이지.
                    */
                    
                    $cal = $totalCount - $numberOfDataPerRequest * ($pageIndex + 1);
                    // error_log("total row : $totalCount");
                    // error_log("pageIndex : $pageIndex");
                    // error_log("cal : $cal");
                    if ($totalCount - $numberOfDataPerRequest * ($pageIndex + 1) > 0) $isLastPage = false;
                    else $isLastPage = true;

                    $resData['myLessonHistory'] = $lessonHistories;
                    $resData['isLastPage'] = $isLastPage;
                    $resData = json_encode($resData);

                    echo sendResponse('get lesson histories success!', $resData, 200);
                }
                else {
                    // 응답으로 보내줄 데이터 양식
                    $lessonHistories = [];
                       // 처음부터 데이터가 없었던 경우에 해당.  
                       $resData['myLessonHistory'] = [];
                       $resData['isLastPage'] = true;
                       $resData = json_encode($resData);    
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