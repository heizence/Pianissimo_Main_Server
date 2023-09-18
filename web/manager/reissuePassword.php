
<?php 
include '../../modules/Config.php';
require '../../modules/secretKeys.php';
require '../../vendor/autoload.php';

use Mailgun\Mailgun;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {        
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $email = $data['email'];

    $checkQuery = $db->query("SELECT * FROM WEB_USER WHERE WU_Email = '$email'");
    if ($checkQuery->num_rows > 0){
        $webUserId;
        while($row = $checkQuery->fetch_assoc()) {                           
            $webUserId = $row['WU_Id'];
        }

        // 임시 비밀번호 생성
        $alphabet = "abcdefghijklmnopqrstuvwxyz0123456789";
        $alphabet_length = strlen($alphabet);

        $randomPassword = "";
        for ($i = 0; $i < 6; $i++) {
            $random_char = $alphabet[rand(0, $alphabet_length - 1)];
            $randomPassword .= $random_char;
        }       
        error_log("randomPassword : $randomPassword");

        $hashedPassword = hash("sha256", $randomPassword);
        error_log("hashedPassword : $hashedPassword");

        // 기존 비밀번호 임시 비밀번호로 업데이트
        $updateQuery = $db->query("UPDATE WEB_USER SET WU_Password = '$hashedPassword' WHERE WU_Id = $webUserId");        

        if ($updateQuery) {                
            // 업데이트 성공 시 이메일로 임시 비밀번호 전송
            try {
                $mgClient = Mailgun::create($MAILGUN_API_KEY);
                $domain = $MAILGUN_DOMAIN;
                $params = array(
                'from'    => "Pianissimo <Administrator@pianissimo.com>",                
                'to'      => $email,
                //'to'      => 'heizence6626@gmail.com',    // for test
                'subject' => '비밀번호 재발급',
                'text'    => "임시 비밀번호가 발급되었습니다.\n $randomPassword"
                );

                # Make the call to the client.
                $mgClient->messages()->send($MAILGUN_DOMAIN, $params);

                echo sendResponse('reissuePassword success!', '', 200);
            }
            catch (Exception $e){
                error_log("error! : $e");
                echo sendResponse('reissuePassword send email failed!', $e->getMessage(), 500);
            }
        }
        else {
            echo sendResponse('reissuePassword update password failed!', '', 500);            
        }            
    }
    else {
        echo sendResponse('no matching account!', '', 404);            
    }

}
?>