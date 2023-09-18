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
                $studentId = $data['studentId'];
                
                // 원생 남은 일시정지 일 수 불러오기
                $selectQuery = $db->query("SELECT AU_TicketExpiracyDate, AU_PauseDayLeft, AU_PauseEndDate FROM APP_USERS WHERE AU_Id = $studentId;");
                $ticketExpiracyDate;
                $pauseDayLeft;
                $pauseEndDate;
                if ($selectQuery->num_rows > 0){        
                    while($row = $selectQuery->fetch_assoc()) {
                        $ticketExpiracyDate = $row['AU_TicketExpiracyDate'];
                        $pauseDayLeft = (int)$row['AU_PauseDayLeft'];    // 남은 일시정지 일 수
                        $pauseEndDate = $row['AU_PauseEndDate'];
                    }

                    // 이용 일시정지 기간 도중 해제하는 것이므로 끝나는 날짜와 오늘 날짜 비교해서 남은 일시정지 일 수(PauseDayLeft) 갱신해 주기
                    $today = date('Y-m-d');
                    $dayInterval = (strtotime($pauseEndDate) - strtotime($today)) / (60 * 60 * 24);    // 이용정지 끝 날짜와 오늘 날짜 사이 일 수
                    $numberOfDayToAdd = $dayInterval + 1;   // 마지막 날 포함이므로 +1 해주기                    

                    // 남은 일시정지 일 수(PauseDayLeft) 갱신해 주기
                    $updatedPauseDayLeft = $pauseDayLeft + $dayInterval;

                    // 결제한 이용권 만료일 갱신
                    $dateTime = new DateTime($ticketExpiracyDate);
                    $ticketExpiresAt = $dateTime->modify("-$dayInterval days");
                    $ticketExpiresAt = $ticketExpiresAt->format('Y-m-d');
                                        
                    $updateQuery = $db->query("UPDATE APP_USERS 
                    SET AU_PauseDayLeft = $updatedPauseDayLeft, AU_PauseStartDate = null, AU_PauseEndDate = null, AU_TicketExpiracyDate = '$ticketExpiresAt', AU_Status = '이용중' WHERE AU_Id = $studentId;");
                    if($updateQuery){
                        echo sendResponse('student resume success!', "", 200);
                    }
                    else {
                        echo sendResponse('student resume failed!', "", 500);
                    }  
                }
                else {
                    echo sendResponse('student resume failed!', "", 500);
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