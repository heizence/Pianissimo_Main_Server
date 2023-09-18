<?php 
include '../../modules/Config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $email = $data['email'];    
    $password = $data['password'];
    $phoneNumber = $data['phoneNumber'];    

    // 이미 가입된 계정이 있는지 체크. 관리자는 1명만 가입할 수 있음.
    $checkQuery = $db->query("SELECT * FROM WEB_USER");
    if($checkQuery->num_rows > 0){
       // for test
       // while($row = $query->fetch_assoc()) {               
       // }
       echo sendResponse('관리자 계정은 1개로 제한하므로 추가로 가입할 수 없습니다.', "", 404);
    }
    else {
        $insertQuery = $db->query("INSERT INTO WEB_USER (WU_Email, WU_Password, WU_PhoneNumber) VALUES('$email', '$password', '$phoneNumber')");
        if($insertQuery === true){
            //$webUserId = $db->insert_id; // for test
            
            echo sendResponse('signup success!', "", 200);
        }
        else {
            echo sendResponse('에러가 발생했습니다.', "", 500);
        }
    }
}
?>