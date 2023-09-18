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
    $numberOfDataPerRequest = 20;
    $dataStartIndex = $pageIndex * $numberOfDataPerRequest;  // 데이터를 불러올 시작 범위 index(0부터 시작)

    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
            $today = date('Y-m-d');
    
            if ($tokenData) { 
                // 데이터는 PH_Id 값에 따라 내림차순으로 정렬. 최근 날짜순으로 정렬됨.
                $selectQuery = $db->query("SELECT * FROM PAYMENT_HISTORIES WHERE PH_StudentId = $studentId ORDER BY PH_Id DESC LIMIT $dataStartIndex, $numberOfDataPerRequest;"); 

                $resData;   // 응답으로 보내줄 데이터                
                if ($selectQuery->num_rows > 0){        
                    $paymentHistories = array();
                    while($row = $selectQuery->fetch_assoc()) {      
                        $eachData = [];                 
                        $eachData['ㅑd'] = (int)$row['PH_Id'];
                        
                        // 이용권 종류 입력
                        $ticketType = $row['PH_TicketType'];
                        $usageMonth = $row['PH_UsageMonth'];
                        $ticketName = $usageMonth."개월 ".$ticketType;

                        $eachData['ticketName'] = $ticketName;
                        $eachData['startDate'] = $row['PH_PayedAt'];
                        $eachData['endDate'] = $row['PH_ExpiresAt'];

                        // 이용중 여부 입력
                        $isActive;
                        if ($row['PH_ExpiresAt'] > $today) $isActive = true;
                        else $isActive = false;

                        $eachData['isActive'] = $isActive;

                        array_push($paymentHistories, $eachData);
                    }

                    $totalCount = sizeof($paymentHistories); // 데이터 최종 갯수
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

                    // 응답으로 보내줄 데이터 양식
                    $resData['paymentHistories'] = $paymentHistories;
                    $resData['isLastPage'] = $isLastPage;
                    $resData = json_encode($resData);

                    echo sendResponse('get payment histories success!', $resData, 200);
                }
                else {
                    // 응답으로 보내줄 데이터 양식
                    $resData['paymentHistories'] = [];         
                    $resData['isLastPage'] = true;
                    $resData = json_encode($resData);           
                    echo sendResponse('get payment histories success!', $resData, 200);                
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