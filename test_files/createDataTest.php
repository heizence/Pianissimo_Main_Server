<?php 
/*
페이징 처리 테스트 및 기타 테스트를 위한 데이터 생성용 테스트 파일
*/

include './modules/Config.php';
include './modules/JWT.php';

/*********************** 원생 등록 테스트 ************************/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {    
    $type = $_POST['type'];    
    
    switch ($type) {
        case 'appUser':
            $appUsersArr = array();
            for ($i = 1; $i<=255; $i++) {
                $email = "test$i@test.com";
                $password = hash("sha256", '1234'); // 원생용 앱 계정 초기 비밀번호는 1234 로 지정
                $name = "이름$i";
                $phoneNumber = "01012345678";    
                $registeredDate = date("Y-m-d");    // 원생 등록 날짜(현재 날짜)
        
                $insertQuery = $db->query("
                INSERT INTO APP_USERS (AU_Email, AU_Password, AU_Name, AU_PhoneNumber, AU_Status, AU_PauseDayLeft, AU_RegisteredDate)
                VALUES('$email', '$password', '$name', '$phoneNumber', '결제내역 없음', 0, '$registeredDate')
                ");
                
                if($insertQuery === true){
                    $appUser = [];
                    $appUser['studentEmail'] = $email;
                    $appUser['studentName'] = $name;
                    $appUser['studentPhoneNumber'] = $phoneNumber;
                    $appUser['studentStatus'] = '결제내역 없음';
                    $appUser['studentLessonsLeft'] = '해당없음';
                    $appUser['studentRegisteredDate'] = $registeredDate;
        
                    array_push($appUsersArr, $appUser);
                }    
            }
        
            $resData['data'] = $appUsersArr;        
            
            echo sendResponse('success!', $resData, 200);            
            break;
        case 'notices':
            $noticesArr = array();
            for ($i = 1; $i<=30; $i++) {
                $title = "공지사항 제목 $i";                
                $contents = "공지사항 내용! $i";                
                $writtenDate = date("Y-m-d");    // 원생 등록 날짜(현재 날짜)
        
                $insertQuery = $db->query("INSERT INTO NOTICES (N_Title, N_Contents, N_WrittenDate)
                VALUES('$title', '$contents', '$writtenDate');
                ");
                
                if($insertQuery === true){
                    $notice = [];
                    $notice['title'] = $title;
                    $notice['contents'] = $contents;
                    $notice['writtenDate'] = $writtenDate;
        
                    array_push($noticesArr, $notice);
                }    
            }        
            $resData['data'] = $noticesArr;                    
            echo sendResponse('success!', $resData, 200);            
            break;
        case 'lessonHistories':
            $lessonHistoriesArr = array();
            for ($i = 1; $i<=255; $i++) {
                $studentId = 264;
                $studentName = "원생$i";
                $instructorId = 1;
                $instructorName = "강사$i";
                $lessonDate = date("Y-m-d"); 
                $lessonTime = "13:00 ~ 14:00";                
        
                $insertQuery = $db->query("
                INSERT INTO LESSON_HISTORIES (LH_StudentId, LH_StudentName, LH_InstructorId, LH_InstructorName, LH_Date, LH_Time) 
                VALUES('$studentId', '$studentName', '$instructorId', '$instructorName', '$lessonDate', '$lessonTime')
                ");
                
                if($insertQuery === true){    
                    $lessonHistory = [];
                    $lessonHistory['studentName'] = $studentName;
                    $lessonHistory['instructorName'] = $instructorName;
                    $lessonHistory['lessonDate'] = $lessonDate;
                    $lessonHistory['lessonTime'] = $lessonTime;
        
                    array_push($lessonHistoriesArr, $lessonHistory);
                }    
            }
        
            $resData['data'] = $lessonHistoriesArr;        
            
            echo sendResponse('success!', $resData, 200);            
            break;    

        case 'paymentHistories':
            $paymentHistoriesArr = array();
            for ($i = 1; $i<=255; $i++) {
                $studentId = 264;
                $ticketType = "이용권$i";
                $usageMonth = 2;
                $payedAt = date("Y-m-d"); 
                $d=strtotime("+$usageMonth Months");
                $expiresAt = date("Y-m-d", $d);                              
        
                $insertQuery = $db->query("
                INSERT INTO PAYMENT_HISTORIES (PH_StudentId, PH_TicketType, PH_UsageMonth, PH_PayedAt, PH_ExpiresAt) 
                VALUES('$studentId', '$ticketType', '$usageMonth', '$payedAt', '$expiresAt')
                ");
                
                if($insertQuery === true){    
                    $paymentHistory = [];
                    $paymentHistory['ticketType'] = $ticketType;
                    $paymentHistory['usageMonth'] = $usageMonth;
                    $paymentHistory['payedAt'] = $payedAt;
                    $paymentHistory['expiresAt'] = $expiresAt;
        
                    array_push($paymentHistoriesArr, $paymentHistory);
                }    
            }
        
            $resData['data'] = $paymentHistoriesArr;        
            
            echo sendResponse('success!', $resData, 200);            
            break;  

        case 'instructors':
            $instructorsArr = array();
            for ($i = 1; $i<=255; $i++) {
                $name = "강사$i";
                $phoneNumber = "01012345678";
                $genre = "클래식";
                $lessonPerformed = $i;
                $rate = "15&4";
                $registeredDate = date("Y-m-d");    // 원생 등록 날짜(현재 날짜)
        
                $insertQuery = $db->query("
                INSERT INTO INSTRUCTORS (I_Name, I_PhoneNumber, I_Genre, I_LessonPerformed, I_Rate, I_RegisteredDate) 
                VALUES('$name', '$phoneNumber', '$genre', '$lessonPerformed', '$rate', '$registeredDate')
                ");
                
                if($insertQuery === true){    
                    $instructor = [];
                    $instructor['name'] = $name;
                    $instructor['phoneNumber'] = $phoneNumber;
                    $instructor['genre'] = $genre;
                    $instructor['lessonPerformed'] = $lessonPerformed;
                    $instructor['rate'] = $rate;
                    $instructor['registeredDate'] = $registeredDate;
        
                    array_push($instructorsArr, $instructor);
                }    
            }
        
            $resData['data'] = $instructorsArr;        
            
            echo sendResponse('success!', $resData, 200);            
            break;  
        case 'lessonRegistered':
            $lessonRegisteredArr = array();
            $startTime = 12;
            for ($i = 1; $i<=30; $i++) {

                $date;               
                $i < 10 ? $date = "2023-02-0$i" : $date = "2023-02-$i";               

                $instructorName = "강사$i";                
                $blockId = "$date&$startTime";                
        
                $insertQuery = $db->query("
                INSERT INTO LESSON_REGISTERED (LR_Date, LR_StartTime, LR_InstructorName, LR_Status, LR_BlockId)
                VALUES('$date', '$startTime', '$instructorName', '예약 가능', '$blockId')
                ");                
                
                if($insertQuery === true){    
                    $lessonRegistered = [];
                    $lessonRegistered['date'] = $date;
                    $lessonRegistered['startTime'] = $startTime;
                    $lessonRegistered['instructorName'] = $instructorName;
                    $lessonRegistered['status'] = '예약 가능';
                    $lessonRegistered['blockId'] = $blockId;
        
                    array_push($lessonRegisteredArr, $lessonRegistered);
                }  
                
                $startTime += 1;
                if ($startTime > 22) $startTime = 12;
            }
        
            $resData['data'] = $lessonRegisteredArr;        
            
            echo sendResponse('success!', $resData, 200);            
            break;
        case 'practiceRooms':
            $practiceRoomsArr = array();
            for ($i = 1; $i<=20; $i++) {
                $name = "연습실$i";            
                $registeredDate = date("Y-m-d");    // 원생 등록 날짜(현재 날짜)
        
                $insertQuery = $db->query("INSERT INTO PRACTICE_ROOMS (PR_Name, PR_RegisteredDate) VALUES('$name', '$registeredDate');");
                
                if($insertQuery === true){    
                    $practiceRoom = [];
                    $practiceRoom['name'] = $name;
                    $practiceRoom['registeredDate'] = $registeredDate;
        
                    array_push($practiceRoomsArr, $practiceRoom);
                }    
            }
        
            $resData['data'] = $practiceRoomsArr;        
            
            echo sendResponse('success!', $resData, 200);            
            break;  
        default:
            echo sendResponse('failed!', 'Wrong type!', 404);
            break;
    }
}
?>