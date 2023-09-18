<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $practiceRoomName = $_GET['practiceRoomName'];  
    $pageIndex = $_GET['pageIndex'];
    $dataStartIndex = $pageIndex * 10;  // 데이터를 불러올 시작 범위 index(0부터 시작)

    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
    
            if ($tokenData) {                 
                $selectQuery = $db->query("
                SELECT * FROM PRACTICE_ROOMS WHERE PR_Name LIKE '%$practiceRoomName%' 
                ORDER BY PR_Id DESC LIMIT $dataStartIndex, 10;
                "); 
                
                $resData = [];  // 응답으로 보내줄 데이터 양식
                $practiceRooms = array();    // 연습실 데이터 배열

                if ($selectQuery->num_rows > 0){                    
                    while($row = $selectQuery->fetch_assoc()) {      
                        $eachData = [];                 
                        $eachData['practiceRoomId'] = (int)$row['PR_Id'];    // 고유 아이디                        
                        $eachData['practiceRoomName'] = $row['PR_Name'];    // 이름
                        $eachData['practiceRoomRegisteredDate'] = $row['PR_RegisteredDate'];    // 추가된 날짜
                        array_push($practiceRooms, $eachData);
                    }
                }

                // 전체 데이터 갯수 구하기
                $getCountquery = $db->query("SELECT COUNT(PR_Id) as counts FROM PRACTICE_ROOMS WHERE PR_Name LIKE '%$practiceRoomName%';");
                $selRow  = mysqli_fetch_array($getCountquery);
                $totalRows = $selRow[0];

                // 응답으로 보내줄 데이터 양식
                $resData['practiceRooms'] = $practiceRooms;
                $resData['counts'] = (int)$totalRows;

                // 이름에 해당하는 연습실이 존재하면 연습실 데이터 보내주기(같은 이름 연습실 포함), 없으면 빈 배열 보내주기
                echo sendResponse('find practice room success!', $resData, 200);
            }
            else {
                echo sendResponse('Invalid Token!', '', 401);
            }
          
        } else {
            echo sendResponse("No token in header!", "", 401);
        }
    }
    else {        
        echo sendResponse("No Authorization in header!", "", 401);
    }
}
?>