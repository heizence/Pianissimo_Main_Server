<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {    
    $noticeId = $_GET['noticeId'];      
    
    if (isset($authorization)) {        
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
    
            if($tokenData) {
                // 데이터는 noticeId 값에 따라 내림차순으로 정렬. 최근 날짜순으로 정렬됨.
                $selectQuery = $db->query("SELECT * FROM NOTICES WHERE N_Id = $noticeId;"); 
        
                if ($selectQuery->num_rows > 0){        
                    while($row = $selectQuery->fetch_assoc()) {      
                        $data = [];                 
                        $data['noticeId'] = (int)$row['N_Id'];
                        $data['noticeTitle'] = $row['N_Title'];
                        $data['noticeContents'] = $row['N_Contents'];
                        $data['noticeWrittenDate'] = $row['N_WrittenDate'];       
                    }
                    echo sendResponse('getEachNotice success!', $data, 200);
                }
                else {
                    echo sendResponse('getEachNotice failed!', '', 500);                
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