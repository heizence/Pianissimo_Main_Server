<?php 
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {        
    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);   

            if ($tokenData) {
                $studentId = (int)$_POST['studentId'];
                $lessonId = (int)$_POST['lessonId'];
                
                // 레슨 정보 및 원생 정보 조회
                $selectQuery = $db->query("SELECT LESSON_REGISTERED.LR_BlockId, APP_USERS.AU_Name, APP_USERS.AU_LessonsLeft FROM LESSON_REGISTERED, APP_USERS
                WHERE LR_Id = $lessonId AND AU_Id = $studentId;");
                                
                if ($selectQuery->num_rows > 0){       
                    while($row = $selectQuery->fetch_assoc()) {
                        $blockId = $row['LR_BlockId'];
                        $studentName = $row['AU_Name'];
                        $studentLessonsLeft = (int)$row['AU_LessonsLeft'];
                        $studentLessonsLeft -= 1;   // 남은 레슨 횟수 1 차감
                      
                        // 레슨 데이터 업데이트 및 원생 정보에서 남은 레슨 횟수 업데이트 해 주기
                        $db->begin_transaction();

                        $updateLesson = $db->prepare("UPDATE LESSON_REGISTERED SET LR_Status = '예약중', LR_StudentId = ?, LR_StudentName = ? WHERE LR_Id = ?;");
                        $updateAppUser = $db->prepare("UPDATE APP_USERS SET AU_LessonsLeft = ? WHERE AU_Id = ?;");

                        // Bind parameters
                        $updateLesson->bind_param('ssi', $studentId, $studentName, $lessonId);  // ssi means string, string, int
                        $updateAppUser->bind_param('si', $studentLessonsLeft, $studentId);  // si means string, int

                        $updateLesson->execute();
                        $updateAppUser->execute();

                        // Check for errors
                        if ($updateLesson->errno || $updateAppUser->errno) {
                            // Rollback transaction on error
                            $db->rollback();                            
                            echo sendResponse('book lesson failed!', "", 500); 
                            die("Transaction rolled back: " . $mysqli->error);                            
                        } else {
                            // Commit transaction if no errors
                            $db->commit();                            
                            echo sendResponse('book lesson success!', "", 200);
                        }                      
                    }
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