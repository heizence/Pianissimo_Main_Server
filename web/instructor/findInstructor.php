<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $instructorName = $_GET['instructorName'];  
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
                SELECT * FROM INSTRUCTORS WHERE I_Name LIKE '%$instructorName%' 
                ORDER BY I_Id DESC LIMIT $dataStartIndex, 10;
                "); 
                
                $resData = [];  // 응답으로 보내줄 데이터 양식
                $instructors = array();    // 원생 데이터 배열

                if ($selectQuery->num_rows > 0){                    
                    while($row = $selectQuery->fetch_assoc()) {      
                        $eachData = [];                 
                        $eachData['instructorId'] = (int)$row['I_Id'];    // 고유 아이디                        
                        $eachData['instructorName'] = $row['I_Name'];    // 이름

                        $instructorPhoneNumber = $row['I_PhoneNumber'];
                        $eachData['instructorPhoneNumber'] = substr($instructorPhoneNumber,0,3)."-".substr($instructorPhoneNumber, 3,4)."-".substr($instructorPhoneNumber, 6, 4);   // 전화번호

                        $eachData['instructorGenre'] = $row['I_Genre'];    // 장르

                        // 평균 평점 계산
                        $rateStr = $row['I_Rate'];
                        $totalRate = (int)explode('&', $rateStr)[0];
                        $numberOfPeople = (int)explode('&', $rateStr)[1];

                        ($totalRate === 0) ? $averageRate = 0 : $averageRate = round(($totalRate / $numberOfPeople),1);
                        $eachData['instructorRate'] = $averageRate;
                        $eachData['instructorRegisteredDate'] = $row['I_RegisteredDate'];    // 등록 날짜
                        array_push($instructors, $eachData);
                    }
                }

                // 전체 데이터 갯수 구하기
                $getCountquery = $db->query("SELECT COUNT(I_Id) as counts FROM INSTRUCTORS WHERE I_Name LIKE '%$instructorName%';");
                $selRow  = mysqli_fetch_array($getCountquery);
                $totalRows = $selRow[0];

                // 응답으로 보내줄 데이터 양식
                $resData['instructors'] = $instructors;
                $resData['counts'] = (int)$totalRows;

                // 이름에 해당하는 원생이 존재하면 원생 데이터 보내주기(동명이인 포함), 없으면 빈 배열 보내주기
                echo sendResponse('findInstructor success!', $resData, 200);
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