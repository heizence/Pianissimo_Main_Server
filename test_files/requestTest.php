<?php
include '../../modules/Config.php';
include '../../modules/JWT.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    /* 데이터를 불러올 시작 범위 index(0부터 시작)
    클라이언트(앱, 웹 모두 포함) 측 page 버튼의 index 에 해당함.
    변수명 변경 시 클라이언트(앱, 웹 모두 포함) 쪽 변수명과 반드시 같이 변경할 것.
    */        
    $pageIndex = 1;        
    $numberOfDataPerRequest = 20;   // 요청 한 번당 불러올 데이터의 갯수
    $dataStartIndex = $pageIndex * $numberOfDataPerRequest;  // 데이터를 불러올 시작 범위 index(0부터 시작)

    // 헤더에 포함된 토큰 체크
    if (isset($authorization)) {
        if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            $jwt = new JWT();
            $token = $matches[1];
            $tokenData = $jwt->dehashing($token);
                
            if ($tokenData) { 
            
                $resData;   // 응답으로 보내줄 데이터    
                $broadcasts = array();
                for ($i=0; $i<20; $i++) {
                    $eachData = [];                 
                    $eachData['id'] = $i;
                    $eachData['thumbnailImg'] = "abcd";
                    $eachData['isLive'] = true;
                    $eachData['title'] = "$i 번 방송";
                    $eachData['hostName'] = "$i 번 호스트";
                    $eachData['numberOfViewers'] = 10;
                    $eachData['broadcastEndedAt'] = "2023-03-21 17:35";
                    array_push($broadcasts, $eachData);
                }

                $totalRows = sizeof($broadcasts);   // 전체 데이터 갯수
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
                $resData['broadcasts'] = $broadcasts;
                $resData['isLastPage'] = $isLastPage;
                $resData = json_encode($resData);                    
                echo sendResponse('getBroadcastData success!', $resData, 200);
            
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