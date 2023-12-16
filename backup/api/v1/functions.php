<?php
require 'DB.php';
$APPROOT   = str_replace('v1', '', APPROOT);
$UserAgent = $_SERVER['HTTP_USER_AGENT'];

require $APPROOT . '/libraries/phpJWT/autoload.php';

use \Firebase\JWT\JWT;

# Prevent unauthorized access via CSRF
function browserify($UserAgent)
{
    if (strpos($UserAgent, 'Opera') || strpos($UserAgent, 'OPR/')) {
        return 'Opera';
    } elseif (strpos($UserAgent, 'Edge')) {
        return 'Edge';
    } elseif (strpos($UserAgent, 'Chrome')) {
        return 'Chrome';
    } elseif (strpos($UserAgent, 'Safari')) {
        return 'Safari';
    } elseif (strpos($UserAgent, 'Firefox')) {
        return 'Firefox';
    } elseif (strpos($UserAgent, 'MSIE') || strpos($UserAgent, 'Trident/7')) {
        return 'Internet Explorer';
    }

    return 'Other';
}

function GetClientIP()
{
    $keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k]) && filter_var($_SERVER[$k], FILTER_VALIDATE_IP)) {
            return $_SERVER[$k];
        }
    }
    return "UNKNOWN";
}

function GetTokenFromHeaders()
{
    $headers = getallheaders();
    $token   = '';

    foreach ($headers as $key => $value) {
        $key === 'token' ? $token = $value : null;
    }

    return $token;
}

function ChainPDO($sql, $binds = null)
{
    global $DB;

    if (!$binds) {
        return $DB->query($sql);
    }
    $stmt = $DB->prepare($sql);
    $stmt->execute($binds);
    return $stmt;
}

/*if (browserify($_SERVER['HTTP_USER_AGENT']) == 'Other') {
die();
}*/
function LogToDB($params)
{
    global $LOG;

    $params = json_decode($params, true);

    $ResponseCode = empty($params['ResponseCode']) ? null : $params['ResponseCode'];
    $ResponseTime = empty($params['ResponseTime']) ? null : $params['ResponseTime'];
    $logtype      = $params['logtype'];
    $operation    = $params['operation'];
    $service      = $params['service'];
    $endpoint     = empty($params['endpoint']) ? null : $params['endpoint'];
    $request      = $params['request'];
    $response     = $params['response'];

    $LOGGER = $LOG->prepare("INSERT INTO logs VALUES (NULL,?,?,?,?,?,?,?,?,now())");
    $LOGGER->execute([$logtype, $operation, $ResponseCode, $ResponseTime, $service, $endpoint, $request, $response]);
}

function PasswordHasher($password)
{
    $hmac            = hash_hmac('sha512', $password, JWT_KEY);
    $base64          = strtr(base64_encode(0), '+', '.');
    $salt            = substr($base64, 0, 22);
    $bcrypt          = crypt($hmac, 'daraja' . $salt);
    return $password = md5($bcrypt);
}

function JWT($params)
{
    return JWT::encode([
        "iss"  => "https://4ctech.co.ke",
        "aud"  => "https://4ctech.co.ke",
        "iat"  => time(), // issued at
        "nbf"  => time(), // not before in seconds
        "exp"  => time() + (60 * 60 * 24), // expire time in seconds
        "data" => json_decode($params, true),
    ], JWT_KEY);
}

function RetrieveAndValidateTokenFromRequest()
{
    $token = getallheaders();
    foreach ($token as $key => $value) {
        $key === 'Authorization' || $key === 'authorization' ? $token = $value : null;
    };
    $token = explode(' ', $token)[1];
    Tokenify($token, false);

    return $token;
}

function Tokenify($token, $reissue)
{
    if ($token) {
        try {
            $decoded = JWT::decode($token, JWT_KEY, ['HS256']);
            if ($reissue) {
                $payload = explode('.', $token)[1];
                $decoded = json_decode(base64_decode($payload), true);
                echo JWT(json_encode($decoded['data']));
            }
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                "message" => "Access denied",
                "error"   => $e->getMessage(),
            ]);
            die();
        }
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Access denied."]);
        die();
    }
}

function TokenDeconstruct($token, $item)
{
    $payload = explode('.', $token)[1];
    $decoded = json_decode(base64_decode($payload), true);
    return $decoded['data'][$item];
}

function TimeElapsed($datetime, $full = false)
{
    $now  = new DateTime;
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) {
        $string = array_slice($string, 0, 1);
    }

    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function HSV_TO_RGB($H, $S, $V) // HSV Values:Number 0-1

{
    // RGB Results:Number 0-255
    $RGB = array();

    if ($S == 0) {
        $R = $G = $B = $V * 255;
    } else {
        $var_H = $H * 6;
        $var_i = floor($var_H);
        $var_1 = $V * (1 - $S);
        $var_2 = $V * (1 - $S * ($var_H - $var_i));
        $var_3 = $V * (1 - $S * (1 - ($var_H - $var_i)));

        if ($var_i == 0) {
            $var_R = $V;
            $var_G = $var_3;
            $var_B = $var_1;
        } else if ($var_i == 1) {
            $var_R = $var_2;
            $var_G = $V;
            $var_B = $var_1;
        } else if ($var_i == 2) {
            $var_R = $var_1;
            $var_G = $V;
            $var_B = $var_3;
        } else if ($var_i == 3) {
            $var_R = $var_1;
            $var_G = $var_2;
            $var_B = $V;
        } else if ($var_i == 4) {
            $var_R = $var_3;
            $var_G = $var_1;
            $var_B = $V;
        } else {
            $var_R = $V;
            $var_G = $var_1;
            $var_B = $var_2;
        }

        $R = $var_R * 255;
        $G = $var_G * 255;
        $B = $var_B * 255;
    }

    $RGB['R'] = $R;
    $RGB['G'] = $G;
    $RGB['B'] = $B;

    return $RGB;
}

function GetColorFromLetter($word)
{
    // get the percent of the first letter ranging from 0-1
    $first_letter_code = (ord(strtolower($word[0])) - 97) / 25.0;

    // add a phase depending on where you want to start on the color spectrum
    // red is 0, green is 0.25, cyan is 0.5, blue is ~0.75, and 1 is back to red
    $hue = $first_letter_code + 0.25;

    // you may also want to divide by how much of the spectrum you want to cover
    // (making the colors range only from green to blue, for instance)
    // but i'll leave that as an exercise

    // constrain it to 0-1
    if ($hue > 1.0) {
        $hue -= 1.0;
    }

    // the second value is the saturation ("colorfulness", ranging from gray to fully-colored)
    // the third is the value (brightness)
    $rgb = HSV_TO_RGB($hue, 1, 0.75);

    $hexstring = "#";

    foreach ($rgb as $c) {
        $hexstring .= str_pad(dechex($c), 2, "0", STR_PAD_LEFT);
    }

    return $hexstring;
}

function StripUnwantedTagsAndAttrs($html)
{
    $xml = new DOMDocument();
    //Suppress warnings: proper error handling is beyond scope of example
    libxml_use_internal_errors(true);
    //List the tags you want to allow here, NOTE you MUST allow html and body otherwise entire string will be cleared
    $allowed_tags = ["html", "body", "b", "br", "em", "hr", "i", "li", "ol", "p", "s", "span", "table", "tr", "td", "u", "ul", "img"];
    //List the attributes you want to allow here
    $allowed_attrs = ["class", "id", "style", "src"];
    if (!strlen($html)) {
        return false;
    }
    if ($xml->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
        foreach ($xml->getElementsByTagName("*") as $tag) {
            if (!in_array($tag->tagName, $allowed_tags)) {
                $tag->parentNode->removeChild($tag);
            } else {
                foreach ($tag->attributes as $attr) {
                    if (!in_array($attr->nodeName, $allowed_attrs)) {
                        $tag->removeAttribute($attr->nodeName);
                    }
                }
            }
        }
    }
    return $xml->saveHTML();
}

set_error_handler(
    function ($ErrorSeverity, $ErrorMessage, $ErrorFile, $ErrorLine) {
        $logtype = 'ERROR';
        switch ($ErrorSeverity) {
            case E_ERROR:
            case E_WARNING:
                $logtype = 'WARNING';
            case E_PARSE:
                $logtype = 'CRITICAL';
            case E_NOTICE:
                $logtype = 'NOTICE';
            case E_CORE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_ERROR:
            case E_COMPILE_WARNING:
            case E_USER_ERROR:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_RECOVERABLE_ERROR:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
        }
        http_response_code(500);
        LogToDB(json_encode(
            [
                'logtype'   => $logtype,
                'operation' => 'SYSTEM ERROR',
                'service'   => 'Caught Exceptions',
                'endpoint'  => $ErrorFile,
                'request'   => null,
                'response'  => '{ "Line": "' . $ErrorLine . '", "Message": "' . $ErrorMessage . '" }',
            ]
        ));
        echo $ErrorSeverity . ' : ' . $ErrorMessage . ' : ' . $ErrorFile . ' : ' . $ErrorLine;
        //echo "Whoops. Something went wrong. Please try again. If this persists, don't hesitate to reach us.";
        die();
    }
);
