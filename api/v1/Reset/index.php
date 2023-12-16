<?php
require '../functions.php';
require "../../libraries/PHPmailer/autoload.php";
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

define('DAILY_RESET_THRESHOLD', 3);

$IP              = GetClientIP();
$browser         = GetBrowser();
$OperatingSystem = GetOS();

$_JSON        = json_decode(file_get_contents('php://input'), true);
$EmailAddress = strip_tags($_JSON['EmailAddress']);

$CountDailyResets = ChainPDO("SELECT COUNT(EmailAddress) FROM PasswordResets WHERE EmailAddress = ? AND DATE(timestamp) = CURDATE()", [$EmailAddress])->fetchColumn();

if ($CountDailyResets <= DAILY_RESET_THRESHOLD) {
    $EmailExists = ChainPDO("SELECT COUNT(EmailAddress) FROM users WHERE EmailAddress = ?", [$EmailAddress])->fetchColumn();
    if ($EmailExists === 0) {
        http_response_code(401);
        echo 'Something went wrong. Kindly try again.';
    } else {
        $passkey  = substr(str_shuffle("23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnopqrstuvwxyz"), 0, 8);
        $password = PasswordHasher($passkey);

        # Update password
        ChainPDO("UPDATE users SET password = ?, FirstTimeLogin = 1 WHERE EmailAddress = ?", [$password, $EmailAddress]);

        # Get details
        $user      = ChainPDO("SELECT FirstName, username FROM users WHERE EmailAddress = ?", [$EmailAddress])->fetch();
        $FirstName = $user['FirstName'];
        $username  = $user['username'];

        # Send mail
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->isHTML(true);
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];
        $mail->Host = "relay.safaricom.co.ke";
        $mail->setFrom('noreply@safaricom.co.ke', "Daraja - Safaricom Developers' Portal");
        $mail->addAddress($EmailAddress);
        $mail->Subject = 'Password reset!';
        ob_start();
        include 'reset.htm';
        $body = ob_get_contents();
        ob_end_clean();
        $mail->Body    = $body;
        $mail->AltBody = "Your password for username $username is $password. You will be required to reset this password once logged in.";

        if (!$mail->Send()) {
            http_response_code(201);
            echo "Your password was reset successfully. However, we faced some challenges sending your email. Kindly reach out on chat if you don't receive an email within 10 minutes";
            LogToDB(json_encode(
                [
                    'logtype'   => 'WARNING',
                    'operation' => 'EMAIL',
                    'service'   => basename(dirname(__FILE__)),
                    'request'   => '{ "Receipient": "' . $EmailAddress . '" }',
                    'response'  => '{ "Mailer Error": "' . $mail->ErrorInfo . '" }',
                ]
            ));
        } else {
            ChainPDO("INSERT INTO PasswordResets VALUES (NULL, ?, ?, now())", [$EmailAddress, $passkey]);
            http_response_code(200);
            echo "Your password was reset successfully. Kindly check your email.";
        }
    }
} else {
    http_response_code(403);
    echo 'You have exceeded the number of password reset quota for today. Kindly try again tomorrow.';
}
