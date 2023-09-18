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
    
            if ($tokenData) { 
                // 데이터는 PH_Id 값에 따라 내림차순으로 정렬. 최근 날짜순으로 정렬됨.
                $selectQuery = $db->query("SELECT * FROM PAYMENT_HISTORIES WHERE PH_StudentId = $studentId ORDER BY PH_Id DESC LIMIT $dataStartIndex, 10;"); 

                $resData;   // 응답으로 보내줄 데이터
                if ($selectQuery->num_rows > 0){        
                    $paymentHistories = array();
                    while($row = $selectQuery->fetch_assoc()) {      
                        $eachData = [];                 
                        $eachData['historyId'] = (int)$row['PH_Id'];                        
                        $eachData['studentId'] = $row['PH_StudentId'];
                        $eachData['ticketType'] = $row['PH_TicketType'];
                        $eachData['usageMonth'] = $row['PH_UsageMonth'];
                        $eachData['payedAt'] = $row['PH_PayedAt'];
                        $eachData['expiresAt'] = $row['PH_ExpiresAt'];                        
                        array_push($paymentHistories, $eachData);
                    }

                    // 전체 데이터 갯수 구하기
                    $getCountquery = $db->query("SELECT COUNT(PH_Id) as counts FROM PAYMENT_HISTORIES");
                    $selRow  = mysqli_fetch_array($getCountquery);
                    $totalRows = $selRow[0];

                    // 응답으로 보내줄 데이터 양식
                    $resData['paymentHistories'] = $paymentHistories;
                    $resData['counts'] = (int)$totalRows;

                    echo sendResponse('get payment histories success!', $resData, 200);
                }
                else {
                    // 응답으로 보내줄 데이터 양식
                    $resData['paymentHistories'] = [];
                    $resData['counts'] = 0;
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