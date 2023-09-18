<?php
include '../modules/Config.php';
include '../modules/JWT.php';

// 라이브 방송 시작 시 방송방 데이터 생성하여 저장
if ($_SERVER['REQUEST_METHOD'] == 'POST') {    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
                
            if ($tokenData) { 
                $roomId = $data['roomId'];
                $senderUserId = (int)$data['senderUserId'];
                $chatMessage = $data['chatMessage'];
                $timeLapsed = (int)$data['timeLapsed'];

                $stmt = $pdo->prepare("INSERT INTO BROADCAST_CHAT_HISTORY
                (BCH_BroadcastId, BCH_SenderId, BCH_ChatMessage, BCH_TimeLapsed)
                VALUES('$roomId', $senderUserId, ?, $timeLapsed)"); // $chatMessage may contain ' or " quote which may cause error when inserting data into SQL DB.

                if ($stmt->execute([$chatMessage])){
                    echo sendResponse('save chat history(web) success!', "", 200);
                }
                else {
                    echo sendResponse('save chat history(web) failed!', "", 500);
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