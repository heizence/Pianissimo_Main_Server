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
                // 데이터는 studentId 값에 따라 내림차순으로 정렬. 최근 날짜순으로 정렬됨.
                $selectQuery = $db->query("SELECT * FROM APP_USERS ORDER BY AU_Id DESC LIMIT $dataStartIndex, 10;"); 

                $resData;   // 응답으로 보내줄 데이터
                if ($selectQuery->num_rows > 0){        
                    $students = array();
                    while($row = $selectQuery->fetch_assoc()) {      
                        $eachData = [];                 
                        $eachData['studentId'] = (int)$row['AU_Id'];    // 고유 아이디
                        $eachData['studentEmail'] = $row['AU_Email'];  // 이메일
                        $eachData['studentName'] = $row['AU_Name'];    // 이름                        

                        $studentPhoneNumber = $row['AU_PhoneNumber'];
                        $eachData['studentPhoneNumber'] = substr($studentPhoneNumber,0,3)."-".substr($studentPhoneNumber, 3,4)."-".substr($studentPhoneNumber, 6, 4);   // 전화번호
                        
                        $eachData['studentStatus'] = $row['AU_Status'];    // 학원 이용여부                        

                        $eachData['studentLessonsLeft'] = (int)$row['AU_LessonsLeft'];  // 남은 레슨 횟수
                        $eachData['studentRegisteredDate'] = $row['AU_RegisteredDate'];    // 등록 날짜
                        array_push($students, $eachData);
                    }

                    // 전체 데이터 갯수 구하기
                    $getCountquery = $db->query("SELECT COUNT(AU_Id) as counts FROM APP_USERS");
                    $selRow  = mysqli_fetch_array($getCountquery);
                    $totalRows = $selRow[0];

                    // 응답으로 보내줄 데이터
                    $resData['students'] = $students;
                    $resData['counts'] = (int)$totalRows;

                    echo sendResponse('getStudents success!', $resData, 200);
                }
                else {
                    // 응답으로 보내줄 데이터
                    $resData['students'] = [];
                    $resData['counts'] = 0;
                    echo sendResponse('getStudents success!', $resData, 200);
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