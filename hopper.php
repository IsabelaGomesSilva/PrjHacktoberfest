<?php

require __DIR__ . '/vendor/autoload.php';

use Curl\Curl;
use Ramsey\Uuid\Uuid;

class HopperDeals
{
    function __construct()
    {
        $this->curl = new Curl();
        $this->uuid = Uuid::uuid4();
        $this->fakerPhoneNumber = Faker\Factory::create('Faker\Provider\en_US\PhoneNumber');
        $this->hRequest = self::jwtGenerator();
    }

    public function proxyChecker($socks5List)
    {
        $file_socks5 = file_get_contents($socks5List);
        $socks5s = explode("\r\n", $file_socks5);
        foreach ($socks5s as $socks5) {
            echo "[+] Connecting to proxy " . $socks5 . "\n";

            $this->curl->setProxy($socks5);
            $this->curl->setProxyType(CURLPROXY_SOCKS5);
            $this->curl->get('https://httpbin.org/ip');

            if (isset($this->curl->response->origin)) {
                $responseData = [
                    'connected' => true,
                    'proxy' => $socks5,
                    'ip' => $this->curl->response->origin,
                ];
                return $responseData;
            } else {
                writeLog('log/proxy_death.json', "\n" . $socks5, 'a+');
                deleteString($socks5, $socks5List);
            }
        }

        echo "[-] No Proxy Connected\n\n";
        exit;
    }

    public function jwtGenerator()
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(["identity" => ["userId" => $this->uuid, "deviceId" => "web-" . $this->uuid, "version" => ["value" => "BUILD", "buildNumber" => 0], "appId" => "com.hopper.champaign.production"], "preferences" => ["locale" => "en_US", "currency" => "USD", "timeZone" => "America/Toronto", "countryCode" => "ID"], "requestId" => $this->uuid, "trackingContextId" => $this->uuid, "experiments" => "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"]);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'SPPOJ92HZ92VAN9E5WF1811M3B5DHWTW0EKGC4LE', true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        return $jwt;
    }

    public function register($email, $socks5)
    {
        $this->curl->setProxy($socks5);
        $this->curl->setProxyType(CURLPROXY_SOCKS5);
        $this->curl->setHeader('Host', 'deals.hopper.com');
        $this->curl->setTimeout(50);
        $this->curl->setConnectTimeout(50);
        $this->curl->setHeader('Connection', 'keep-alive');
        $this->curl->setHeader('sec-ch-ua', '"Chromium";v="104", " Not A;Brand";v="99", "Google Chrome";v="104"');
        $this->curl->setHeader('H-Request', $this->hRequest);
        $this->curl->setHeader('sec-ch-ua-mobile', '?0');
        $this->curl->setHeader('Content-Type', 'application/json');
        $this->curl->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36');
        $this->curl->setHeader('sec-ch-ua-platform', '"Windows"');
        $this->curl->setHeader('Accept', '*/*');
        $this->curl->setHeader('Origin', 'https://deals.hopper.com');
        $this->curl->setHeader('Sec-Fetch-Site', 'same-origin');
        $this->curl->setHeader('Sec-Fetch-Mode', 'cors');
        $this->curl->setHeader('Sec-Fetch-Dest', 'empty');
        $this->curl->setHeader('Referer', 'https://deals.hopper.com/');
        $this->curl->setHeader('Accept-Encoding', 'gzip, deflate, br');
        $this->curl->setHeader('Accept-Language', 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7');
        $this->curl->setOpt(CURLOPT_ENCODING, "");
        $this->curl->put('https://deals.hopper.com/api/v2/auth/sign_in', '{"SignInRequest":"EmailBased","email":"' . $email . '"}');

        if ($this->curl->error) {
            echo '[-] Error: Register - ' . $this->curl->errorCode . ': ' . $this->curl->errorMessage . "\n\n";

            var_dump($this->curl->response);
        } else {
            $responseData = $this->curl->response;
            return $responseData;
        }
    }

    public function verificationOTP($otpCode, $email, $token, $socks5)
    {
        $this->curl->setProxy($socks5);
        $this->curl->setProxyType(CURLPROXY_SOCKS5);
        $this->curl->setHeader('Host', 'deals.hopper.com');
        $this->curl->setTimeout(50);
        $this->curl->setConnectTimeout(50);
        $this->curl->setHeader('Connection', 'keep-alive');
        $this->curl->setHeader('sec-ch-ua', '"Chromium";v="104", " Not A;Brand";v="99", "Google Chrome";v="104"');
        $this->curl->setHeader('H-Request', $this->hRequest);
        $this->curl->setHeader('sec-ch-ua-mobile', '?0');
        $this->curl->setHeader('Content-Type', 'application/json');
        $this->curl->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36');
        $this->curl->setHeader('sec-ch-ua-platform', '"Windows"');
        $this->curl->setHeader('Accept', '*/*');
        $this->curl->setHeader('Origin', 'https://deals.hopper.com');
        $this->curl->setHeader('Sec-Fetch-Site', 'same-origin');
        $this->curl->setHeader('Sec-Fetch-Mode', 'cors');
        $this->curl->setHeader('Sec-Fetch-Dest', 'empty');
        $this->curl->setHeader('Referer', 'https://deals.hopper.com/');
        $this->curl->setHeader('Accept-Encoding', 'gzip, deflate, br');
        $this->curl->setHeader('Accept-Language', 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7');
        $this->curl->setOpt(CURLOPT_ENCODING, "");
        $this->curl->put('https://deals.hopper.com/api/v2/auth/check_verification_code', '{"code":"' . $otpCode . '","VerifyCodeRequest":"EmailBased","email":"' . $email . '","token":"' . $token . '"}');

        if ($this->curl->error) {
            echo '[-] Error: Verification OTP - ' . $this->curl->errorCode . ': ' . $this->curl->errorMessage . "\n\n";
        } else {
            $responseData = $this->curl->response;
            return $responseData;
        }
    }

    public function inputData($firstName, $lastName, $token, $phoneNumber, $socks5)
    {
        $this->curl->setProxy($socks5);
        $this->curl->setProxyType(CURLPROXY_SOCKS5);
        $this->curl->setHeader('Host', 'deals.hopper.com');
        $this->curl->setTimeout(50);
        $this->curl->setConnectTimeout(50);
        $this->curl->setHeader('Connection', 'keep-alive');
        $this->curl->setHeader('sec-ch-ua', '"Chromium";v="104", " Not A;Brand";v="99", "Google Chrome";v="104"');
        $this->curl->setHeader('H-Request', $this->hRequest);
        $this->curl->setHeader('sec-ch-ua-mobile', '?0');
        $this->curl->setHeader('Content-Type', 'application/json');
        $this->curl->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36');
        $this->curl->setHeader('sec-ch-ua-platform', '"Windows"');
        $this->curl->setHeader('Accept', '*/*');
        $this->curl->setHeader('Origin', 'https://deals.hopper.com');
        $this->curl->setHeader('Sec-Fetch-Site', 'same-origin');
        $this->curl->setHeader('Sec-Fetch-Mode', 'cors');
        $this->curl->setHeader('Sec-Fetch-Dest', 'empty');
        $this->curl->setHeader('Referer', 'https://deals.hopper.com/');
        $this->curl->setHeader('Accept-Encoding', 'gzip, deflate, br');
        $this->curl->setHeader('Accept-Language', 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7');
        $this->curl->setOpt(CURLOPT_ENCODING, "");
        $this->curl->put('https://deals.hopper.com/api/v2/auth/finalize', '{"firstName":"' . $firstName . '","lastName":"' . $lastName . '","token":"' . $token . '","FinalizeRequest":"Phone","phoneNumber":"' . $phoneNumber . '"}');

        if ($this->curl->error) {
            echo '[-] Error: Input Data - ' . $this->curl->errorCode . ': ' . $this->curl->errorMessage . "\n\n";
        } else {
            $responseData = $this->curl->response;
            return $responseData;
        }
    }

    public function getUser($token)
    {
        $this->curl->setHeader('Host', 'deals.hopper.com');
        $this->curl->setTimeout(50);
        $this->curl->setConnectTimeout(50);
        $this->curl->setHeader('Connection', 'keep-alive');
        $this->curl->setHeader('sec-ch-ua', '"Chromium";v="104", " Not A;Brand";v="99", "Google Chrome";v="104"');
        $this->curl->setHeader('H-Request', $token);
        $this->curl->setHeader('sec-ch-ua-mobile', '?0');
        $this->curl->setHeader('Content-Type', 'application/json');
        $this->curl->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36');
        $this->curl->setHeader('sec-ch-ua-platform', '"Windows"');
        $this->curl->setHeader('Accept', '*/*');
        $this->curl->setHeader('Origin', 'https://deals.hopper.com');
        $this->curl->setHeader('Sec-Fetch-Site', 'same-origin');
        $this->curl->setHeader('Sec-Fetch-Mode', 'cors');
        $this->curl->setHeader('Sec-Fetch-Dest', 'empty');
        $this->curl->setHeader('Referer', 'https://deals.hopper.com/');
        $this->curl->setHeader('Accept-Encoding', 'gzip, deflate, br');
        $this->curl->setHeader('Accept-Language', 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7');
        $this->curl->setOpt(CURLOPT_ENCODING, "");
        $this->curl->put('https://deals.hopper.com/api/v2/auth/finalize', '{}');

        if ($this->curl->error) {
            echo '[-] Error: getUser - ' . $this->curl->errorCode . ': ' . $this->curl->errorMessage . "\n\n";
        } else {
            $responseData = $this->curl->response;
            return $responseData;
        }
    }

    public function phoneNumber()
    {
        $phoneNumber = $this->fakerPhoneNumber->tollFreePhoneNumber();
        $phoneNumber = str_replace(' ', '', $phoneNumber);
        $phoneNumber = str_replace('-', '', $phoneNumber);
        $phoneNumber = str_replace('(', '', $phoneNumber);
        $phoneNumber = str_replace(')', '', $phoneNumber);
        $phoneNumber = str_replace('.', '', $phoneNumber);
        return "+1" . $phoneNumber;
    }
}

function writeLog($location, $text, $config)
{
    $file = fopen($location, $config);
    fwrite($file, $text);
    fclose($file);
}

function deleteString($key, $file_name)
{
    $lines  = file($file_name);
    $search = $key;

    $result = '';
    foreach ($lines as $line) {
        if (stripos($line, $search) === false) {
            $result .= $line;
        }
    }
    file_put_contents($file_name, $result);
}

$banner = '
_______  _______  ______  ______  _______  ______ 
|   |   ||       ||   __ \|   __ \|    ___||   __ \
|       ||   -   ||    __/|    __/|    ___||      <
|___|___||_______||___|   |___|   |_______||___|__|
================================ HOPPER X WARIFP.CO
== CONTRIBUTORS : PRASETIYANTO NUGROHO | ABU YASKUR
';
echo $banner . "\n";

$socks5List = readline('[?] SOCKS5 List? : ');
$randEmail = 'n';
if ($randEmail == 'y') {
    $emailExt = readline('[?] Ext Mail? (@gmail.com) : ');
    echo "\n";
} else if ($randEmail == 'n') {
    $email = readline('[?] Email : ');
    echo "\n";
} else {
    echo "[-] Error: Input Data\n\n";
    exit;
}

register:
$hopperDeals = new HopperDeals();
$fakerName = $faker = Faker\Factory::create('Faker\Provider\en_US\Person');
$proxyChecker = $hopperDeals->proxyChecker($socks5List);

if (isset(($proxyChecker))) {
    $phoneNumber = $hopperDeals->phoneNumber();
    $firstName = (string)$fakerName->firstName;
    $lastName =  (string)$fakerName->lastName;

    if ($randEmail == 'y') {
        $email = strtolower($firstName) . strtolower($lastName) . $emailExt;
    }

    echo "\n[+] Proxy SOCKS5 " . $proxyChecker['proxy']  . " - " . $proxyChecker['ip'] . " is connected.\n\n";
    echo "[+] [ " . $email . " | " . $phoneNumber .  " | " . $firstName . " | " . $lastName . " ]\n";

    $register = $hopperDeals->register($email, $proxyChecker['proxy']);
    if ($register) {
        if ($register->SignInResponse == 'Success') {
            writeLog('log/proxy_live_used.json', "\n" . $proxyChecker['proxy'], 'a+');
            deleteString($proxyChecker['proxy'], $socks5List);

            $otp = readline('[?] Enter OTP Code: ');
            $verificationOTP = $hopperDeals->verificationOTP($otp, $email, $register->token, $proxyChecker['proxy']);

            if ($verificationOTP->VerifyCodeResponse == 'Success') {
                $inputData = $hopperDeals->inputData($firstName, $lastName, $verificationOTP->token, $phoneNumber, $proxyChecker['proxy']);

                if ($inputData->FinalizeResponse == 'Success') {

                    $dataToSave = [
                        'email' => $email,
                        'phoneNumber' => $phoneNumber,
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'token' => $verificationOTP->token,
                        'userId' => $inputData->authenticationTokens->userId,
                        'accessToken' => $inputData->authenticationTokens->accessToken,
                        'refreshToken' => $inputData->authenticationTokens->refreshToken,
                    ];
                    writeLog('log/result.json', "\n" . json_encode($dataToSave), 'a+');

                    echo "[+] Register Successful, results saved in log/result.json \n\n";

                    if ($randEmail == 'y') {
                        goto register;
                    }
                }

                // echo '[!] Error on Input Data' . "\n";
                exit();
            }
            echo '[!] Error [Verification OTP]: ' . $verificationOTP->message . "\n";
            exit;
        }
        echo '[!] Error [Register]: ' . $register->Failure . "\n";
        exit;
    }
} else {
    echo "[-] No Proxy Connected\n\n";
    exit;
}
