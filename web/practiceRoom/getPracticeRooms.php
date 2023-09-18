<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    /* 데이터를 불러올 시작 범위 index(0부터 시작)
    클라이언트(앱, 웹 모두 포함) 측 page 버튼의 index 에 해당함.
    변수명 변경 시 클라이언트(앱, 웹 모두 포함) 쪽 변수명과 반드시 같이 변경할 것.
    */    
    $pageIndex = $_GET['pageIndex'];  
    $dataStartIndex = $pageIndex * 10;  // 데이터를 불러올 시작 범위 index(0부터 시작)

    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
    
            if ($tokenData) { 
                // 데이터는 PR_Id 값에 따라 내림차순으로 정렬. 최근 날짜순으로 정렬됨.
                $selectQuery = $db->query("SELECT * FROM PRACTICE_ROOMS ORDER BY PR_Id DESC LIMIT $dataStartIndex, 10;"); 

                $resData;   // 응답으로 보내줄 데이터
                if ($selectQuery->num_rows > 0){        
                    $practiceRooms = array();
                    while($row = $selectQuery->fetch_assoc()) {      
                        $eachData = [];                 
                        $eachData['practiceRoomId'] = (int)$row['PR_Id'];    // 고유 아이디
                        $eachData['practiceRoomName'] = $row['PR_Name'];    // 이름
                        $eachData['practiceRoomRegisteredDate'] = $row['PR_RegisteredDate'];    // 등록 날짜
                        array_push($practiceRooms, $eachData);
                    }

                    // 전체 데이터 갯수 구하기
                    $getCountquery = $db->query("SELECT COUNT(PR_Id) as counts FROM PRACTICE_ROOMS");
                    $selRow  = mysqli_fetch_array($getCountquery);
                    $totalRows = $selRow[0];

                    // 응답으로 보내줄 데이터 양식
                    $resData['practiceRooms'] = $practiceRooms;
                    $resData['counts'] = (int)$totalRows;

                    echo sendResponse('get practice rooms success!', $resData, 200);
                }
                else {
                    // 응답으로 보내줄 데이터 양식
                    $resData['practiceRooms'] = [];
                    $resData['counts'] = 0;
                    echo sendResponse('get practice rooms success!!', $resData, 200);                
                }
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