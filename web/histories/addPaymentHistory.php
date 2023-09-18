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
                $studentId = (int)$data['studentId'];
                $ticketType = $data['ticketType'];        
                $ticketMonth = (int)$data['ticketMonth'];                 
                $today = date("Y-m-d");

                // 결제한 이용권 만료일 설정
                $dateTime = new DateTime($today);
                $ticketExpiresAt = $dateTime->modify("+$ticketMonth month");
                $ticketExpiresAt = $ticketExpiresAt->format('Y-m-d');                

                // 결제내역 추가
                $insertQuery = $db->query("INSERT INTO PAYMENT_HISTORIES
                (PH_StudentId, PH_TicketType, PH_UsageMonth, PH_PayedAt, PH_ExpiresAt)
                VALUES($studentId, '$ticketType 이용권', $ticketMonth, '$today', '$ticketExpiresAt');
                ");
                // 추가 성공 시 원생 정보에서 이용권 관련 정보 업데이트하기
                if($insertQuery === true){
                    // 원생 정보 불러오기
                    $selectQuery = $db->query("SELECT * FROM APP_USERS WHERE AU_Id = $studentId;");                
                    
                    $statusToUpdate;    // 학원 이용 상태
                    $ticketTypeToUpdate;    // 이용권 종류
                    $ticketStartDateToUpdate;   // 이용권 시작일
                    $ticketExpiracyDateToUpdate;    // 이용권 만료 기간
                    $lessonsLeftToUpdate;    // 남은 레슨 수
                    $pauseDayLeftToUpdate;  // 일시정지 남은 횟수         

                    if ($selectQuery->num_rows > 0){        
                        while($row = $selectQuery->fetch_assoc()) {
                            $previous_status = $row['AU_Status'];
                            $previous_ticketType = $row['AU_TicketType'];
                            $previous_ticketStartDate = $row['AU_TicketStartDate'];                            
                            $previous_ticketExpiracyDate = $row['AU_TicketExpiracyDate'];
                            $previous_lessonLeft = (int)$row['AU_LessonsLeft'];
                            $previous_pauseDayLeft = (int)$row['AU_PauseDayLeft'];    // 남은 일시정지 일 수

                            // 경우에 따라 정보 업데이트 해 주기
                            
                            // 학원이용 상태 갱신
                            if ($previous_status != '홀딩중') $statusToUpdate = '이용중';
                            
                            // 이용권 종류 갱신
                            if ($previous_ticketType == '레슨 이용권') {
                                $ticketTypeToUpdate = '레슨 이용권';
                            } else {
                                $ticketTypeToUpdate = "$ticketType 이용권";  // 기존 종류가 연습실 이용권의 경우 새로 결제한 이용권 종류로 갱신
                            }
                            
                            // 만료 기간 갱신
                            if (isset($previous_ticketExpiracyDate)) {                                
                                $dateTime = new DateTime($previous_ticketExpiracyDate);
                                $ticketExpiracyDateToUpdate = $dateTime->modify("+$ticketMonth month");
                                $ticketExpiracyDateToUpdate = $ticketExpiracyDateToUpdate->format('Y-m-d');                                   
                            }
                            else {                                
                                $dateTime = new DateTime($today);
                                $ticketExpiracyDateToUpdate = $ticketExpiresAt;                                
                            }

                            // 레슨 수 갱신
                            if ($ticketType == '레슨') {
                                if (isset($previous_lessonLeft)) {
                                    $lessonsLeftToUpdate = $previous_lessonLeft + $ticketMonth * 4;
                                }
                                else {
                                    $lessonsLeftToUpdate = $ticketMonth * 4;
                                }
                            }
                            else {
                                $lessonsLeftToUpdate = $previous_lessonLeft;
                            }
                            // 남은 일시정지 수 갱신
                            $pauseDayLeftToUpdate = (int) ($previous_pauseDayLeft + $ticketMonth * 7);

                            // 이용권 시작일 갱신
                            if (isset($previous_ticketStartDate)) {
                                $ticketStartDateToUpdate = $previous_ticketStartDate;
                            }
                            else {
                                $ticketStartDateToUpdate = $today;
                            }
                        }
                    }

                    $updateQuery = $db->query("UPDATE APP_USERS
                    SET AU_Status = '$statusToUpdate', AU_TicketType = '$ticketTypeToUpdate', AU_TicketStartDate = '$ticketStartDateToUpdate', 
                    AU_TicketExpiracyDate = '$ticketExpiracyDateToUpdate', AU_LessonsLeft = $lessonsLeftToUpdate, AU_PauseDayLeft = $pauseDayLeftToUpdate
                    WHERE AU_Id = $studentId");

                    if($updateQuery === true){                        
                        echo sendResponse('add payment success!', "", 200);
                    }
                    else {
                        echo sendResponse('add payment failed!', "", 500);    
                    }                    
                }
                else {
                    echo sendResponse('add payment failed!', "", 500);
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