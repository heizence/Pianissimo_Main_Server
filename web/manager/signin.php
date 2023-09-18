<?php 
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $email = $data['email'];    
    $password = $data['password'];    

    $checkQuery = $db->query("SELECT * FROM WEB_USER WHERE WU_Email = '$email' AND WU_Password = '$password'");
    if ($checkQuery->num_rows > 0){        
        $webUserId;
        while($row = $checkQuery->fetch_assoc()) {                           
            $webUserId = $row['WU_Id'];
            error_log($webUserId);
        }
        $jwt = new JWT();
        $token = $jwt->hashing(array(
            'exp' => time() + (60 * 60 * 24 * 365), // 만료기간(1년)
            //'exp' => time() + (10), // 만료기간(10초). 테스트용
            // .이 들어가도 JWT가 분리되지 않기 위한 base64 인코딩
            'webUserId' => base64_encode($webUserId),
            'email' => base64_encode($email), 
            'password' => base64_encode($password) 
        ));
        echo sendResponse('signin success!', $token, 200);
    }
    else {
        echo sendResponse('Invalid username or password!', '', 404);                
    }
}
?>