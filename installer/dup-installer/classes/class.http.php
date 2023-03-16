<?php

defined("DUPXABSPATH") or die("");

/**
 *  CLASS::DUPX_Http
 *  Http Class Utility
 */
class DUPX_HTTP
{
    /**
     *  Do an http post request with curl or php code
     *
     *  @param string $url      A URL to post to
     *  @param string $params   A valid key/pair combo $data = array('key1' => 'value1', 'key2' => 'value2');
     *  @param array $headers  Optional header elements
     *
     *  @return string or FALSE on failure.
     */
    public static function post($url, $params = array(), $headers = null)
    {
        //PHP POST
        if (!function_exists('curl_init')) {
            return self::php_get_post($url, $params, $headers = null, 'POST');
        }

        //CURL POST
        $headers_on = isset($headers) && array_count_values($headers);
        $params     = http_build_query($params);
        $ch         = curl_init();
        // Return contents of transfer on curl_exec
        // Allow self-signed certs
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, $headers_on);
        if ($headers_on) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_POST, count($params));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     *  Do an http post request with curl or php code
     *
     *  @param string $url      A URL to get.  If $params is not null then all query strings will be removed.
     *  @param string $params   A valid key/pair combo $data = array('key1' => 'value1', 'key2' => 'value2');
     *  @param string $headers  Optional header elements
     *
     *  @return string|bool a string or FALSE on failure.
     */
    public static function get($url, $params = array(), $headers = null)
    {
        //PHP GET
        if (!function_exists('curl_init')) {
            return self::php_get_post($url, $params, $headers = null, 'GET');
        }

        //Remove query string if $params are passed
        $full_url = $url;
        if (count($params)) {
            $url      = preg_replace('/\?.*/', '', $url);
            $full_url = $url . '?' . http_build_query($params);
        }
        $headers_on = isset($headers) && array_count_values($headers);
        $ch         = curl_init();
        // Return contents of transfer on curl_exec
        // Allow self-signed certs
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL, $full_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, $headers_on);
        if ($headers_on) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     *  Check to see if the internet is accessible
     *
     *  @param string $host      A URL e.g without prefix "ajax.googleapis.com"
     *  @param string $port      A valid port number
     *
     *  @return bool
     */
    public static function is_url_active($url, $port = 443, $timeout = 5)
    {
        //localhost will not have cpanel in most cases
        if (strpos($url, 'localhost:2083') !== false) {
            return false;
        }

        switch (true) {
            case function_exists('curl_init'):
                return self::is_url_active_curl($url, $port, $timeout);
            break;

            case function_exists('fsockopen'):
                return self::is_url_active_fsockopen($url, $port, $timeout);
            break;

            default:
                return false;
            break;
        }
    }

    /**
     *  Returns the host part of a URL
     *
     *  @param string $url      A valid URL
     *
     *  @return string
     */
    public static function parse_host($url)
    {
        $url = parse_url(trim($url));
        if ($url == false) {
            return null;
        }
        return trim($url['host'] ? $url['host'] : array_shift(explode('/', $url['path'], 2)));
    }

    /**
     *  Return the current page URL
     *
     *  @param bool $withQuery      Return the URL with its string
     *
     *  @return string
     */
    public static function get_page_url($withQuery = true)
    {
        $protocol = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        || (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && intval($_SERVER['SERVER_PORT']) === 443) ? 'https' : 'http';

        $uri = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        return $withQuery ? $uri : str_replace('?' . $_SERVER['QUERY_STRING'], '', $uri);
    }

    //PHP POST or GET requets
    private static function php_get_post($url, $params, $headers = null, $method = 'POST')
    {
        $full_url = $url;
        if ($method == 'GET' && count($params)) {
            $url      = preg_replace('/\?.*/', '', $url);
            $full_url = $url . '?' . http_build_query($params);
        }

        $data = array('http' => array(
                'method'  => $method,
                'content' => http_build_query($params)));
        if ($headers !== null) {
            $data['http']['header'] = $headers;
        }
        $ctx = stream_context_create($data);
        $fp  = @fopen($full_url, 'rb', false, $ctx);
        if (!$fp) {
            throw new Exception("Problem with $full_url, $php_errormsg");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            throw new Exception("Problem reading data from $full_url, $php_errormsg");
        }
        return $response;
    }

    //Check URL with fsockopen/
    private static function is_url_active_fsockopen($url, $port = 443, $timeout = 5)
    {
        try {
            $host       = parse_url($url, PHP_URL_HOST);
            $host       = DUPX_U::is_ssl() ? "ssl://{$host}" : $host;
            $errno      = 0;
            $message    = 'Error with fsockopen';
            $connection = @fsockopen($host, $port, $code, $message, $timeout);

            if (!is_resource($connection)) {
                return false;
            }
            @fclose($connection);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    //Check URL with curl
    private static function is_url_active_curl($url, $port = 443, $timeout = 5)
    {
        try {
            $result = false;
            $url    = filter_var($url, FILTER_VALIDATE_URL);
            $handle = curl_init($url);

            /* Set curl parameter */
            curl_setopt_array($handle, array(
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_NOBODY => true,
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_PORT => $port
            ));

            $response = curl_exec($handle);

            $httpCode = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);  // Try to get the last url
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);      // Get http status from last url

            /* Check for 200 (file is found). */
            if ($httpCode == 200) {
                $result = true;
            }

            curl_close($handle);
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
}
