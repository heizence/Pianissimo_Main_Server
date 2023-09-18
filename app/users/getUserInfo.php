<?php 
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {    
    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);   

            if ($tokenData) {              
                $studentId = (int)$_GET['studentId'];
                
                // 사용자 정보 불러오기
                $selectQuery = $db->query("SELECT * FROM APP_USERS WHERE AU_Id = $studentId");

                if ($selectQuery->num_rows > 0){
                    while($row = $selectQuery->fetch_assoc()) {                                                                                           
                        $AU_Id = $row['AU_Id'];
                        $AU_Email = $row['AU_Email'];
                        $AU_Name = $row['AU_Name'];        
                        $AU_PhoneNumber = $row['AU_PhoneNumber'];          
                        $AU_ProfileImg = $row['AU_ProfileImg'];
                        $AU_Status = $row['AU_Status'];
                        $AU_TicketType = $row['AU_TicketType'];
                        $AU_TicketStartDate = $row['AU_TicketStartDate'];
                        $AU_TicketExpiracyDate = $row['AU_TicketExpiracyDate'];
                        $AU_LessonsLeft = $row['AU_LessonsLeft'];
                        $AU_RegisteredDate = $row['AU_RegisteredDate'];     
                    }
                    
                    $resData['AU_Email']= $AU_Email;
                    $resData['AU_Name'] = $AU_Name;
                    $resData['AU_ProfileImg'] = $AU_ProfileImg;
                    $resData['AU_TicketStartDate'] = $AU_TicketStartDate;
                    $resData['AU_TicketExpiracyDate'] = $AU_TicketExpiracyDate;
            
                    $resData = json_encode($resData);
                    echo sendResponse('get user info success!', $resData, 200);
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