<?php 
include '../../modules/Config.php';
require '../../modules/secretKeys.php';
require '../../vendor/autoload.php';

use Mailgun\Mailgun;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {        
    $email = $_POST['email'];

    $checkQuery = $db->query("SELECT * FROM APP_USERS WHERE AU_Email = '$email'");
    if ($checkQuery->num_rows > 0){
        $appUserId;
        while($row = $checkQuery->fetch_assoc()) {                           
            $appUserId = $row['AU_Id'];            
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
        $updateQuery = $db->query("UPDATE APP_USERS SET AU_Password = '$hashedPassword' WHERE AU_Id = $appUserId");

        if ($updateQuery) {                
            // 업데이트 성공 시 이메일로 임시 비밀번호 전송
            try {
                $mgClient = Mailgun::create($MAILGUN_API_KEY);
                $domain = $MAILGUN_DOMAIN;
                $params = array(
                //'from'    => "Pianissimo <me@samples.mailgun.org>",
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