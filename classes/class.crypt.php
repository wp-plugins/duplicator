<?php
defined("ABSPATH") or die("");
// New encryption class

class DUP_Crypt
{
    public static function encrypt($key, $string)
    {
        $result = '';
        for ($i = 0; $i < strlen($string); $i++)
        {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result .= $char;
        }

        return urlencode(base64_encode($result));
    }

    public static function decrypt($key, $string)
    {
        $result = '';
        $string = urldecode($string);
        $string = base64_decode($string);

        for ($i = 0; $i < strlen($string); $i++)
        {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }

        return $result;
    }

    public static function scramble($string)
    {
        return self::encrypt(self::sk1() . self::sk2(), $string);
    }

    public static function unscramble($string)
    {
        return self::decrypt(self::sk1() . self::sk2(), $string);
    }

    public static function sk1()
    {
        return 'fdas' . self::encrypt('abx', 'v1');
    }

    public static function sk2()
    {
        return 'fres' . self::encrypt('ad3x', 'v2');
    }
}
