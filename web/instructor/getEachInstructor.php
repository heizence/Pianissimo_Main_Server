<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {    
    $instructorId = $_GET['instructorId']; 
    
    if (isset($authorization)) {        
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
    
            if($tokenData) {
                // 데이터는 appUserId 값에 따라 내림차순으로 정렬. 최근 날짜순으로 정렬됨.
                $selectQuery = $db->query("SELECT * FROM INSTRUCTORS WHERE I_Id = $instructorId;"); 
        
                if ($selectQuery->num_rows > 0){        
                    while($row = $selectQuery->fetch_assoc()) {      
                        $data = [];                 
                        $data['instructorId'] = (int)$row['I_Id'];                        
                        $data['instructorName'] = $row['I_Name']; // 이름                        

                        $InstructorPhoneNumber = $row['I_PhoneNumber'];
                        $data['instructorPhoneNumber'] = substr($InstructorPhoneNumber,0,3)."-".substr($InstructorPhoneNumber, 3,4)."-".substr($InstructorPhoneNumber, 6, 4);   // 전화번호

                        $data['instructorRegisteredDate'] = $row['I_RegisteredDate']; // 등록일
                        $data['instructorGenre'] = $row['I_Genre']; // 장르            
                        $data['instructorLessonPerformed'] = (int)$row['I_LessonPerformed'];    // 레슨 횟수
                        
                        // 평균 평점 계산
                        $rateStr = $row['I_Rate'];
                        $totalRate = (int)explode('&', $rateStr)[0];
                        $numberOfPeople = (int)explode('&', $rateStr)[1];
                        ($totalRate === 0) ? $averageRate = 0 : $averageRate = round(($totalRate / $numberOfPeople),1);
                        $data['instructorRate'] = $averageRate;
                    }
                    echo sendResponse('getEachInstructor success!', $data, 200);
                }
                else {
                    echo sendResponse('getEachInstructor failed!', '', 500);                
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