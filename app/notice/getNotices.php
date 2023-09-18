<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    /* 데이터를 불러올 시작 범위 index(0부터 시작)
    클라이언트(앱, 웹 모두 포함) 측 page 버튼의 index 에 해당함.
    변수명 변경 시 클라이언트(앱, 웹 모두 포함) 쪽 변수명과 반드시 같이 변경할 것.
    */        
    $pageIndex = $_GET['pageIndex'];          
    $numberOfDataPerRequest = 20;   // 요청 한 번당 불러올 데이터의 갯수
    $dataStartIndex = $pageIndex * $numberOfDataPerRequest;  // 데이터를 불러올 시작 범위 index(0부터 시작)

    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
                
            if ($tokenData) { 
                // 데이터는 noticeId 값에 따라 내림차순으로 정렬. 최근 날짜순으로 정렬됨.
                $selectQuery = $db->query("SELECT * FROM NOTICES ORDER BY N_Id DESC LIMIT $dataStartIndex, $numberOfDataPerRequest;"); 

                $resData;   // 응답으로 보내줄 데이터
                if ($selectQuery->num_rows > 0){        
                    $notices = array();
                    while($row = $selectQuery->fetch_assoc()) {      
                        // 변수명은 클라이언트 recyclerView 에서 사용하는 각 notice class 변수명과 통일하야 함. 변경 시 클라이언트 쪽과 반드시 같이 변경하기
                        $eachData = [];                 
                        $eachData['id'] = (int)$row['N_Id'];
                        $eachData['writtenDate'] = $row['N_WrittenDate'];
                        $eachData['title'] = $row['N_Title'];
                        $eachData['contents'] = $row['N_Contents'];
                        array_push($notices, $eachData);
                    }

                    // 전체 데이터 갯수 구하기
                    $getCountquery = $db->query("SELECT COUNT(N_Id) as counts FROM NOTICES");
                    $selRow  = mysqli_fetch_array($getCountquery);
                    $totalRows = (int)$selRow[0];
                    $isLastPage;    // 현재 페이지가 마지막 페이지인지 아닌지 식별

                    /*
                    총 데이터 갯수와 현재 페이지까지 누적된 데이터 갯수를 비교하여 마지막 페이지인지 아닌지 식별.
                    예) 총 데이터가 30개, 페이지가 0,1 총 2개, 한번에 불러오는 데이터가 20개 일 때
                    현재 페이지가 0일 때 -> 30 - 20 * (0+1) = 10 > 0 -> 마지막 페이지 아님.
                    현재 페이지가 1일 때 -> 30 - 20 * (1+1) = -10 < 0 -> 마지막 페이지.
                    */
                    if ($totalRows - $numberOfDataPerRequest * ($pageIndex + 1) > 0) $isLastPage = false;
                    else $isLastPage = true;

                    /* 
                    응답으로 보내줄 데이터 양식                
                    클라이언트에서 isLastPage 가 true 라고 식별하면 더 이상 데이터 로드 요청을 보내지 않음.
                    */
                    $resData['notices'] = $notices;
                    $resData['isLastPage'] = $isLastPage;
                    $resData = json_encode($resData);                    
                    echo sendResponse('getNotices success!', $resData, 200);
                }
                else {
                    // 처음부터 데이터가 없었던 경우에 해당.                                       
                    $resData['notices'] = [];
                    $resData['isLastPage'] = true;
                    $resData = json_encode($resData);
                    echo sendResponse('getNotices success!', $resData, 200);                
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