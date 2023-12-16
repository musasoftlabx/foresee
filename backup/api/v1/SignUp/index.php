<?php
require '../functions.php';
require "../../libraries/PHPmailer/autoload.php";
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$_RAW            = file_get_contents('php://input');
$_JSON           = json_decode($_RAW, true);
$OperatingSystem = GetOS();
$browser         = GetBrowser();

$FirstName    = ucwords(strtolower(strip_tags($_JSON['FirstName'])));
$LastName     = ucwords(strtolower(strip_tags($_JSON['LastName'])));
$EmailAddress = strtolower(strip_tags($_JSON['EmailAddress']));
$username     = strtolower(strip_tags($_JSON['username']));

$DoesExist = ChainPDO("SELECT COUNT(*) FROM users WHERE username = ? OR EmailAddress = ?", [$username, $EmailAddress])->fetchColumn();

if ($DoesExist > 0) {
    http_response_code(409);
    echo 'The email address or username you entered already exists. Kindly try another.';
    LogToDB(json_encode(
        [
            'logtype'   => 'WARNING',
            'operation' => 'DATABASE',
            'service'   => basename(dirname(__FILE__)),
            'request'   => null,
            'response'  => '{ "Pre-existing email or username": "' . $EmailAddress . '" }',
        ]
    ));
} else {
    $passkey  = substr(str_shuffle("23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnopqrstuvwxyz"), 0, 8);
    $password = PasswordHasher($passkey);

    # Create developer on apigee

    $request =
        '{
            "attributes": [],
            "email": "' . $EmailAddress . '",
            "firstName": "' . $FirstName . '",
            "lastName": "' . $LastName . '",
            "userName": "' . $username . '"
        }';

    $url = CREATE_DEVELOPER_ON_APIGEE_ENDPOINT;
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode(APIGEE_USERNAME . ':' . APIGEE_PASSWORD),
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);
    $response     = curl_exec($ch);
    $error        = curl_error($ch);
    $ResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    http_response_code($ResponseCode);

    if ($error) {
        echo SERVER_ERROR;
        die();
    } else {
        $result = json_decode($response, true);
        switch ($ResponseCode) {
            case 200:
            case 201:
                $ApigeeDeveloperID = $result['developerId'];
                break;
            case 400:
            case 401:
            case 403:
            case 404:
                echo 'Account not created. ' . $result['message'];
                die();
                break;
            default:
                echo SERVER_ERROR;
                die();
                break;
        }
    }

    # Create account on database

    try
    {
        $DB->beginTransaction();

        ChainPDO("INSERT INTO users VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, 1, now(), now())", [
            $ApigeeDeveloperID,
            $FirstName,
            $LastName,
            $EmailAddress,
            $username,
            strip_tags($_JSON['PhoneNumber']),
            $_JSON['country'],
            $_JSON['AccountType'],
            strip_tags($_JSON['CompanyName']),
            $password,
            $OperatingSystem,
            $browser,
        ]);

        $DB->commit();

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
        $mail->Subject = 'Thank you for signing up!';
        ob_start();
        include 'signup.htm';
        $body = ob_get_contents();
        ob_end_clean();
        $mail->Body    = $body;
        $mail->AltBody = "Thank you for signing up to Daraja! We are very glad to welcome you to our developers' platform. Your password for $username is $password. You will be required to reset your password once logged in.";

        if (!$mail->Send()) {
            http_response_code(201);
            echo "Sign up was successful. However, we faced some challenges sending your email. Kindly reach out on chat if you don't receive an email within 10 minutes";
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
            http_response_code(200);
            echo "Sign up was successful. We sent you an email with your login credentials.";
        }
    } catch (PDOException $e) {
        $DB->rollBack();
        http_response_code(500);
        LogToDB(json_encode(
            [
                'logtype'   => 'ERROR',
                'operation' => 'DATABASE',
                'service'   => basename(dirname(__FILE__)),
                'request'   => $_RAW,
                'response'  => '{ "PDO Error": "' . $e . '" }',
            ]
        ));
        echo SERVER_ERROR;
    }
}
