<?php
include '../modules/Config.php';
include '../modules/JWT.php';

// 방송 끝

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
                
            if ($tokenData) { 
                $roomId = $_POST['roomId'];
                $deleteQuery = $db->query("DELETE FROM BROADCASTS WHERE B_Id = '$roomId';");

                if ($deleteQuery){
                    echo sendResponse('delete live room success!', "", 200);
                }
                else {
                    echo sendResponse('delete live room failed!', "", 500);
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

// 추후 방송 다시보기 기능 개발 시 사용하기
/*
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roomId = $_POST['roomId']; // Retrieve the roomId value
    $uploadedFile = $_FILES['videoFile']; // Retrieve the uploaded file details

    // for check
    $checkFile = json_encode($uploadedFile);    
    error_log("check roomId : $roomId");
    error_log("check uploadedFile : $checkFile");

    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
                
            if ($tokenData) { 
                // Extract file information
                $fileName = $uploadedFile['name'];
                $tmpFilePath = $uploadedFile['tmp_name'];
                
                error_log("check fileName : $fileName");
                error_log("check tmpFilePath : $tmpFilePath");
                               
                // Declare update query
                $updateQuery = "UPDATE Pianissimo.BROADCASTS SET B_IsLive = ?, B_NumberOfWatchers = ?, B_RecordedMedia = ? WHERE B_Id = ?";
                
                // Bind params
                $isLive = 0;
                $numberOfWatchers = 0;
                $fileContents = file_get_contents($uploadedFile['tmp_name']);    // blob file           
                error_log("check fileContents : $fileContents");

                //$stmt->bind_param("iibs", $isLive, $numberOfWatchers, $fileContents, $roomId);    // didn't work. why?

                $stmt = $pdo->prepare($updateQuery);

                // Bind the parameters                
                $stmt->bindParam(1, $isLive, PDO::PARAM_INT);
                $stmt->bindParam(2, $numberOfWatchers, PDO::PARAM_INT);
                $stmt->bindParam(3, $fileContents, PDO::PARAM_LOB);
                $stmt->bindParam(4, $roomId, PDO::PARAM_STR);
                
                if ($stmt->execute()) {
                    echo sendResponse('finish live and save recorded file success!',"", 200);
                } else {
                    echo sendResponse('finish live and save recorded file failed!', "", 404);
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
*/
?>