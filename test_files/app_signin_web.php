<?php 
include '../modules/Config.php';
include '../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $email = $data['email'];    
    $password = $data['password'];    

    $checkQuery = $db->query("SELECT * FROM APP_USERS WHERE AU_Email = '$email' AND AU_Password = '$password'");
    if ($checkQuery->num_rows > 0){
        // for test
        while($row = $checkQuery->fetch_assoc()) {                           
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
        $jwt = new JWT();
        $token = $jwt->hashing(array(
            'exp' => time() + (60 * 60 * 24 * 365), // 만료기간(1년)
            //'exp' => time() + (10), // 만료기간(10초). 테스트용
            // .이 들어가도 JWT가 분리되지 않기 위한 base64 인코딩
            'appUserId' => base64_encode($AU_Id),
            'email' => base64_encode($AU_Email), 
            'password' => base64_encode($password) 
        ));
        
        // 회원 정보들 보내주기
        $resData['token'] = $token;
        $resData['AU_Id']= $AU_Id;
        $resData['AU_Email']= $AU_Email;
        $resData['AU_Name'] = $AU_Name;
        $resData['AU_PhoneNumber'] = $AU_PhoneNumber;
        $resData['AU_ProfileImg'] = $AU_ProfileImg;
        $resData['AU_Status'] = $AU_Status;
        $resData['AU_TicketType'] = $AU_TicketType;
        $resData['AU_TicketStartDate'] = $AU_TicketStartDate;
        $resData['AU_TicketExpiracyDate'] = $AU_TicketExpiracyDate;
        $resData['AU_LessonsLeft'] = $AU_LessonsLeft;
        $resData['AU_RegisteredDate'] = $AU_RegisteredDate;

        echo sendResponse('signin success!', $resData, 200);
    }
    else {
        echo sendResponse('Invalid username or password!', '', 404);                
    }
}
?>