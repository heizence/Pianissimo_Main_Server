<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

// 라이브 방송 시작 시 방송방 데이터 생성하여 저장
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
                
            if ($tokenData) { 
                $roomId = $_POST['roomId'];
                $hostId = (int)$_POST['hostId'];
                $roomName = $_POST['roomName'];
                $isLive = true; // 1 if true, 0 is false
                $liveStartedAt = $_POST['liveStartedAt'];
                $thumbnailImage = $_POST['thumbnailImage'];
                $numberOfWatchers = (int)$_POST['numberOfWatchers'];
                
                $insertQuery = $db->query("INSERT INTO BROADCASTS
                (B_Id, B_HostId, B_RoomName, B_IsLive, B_LiveStartedAt, B_Thumbnail, B_NumberOfWatchers)
                VALUES('$roomId', $hostId, '$roomName', $isLive, '$liveStartedAt', '$thumbnailImage', $numberOfWatchers);
                "); 

                if ($insertQuery){
                    echo sendResponse('create live room success!', "", 200);
                }
                else {
                    echo sendResponse('create live room failed!', "", 500);
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