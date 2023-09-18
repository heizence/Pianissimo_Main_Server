<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {    
    $studentId = $_GET['studentId']; 
    
    if (isset($authorization)) {        
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
    
            if($tokenData) {
                // 데이터는 appUserId 값에 따라 내림차순으로 정렬. 최근 날짜순으로 정렬됨.
                $selectQuery = $db->query("SELECT * FROM APP_USERS WHERE AU_Id = $studentId;"); 
        
                if ($selectQuery->num_rows > 0){        
                    while($row = $selectQuery->fetch_assoc()) {      
                        $data = [];                 
                        $data['studentId'] = (int)$row['AU_Id'];                        
                        $data['studentName'] = $row['AU_Name']; // 이름
                        $data['studentEmail'] = $row['AU_Email'];   // 아이디(이메일)

                        $studentPhoneNumber = $row['AU_PhoneNumber'];

                        $data['studentPhoneNumber'] = substr($studentPhoneNumber,0,3)."-".substr($studentPhoneNumber, 3,4)."-".substr($studentPhoneNumber, 7, 4);   // 전화번호                        
                        $data['studentRegisteredDate'] = $row['AU_RegisteredDate']; // 등록일

                        $data['studentPauseStartDate'] = $row['AU_PauseStartDate'];    // 일시정지 시작일(홀딩중 일때만 해당)
                        $data['studentPauseEndDate'] = $row['AU_PauseEndDate'];    // 일시정지 끝 일(홀딩중 일때만 해당)
                        $data['studentStatus'] = $row['AU_Status'];    // 원생 학원이용 여부
                        
                        $data['studentTicketExpiracyDate'] = $row['AU_TicketExpiracyDate'];    // 이용권 만료 날짜
                        $data['studentTicketType'] = ($row['AU_TicketType'] !== null) ? $row['AU_TicketType'] : "결제내역 없음";   // 이용권 종류                        

                        // 이용권 이용 기간(시작, 만료)
                        $studentTicketStartDate = $row['AU_TicketStartDate']; 
                        $studentTicketExpiracyDate = $row['AU_TicketExpiracyDate']; 

                        if ($studentTicketStartDate === null) {
                            $data['studentUsageDate'] = "해당없음";
                        }
                        else {
                            $data['studentUsageDate'] = $studentTicketStartDate." ~ ".$studentTicketExpiracyDate;
                        }
                                                
                        $data['studentLessonsLeft'] = ($row['AU_LessonsLeft'] !== null) ? $row['AU_LessonsLeft'] : "해당없음";   // 남은 레슨 횟수
                        $data['studentPauseDayLeft'] = (int)$row['AU_PauseDayLeft'];    // 남은 일시정지 일 수
        
                        // 원생 학원이용 여부 상태 체크 및 수정                                            
                        $updateQueryString = null;
                        if (isset($studentTicketExpiracyDate) && date('Y-m-d') > $studentTicketExpiracyDate) {
                            // 이용권 만료 날짜가 지나면 '기간 만료' 로 표시. 중복 갱신 방지 처리 포함
                            if ($row['AU_Status'] != '기간 만료') {
                                $updateQueryString = "UPDATE APP_USERS SET AU_Status = '기간 만료' WHERE AU_Id = $studentId;";                                
                            }
                        }
                        else if (isset($row['AU_PauseEndDate']) && date('Y-m-d') > $row['AU_PauseEndDate']) {                            
                            // 일시정지 끝 일이 지나면 '이용중' 으로 상태 갱신. 중복 갱신 방지 처리 포함 
                            if ($row['AU_Status'] != '이용중') {
                                $updateQueryString = "UPDATE APP_USERS SET AU_Status = '이용중', AU_PauseStartDate = null, AU_PauseEndDate = null WHERE AU_Id = $studentId;";
                            }
                        }
                        else if (isset($row['AU_PauseStartDate']) && date('Y-m-d') >= $row['AU_PauseStartDate']) {
                            // 일시정지 시작일에 해당하면 '홀딩중' 으로 상태 갱신. 중복 갱신 방지 처리 포함
                            if ($row['AU_Status'] != '홀딩중') {
                                $updateQueryString = "UPDATE APP_USERS SET AU_Status = '홀딩중' WHERE AU_Id = $studentId;";
                            }
                        }

                        // 상태 업데이트
                        if (isset($updateQueryString)) {
                            error_log("상태 업데이트! : $updateQueryString");
                            $updateQuery = $db->query($updateQueryString);
                        }
                    }
                    echo sendResponse('getEachStudent success!', $data, 200);
                }
                else {
                    echo sendResponse('getEachStudent failed!', '', 500);                
                }
            }
            else {
                echo sendResponse('Invalid Token!', '', 401);
            }          
        } else {          
            echo sendResponse("No token in Authorization header!", "", 401);
        }
    }
    else {        
        echo sendResponse("No Authorization in header!", "", 401);
    }
}
?>