<?php
include '../modules/Config.php';
include '../modules/JWT.php';

// 현재 진행중인 라이브 방송 데이터, 다시보기 영상 데이터 불러오기
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
        // 데이터는 방송 날짜 및 시간 값에 따라 내림차순으로 정렬. 방송을 시작한 날짜 및 시간순으로 정렬됨.
        $selectQuery = "SELECT BROADCASTS.B_Id, BROADCASTS.B_HostId, BROADCASTS.B_RoomName, BROADCASTS.B_IsLive, BROADCASTS.B_LiveStartedAt, 
        BROADCASTS.B_Thumbnail, BROADCASTS.B_NumberOfWatchers, BROADCASTS.B_RecordedMedia,
        APP_USERS.AU_Name, APP_USERS.AU_ProfileImg
        FROM BROADCASTS
        INNER JOIN APP_USERS ON BROADCASTS.B_HostId = APP_USERS.AU_Id
        ORDER BY B_LiveStartedAt DESC LIMIT ?, ?";

        $stmt = $pdo->prepare($selectQuery);
        $stmt->bindParam(1, $dataStartIndex, PDO::PARAM_INT);
        $stmt->bindParam(2, $numberOfDataPerRequest, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $resData = [];   // 응답으로 보내줄 데이터
            $broadcasts = array();

            if (sizeof($rows) > 0) {
                foreach ($rows as $row) {
                    $base64MediaData = base64_encode($row['B_RecordedMedia']);
                    
                    $data = array(
                        'id' => $row['B_Id'],
                        'hostId' => (int)$row['B_HostId'],
                        'hostName' => $row['AU_Name'],
                        'isLive' => (int)$row['B_IsLive'] == 1 ? true : false,
                        'hostProfileImage' => $row['AU_ProfileImg'],
                        'roomName' => $row['B_RoomName'],
                        'liveStartedAt' => $row['B_LiveStartedAt'],
                        'thumbnailImage' => $row['B_Thumbnail'],
                        'numberOfWatchers' => (int)$row['B_NumberOfWatchers'],
                        'recordedMedia' => $base64MediaData
                    );
                    $check = json_encode($base64MediaData);
                    error_log("check base64 data : $check");
                    $broadcasts[] = $data;

                    // $eachData = [];
                    // $eachData['id'] = $row['B_Id'];
                    // $eachData['hostId'] = (int)$row['B_HostId'];
                    // $eachData['hostName'] = $row['AU_Name'];
                    // $eachData['isLive'] = (int)$row['B_IsLive'] == 1 ? true : false;
                    // $eachData['hostProfileImage'] = $row['AU_ProfileImg'];
                    // $eachData['roomName'] = $row['B_RoomName'];
                    // $eachData['liveStartedAt'] = $row['B_LiveStartedAt'];
                    // $eachData['thumbnailImage'] = $row['B_Thumbnail'];
                    // $eachData['numberOfWatchers'] = (int)$row['B_NumberOfWatchers'];
                    // $eachData['recordedMedia'] = $row['B_RecordedMedia'];
                    
                    // $check = json_encode($eachData);
                    // error_log("check each row : $check");
                    // array_push($broadcasts, $eachData);
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
                echo sendResponse('getBroadcastData success!', $resData, 200);
            }
            else {
                // 처음부터 데이터가 없었던 경우에 해당.                                       
                $resData['broadcasts'] = [];
                $resData['isLastPage'] = true;
                echo sendResponse('getBroadcastData success!', $resData, 200);                
            }
        }
    }
    else {        
        error_log('No Authorization in header!');
        echo sendResponse("No Authorization in header!", "", 401);
    }
}
?>