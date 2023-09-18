<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

// 라이브 방송 실시간 시청자 수 업데이트
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);

            if ($tokenData) { 
                $roomId = $_POST['roomId'];
                $updatedNumberOfWatchers = (int)$_POST['updatedNumberOfWatchers']; // 업데이트 된 시청자 수. client 또는 signaling 서버 쪽에서 보내줌.
                
                error_log("check roomId : $roomId");
                error_log("check updatedNumberOfViewers : $updatedNumberOfWatchers");

                $updateQuery = $db->query("UPDATE BROADCASTS SET B_NumberOfWatchers = $updatedNumberOfWatchers WHERE B_Id = '$roomId';");

                if ($updateQuery) {
                    echo sendResponse('update number of watchers success!', "", 200);
                }
                else {
                    echo sendResponse('update number of watchers failed!', "", 500);
                }  
            }
            else {
                error_log('Invalid Token');
                echo sendResponse('Invalid Token!', '', 401);
            }
          
        } else {
            error_log('No token in header!');
            echo sendResponse("No token in header!", "", 401);
        }
    }
    else {        
        error_log('No Authorization in header!');
        echo sendResponse("No Authorization in header!", "", 401);
    }
}
?>