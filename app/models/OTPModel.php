<?php

class OTPModel
{
    private $jsonFile;

    public function __construct()
    {
        $this->jsonFile = DATA_PATH . '/otp.json';

        if (!file_exists($this->jsonFile)) {
            file_put_contents($this->jsonFile, json_encode([]));
        }
    }

    public function generate($email)
    {
        $otpData = json_decode(file_get_contents($this->jsonFile), true);

        $code = rand(100000, 999999);

        $otpData[$email] = [
            'code' => $code,
            'expires_at' => time() + 300
        ];

        file_put_contents($this->jsonFile, json_encode($otpData, JSON_PRETTY_PRINT));

        return $code;
    }

    public function verify($email, $code)
    {
        $otpData = json_decode(file_get_contents($this->jsonFile), true);

        if (!isset($otpData[$email])) {
            return false;
        }

        if (time() > $otpData[$email]['expires_at']) {
            return false;
        }

        return $otpData[$email]['code'] == $code;
    }
}
