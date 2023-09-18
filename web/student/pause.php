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
                $pauseStartDate = $data['pauseStartDate'];
                $pauseEndDate = $data['pauseEndDate'];

                // 일시정지 일 수
                $dayInterval = (strtotime($pauseEndDate) - strtotime($pauseStartDate)) / (60 * 60 * 24);    // 시작, 끝 날짜 사이 일 수
                $numberOfPauseDay = $dayInterval + 1;   // 시작일 포함이므로 +1 해주기                        

                // 원생 남은 일시정지 일 수 불러오기
                $selectQuery = $db->query("SELECT AU_TicketExpiracyDate, AU_PauseDayLeft FROM APP_USERS WHERE AU_Id = $studentId;");                
                $ticketExpiracyDate;
                $pauseDayLeft;
                if ($selectQuery->num_rows > 0){        
                    while($row = $selectQuery->fetch_assoc()) {
                        $ticketExpiracyDate = $row['AU_TicketExpiracyDate'];                        
                        $pauseDayLeft = (int)$row['AU_PauseDayLeft'];    // 남은 일시정지 일 수
                    }
                }
                else {
                    echo sendResponse('student pause failed!', "", 500);
                }                
                
                // 남은 일시정지 일 수(PauseDayLeft) 갱신해 주기
                $updatedPauseDayLeft = $pauseDayLeft - $numberOfPauseDay;

                // 결제한 이용권 만료일 설정
                $dateTime = new DateTime($ticketExpiracyDate);
                $ticketExpiresAt = $dateTime->modify("+$numberOfPauseDay days");
                $ticketExpiresAt = $ticketExpiresAt->format('Y-m-d');   

                /*
                일시정지 시작일이 오늘이 아니라 이후 날짜일 수 있으므로 원생 학원 이용 상태는 갱신하지 않음.
                원생 데이터 불러오기 요청 시 일시정지 시작 날짜와 비교해서 상태 갱신해 주기
                */
                $updateQuery = $db->query("UPDATE APP_USERS 
                SET AU_Status = '홀딩중', AU_PauseDayLeft = '$updatedPauseDayLeft', AU_PauseStartDate = '$pauseStartDate', AU_PauseEndDate = '$pauseEndDate', AU_TicketExpiracyDate = '$ticketExpiresAt' WHERE AU_Id = $studentId;");
                if($updateQuery){
                    echo sendResponse('student pause success!', "", 200);
                }
                else {
                    echo sendResponse('student pause failed!', "", 500);
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