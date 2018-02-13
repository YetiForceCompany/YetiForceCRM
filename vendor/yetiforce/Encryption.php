<?php

namespace App;

/**
 * Encryption basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Encryption
{
    protected $method = false;
    protected $pass = false;
    protected $vector = false;
    protected $options = true;

    /**
     * Class contructor.
     */
    public function __construct()
    {
        $row = (new \App\Db\Query())->from('a_#__encryption')->one(\App\Db::getInstance('admin'));
        if ($row) {
            $this->method = $row['method'];
            $this->vector = $row['pass'];
            $this->pass = \AppConfig::securityKeys('encryptionPass');
        }
    }

    public function encrypt($decrypted)
    {
        if (!$this->isActive()) {
            return $decrypted;
        }
        $encrypted = openssl_encrypt($decrypted, $this->method, $this->pass, $this->options, $this->vector);

        return base64_encode($encrypted);
    }

    public function decrypt($encrypted)
    {
        if (!$this->isActive()) {
            return $encrypted;
        }
        $decrypted = openssl_decrypt(base64_decode($encrypted), $this->method, $this->pass, $this->options, $this->vector);

        return $decrypted;
    }

    public function getMethods()
    {
        return openssl_get_cipher_methods();
    }

    public function isActive()
    {
        if (!function_exists('openssl_encrypt')) {
            return false;
        } elseif (empty($this->method)) {
            return false;
        } elseif ($this->method != \AppConfig::securityKeys('encryptionMethod')) {
            return false;
        } elseif (!in_array($this->method, $this->getMethods())) {
            return false;
        }

        return true;
    }

    /**
     * Generate random password.
     *
     * @param int $length
     *
     * @return string
     */
    public static function generatePassword($length = 10, $type = 'lbd')
    {
        $chars = [];
        if (strpos($type, 'l') !== false) {
            $chars[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if (strpos($type, 'b') !== false) {
            $chars[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if (strpos($type, 'd') !== false) {
            $chars[] = '0123456789';
        }
        if (strpos($type, 's') !== false) {
            $chars[] = '!"#$%&\'()*+,-./:;<=>?@[\]^_{|}';
        }
        $password = $allChars = '';
        foreach ($chars as $char) {
            $allChars .= $char;
            $password .= $char[array_rand(str_split($char))];
        }
        $allChars = str_split($allChars);
        $missing = $length - count($chars);
        for ($i = 0; $i < $missing; ++$i) {
            $password .= $allChars[array_rand($allChars)];
        }

        return str_shuffle($password);
    }

    /**
     * Generate user password.
     *
     * @param int $length
     *
     * @return string
     */
    public static function generateUserPassword($length = 10)
    {
        $passDetail = \Settings_Password_Record_Model::getUserPassConfig();
        if ($length > $passDetail['max_length']) {
            $length = $passDetail['max_length'];
        }
        if ($length < $passDetail['min_length']) {
            $length = $passDetail['min_length'];
        }
        $type = 'l';
        if ($passDetail['numbers'] === 'true') {
            $type .= 'd';
        }
        if ($passDetail['big_letters'] === 'true') {
            $type .= 'b';
        }
        if ($passDetail['special'] === 'true') {
            $type .= 's';
        }

        return static::generatePassword($length, $type);
    }

    /**
     * Function to create a hash.
     *
     * @param string $text
     *
     * @return string
     */
    public static function createHash($text)
    {
        return crypt($text, '$1$'.\AppConfig::main('application_unique_key'));
    }
}
