<?php

/**
 * REST API Server. It is the server part of the Restler framework.
 * Based on the RestServer code from 
 * <http://jacwright.com/blog/resources/RestServer.txt>
 *
 * @category   Framework
 * @package    restler
 * @author     Jac Wright <jacwright@gmail.com>
 * @author     R.Arul Kumaran <arul@luracast.com>
 * @copyright  2010 Luracast
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://luracast.com/products/restler/
 * @version    2.2.1
 */
class Restler
{
    // ==================================================================
    //
    // Public variables
    //
    // ------------------------------------------------------------------

    const VERSION = '2.2.1';

    /**
     * URL of the currently mapped service
     * @var string
     */
    public $url;

    /**
     * Http request method of the current request.
     * Any value between [GET, PUT, POST, DELETE]
     * @var string
     */
    public $request_method;

    /**
     * Requested data format. Instance of the current format class
     * which implements the iFormat interface
     * @var iFormat
     * @example jsonFormat, xmlFormat, yamlFormat etc
     */
    public $request_format;

    /**
     * Data sent to the service
     * @var array
     */
    public $request_data = array();

    /**
     * Used in production mode to store the URL Map to disk
     * @var string
     */
    public $cache_dir;

    /**
     * base directory to locate format and auth files
     * @var string
     */
    public $base_dir;

    /**
     * Name of an iRespond implementation class
     * @var string
     */
    public $response = 'DefaultResponse';

    /**
     * Response data format. Instance of the current format class
     * which implements the iFormat interface
     * @var iFormat
     * @example jsonFormat, xmlFormat, yamlFormat etc
     */
    public $response_format;

    // ==================================================================
    //
    // Private & Protected variables
    //
    // ------------------------------------------------------------------

    /**
     * When set to false, it will run in debug mode and parse the
     * class files every time to map it to the URL
     * @var boolean
     */
    protected $production_mode;

    /**
     * Associated array that maps urls to their respective class and method
     * @var array
     */
    protected $routes = array();

    /**
     * Associated array that maps formats to their respective format class name
     * @var array
     */
    protected $format_map = array();

    /**
     * Instance of the current api service class
     * @var object
     */
    protected $service_class_instance;

    /**
     * Name of the api method being called
     * @var string
     */
    protected $service_method;

    /**
     * list of authentication classes
     * @var array
     */
    protected $auth_classes = array();

    /**
     * list of error handling classes
     * @var array
     */
    protected $error_classes = array();

    /**
     * HTTP status codes
     * @var array
     */
    private $codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    );

    /**
     * Caching of url map is enabled or not
     * @var boolean
     */
    protected $cached;

    // ==================================================================
    //
    // Public functions
    //
    // ------------------------------------------------------------------


    /**
     * Constructor
     * @param boolean $production_mode When set to false, it will run in
     * debug mode and parse the class files every time to map it to the URL
     */
    public function __construct($production_mode = false)
    {
        $this->production_mode = $production_mode;
        $this->cache_dir = getcwd();
        $this->base_dir = RESTLER_PATH;
    }


    /**
     * Store the url map cache if needed
     */
    public function __destruct()
    {
        if ($this->production_mode && !($this->cached)) {
            $this->saveCache();
        }
    }


    /**
     * Use it in production mode to refresh the url map cache
     */
    public function refreshCache()
    {
        $this->routes = array();
        $this->cached = false;
    }


    /**
     * Call this method and pass all the formats that should be
     * supported by the API. Accepts multiple parameters
     * @param string class name of the format class that implements iFormat
     * @example $restler->setSupportedFormats('JsonFormat', 'XmlFormat'...);
     */
    public function setSupportedFormats()
    {
        $args = func_get_args();
        $extensions = array();
        foreach ($args as $class_name) {
            if (!is_string($class_name) || !class_exists($class_name)) {
                throw new Exception("$class_name is not a vaild Format Class.");
            }
            $obj = new $class_name;
            if (!($obj instanceof iFormat)) {
                throw new Exception('Invalid format class; must implement '
                    . 'iFormat interface');
            }
            foreach ($obj->getMIMEMap() as $extension => $mime) {
                if (!isset($this->format_map[$extension])) {
                    $this->format_map[$extension] = $class_name;
                }
                $mime = explode(',', $mime);
                if (!is_array($mime)) {
                    $mime = array($mime);
                }
                foreach ($mime as $value) {
                    if (!isset($this->format_map[$value])) {
                        $this->format_map[$value] = $class_name;
                    }
                }
                $extensions[".$extension"] = true;
            }
        }
        $this->format_map['default'] = $args[0];
        $this->format_map['extensions'] = array_keys($extensions);
    }


    /**
     * Add api classes throgh this method. All the public methods
     * that do not start with _ (underscore) will be  will be exposed
     * as the public api by default.
     *
     * All the protected methods that do not start with _ (underscore)
     * will exposed as protected api which will require authentication
     * @param string $class name of the service class
     * @param string $basePath optional url prefix for mapping, uses
     * lowercase version of the class name when not specified
     * @throws Exception when supplied with invalid class name
     */
    public function addAPIClass($class_name, $base_path = null)
    {
        if (!class_exists($class_name)) {
            throw new Exception("API class $class_name is missing.");
        }
        $this->loadCache();
        if (!$this->cached) {
            if (is_null($base_path)) {
                $base_path = strtolower($class_name);
                $index = strrpos($class_name, '\\');
                if ($index !== false) {
                    $base_path = substr($base_path, $index + 1);
                }
            } else {
                $base_path = trim($base_path, '/');
            }
            if (strlen($base_path) > 0) {
                $base_path .= '/';
            }
            $this->generateMap($class_name, $base_path);
        }
    }


    /**
     * protected methods will need atleast one authentication class to be set
     * in order to allow that method to be executed
     * @param string $class_name of the authentication class
     * @param string $base_path optional url prefix for mapping
     */
    public function addAuthenticationClass($class_name, $base_path = null)
    {
        $this->auth_classes[] = $class_name;
        $this->addAPIClass($class_name, $base_path);
    }


    /**
     * Add class for custom error handling
     * @param string $class_name of the error handling class
     */
    public function addErrorClass($class_name)
    {
        $this->error_classes[] = $class_name;
    }


    /**
     * Convenience method to respond with an error message
     * @param int $statusCode http error code
     * @param string $errorMessage optional custom error message
     */
    public function handleError($status_code, $error_message = null)
    {
        $method = "handle$status_code";
        $handled = false;
        foreach ($this->error_classes as $class_name) {
            if (method_exists($class_name, $method)) {
                $obj = new $class_name();
                $obj->restler = $this;
                $obj->$method();
                $handled = true;
            }
        }
        if ($handled) {
            return;
        }
        $message = $this->codes[$status_code]
            . (!$error_message ? '' : ': ' . $error_message);
        $this->setStatus($status_code);
        $responder = new $this->response();
        $responder->restler = $this;
        $this->sendData($responder->__formatError($status_code, $message));
    }


    /**
     * An initialize function to allow use of the restler error generation 
     * functions for pre-processing and pre-routing of requests.
     */
    public function init()
    {
        if (empty($this->format_map)) {
            $this->setSupportedFormats('JsonFormat');
        }
        $this->url = $this->getPath();
        $this->request_method = $this->getRequestMethod();
        $this->response_format = $this->getResponseFormat();
        $this->request_format = $this->getRequestFormat();
        if (is_null($this->request_format)) {
            $this->request_format = $this->response_format;
        }
        if ($this->request_method == 'PUT' || $this->request_method == 'POST') {
            $this->request_data = $this->getRequestData();
        }
    }


    /**
     * Main function for processing the api request
     * and return the response
     * @throws Exception when the api service class is missing
     * @throws RestException to send error response
     */
    public function handle()
    {
        $this->init();
        $o = $this->mapUrlToMethod();

        if (!isset($o->class_name)) {
            $this->handleError(404);
        } else {
            try {
                if ($o->method_flag) {
                    $auth_method = '__isAuthenticated';
                    if (!count($this->auth_classes)) {
                        throw new RestException(401);
                    }
                    foreach ($this->auth_classes as $auth_class) {
                        $auth_obj = new $auth_class();
                        $auth_obj->restler = $this;
                        $this->applyClassMetadata($auth_class, $auth_obj, $o);
                        if (!method_exists($auth_obj, $auth_method)) {
                            throw new RestException(401, 'Authentication Class '
                                . 'should implement iAuthenticate');
                        } else if (!$auth_obj->$auth_method()) {
                            throw new RestException(401);
                        }
                    }
                }
                $this->applyClassMetadata(get_class($this->request_format),
                    $this->request_format, $o);
                $pre_process = '_' . $this->request_format->getExtension() . '_'
                    . $o->method_name;
                $this->service_method = $o->method_name;
                if ($o->method_flag == 2) {
                    $o = unprotect($o);
                }
                $object = $this->service_class_instance = new $o->class_name();
                $object->restler = $this;
                if (method_exists($o->class_name, $pre_process)) {
                    call_user_func_array(
                        array($object, $pre_process), $o->arguments
                    );
                }
                switch ($o->method_flag) {
                    case 3:
                        $reflection_method = new ReflectionMethod($object,
                                $o->method_name);
                        $reflection_method->setAccessible(true);
                        $result = $reflection_method->invokeArgs($object,
                            $o->arguments);
                        break;
                    case 2:
                    case 1:
                    default:
                        $result = call_user_func_array(array(
                            $object,
                            $o->method_name), $o->arguments
                        );
                        break;
                }
            } catch (RestException $e) {
                $this->handleError($e->getCode(), $e->getMessage());
            }
        }
        $responder = new $this->response();
        $responder->restler = $this;
        $this->applyClassMetadata($this->response, $responder, $o);
        if (isset($result) && $result !== null) {
            $result = $responder->__formatResponse($result);
            $this->sendData($result);
        }
    }


    /**
     * Encodes the response in the prefered format
     * and sends back
     * @param $data array php data
     */
    public function sendData($data)
    {
        $data = $this->response_format->encode($data, 
            !($this->production_mode)
        );
        $post_process = '_' . $this->service_method . '_'
            . $this->response_format->getExtension();
        if (isset($this->service_class_instance)
            && method_exists($this->service_class_instance, $post_process)
        ) {
            $data = call_user_func(array($this->service_class_instance,
                $post_process), $data);
        }
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: 0");
        header('Content-Type: ' . $this->response_format->getMIME());
        //.'; charset=utf-8');
        header("X-Powered-By: Luracast Restler v" . Restler::VERSION);
		if($this->production_mode){
			die($data);
		}else{
			echo $data;
		}
    }


    /**
     * Sets the HTTP response status
     * @param int $code response code
     */
    public function setStatus($code)
    {
        header("{$_SERVER['SERVER_PROTOCOL']} $code " . 
            $this->codes[strval($code)]);
    }


    /**
     * Compare two strings and remove the common
     * sub string from the first string and return it
     * @param string $first
     * @param string $second
     * @param string $char optional, set it as
     * blank string for char by char comparison
     * @return string
     */
    public function removeCommonPath($first, $second, $char = '/')
    {
        $first = explode($char, $first);
        $second = explode($char, $second);
        while (count($second)) {
            if ($first[0] == $second[0]) {
                array_shift($first);
            } else {
                break;
            }
            array_shift($second);
        }
        return implode($char, $first);
    }


    /**
     * Save cache to file
     */
    public function saveCache()
    {
        $file = $this->cache_dir . '/routes.php';
        $s = '$o=array();' . PHP_EOL;
        foreach ($this->routes as $key => $value) {
            $s .= PHP_EOL . PHP_EOL . PHP_EOL . 
                "############### $key ###############" . PHP_EOL . PHP_EOL;
            $s .= '$o[\'' . $key . '\']=array();';
            foreach ($value as $ke => $va) {
                $s .= PHP_EOL . PHP_EOL . "#==== $key $ke" . PHP_EOL . PHP_EOL;
                $s .= '$o[\'' . $key . '\'][\'' . $ke . '\']=' . str_replace(
                        PHP_EOL, PHP_EOL . "\t", var_export($va, true)
                    ) . ';';
            }
        }
        $s .= PHP_EOL . 'return $o;';
        $r = @file_put_contents($file, "<?php $s");
        @chmod($file, 0777);
        if ($r === false) {
            throw new Exception(
                "The cache directory located at '$this->cache_dir' needs to "
                . "have the permissions set to read/write/execute for everyone"
                . " in order to save cache and improve performance.");
        }
    }

    // ==================================================================
    //
    // Protected functions
    //
    // ------------------------------------------------------------------


    /**
     * Parses the requst url and get the api path
     * @return string api path
     */
    protected function getPath()
    {
        $path = urldecode($this->removeCommonPath($_SERVER['REQUEST_URI'],
                $_SERVER['SCRIPT_NAME']));
        $path = preg_replace('/(\/*\?.*$)|(\/$)/', '', $path);
        $path = str_replace($this->format_map['extensions'], '', $path);
        return $path;
    }


    /**
     * Parses the request to figure out the http request type
     * @return string which will be one of the following
     * [GET, POST, PUT, DELETE]
     * @example GET
     */
    protected function getRequestMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        }
        //support for HEAD request
        if ($method == 'HEAD') {
            $method = 'GET';
        }
        return $method;
    }


    /**
     * Parses the request to figure out format of the request data
     * @return iFormat any class that implements iFormat
     * @example JsonFormat
     */
    protected function getRequestFormat()
    {
        $format = null;
        //check if client has sent any information on request format
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $mime = explode(';', $_SERVER['CONTENT_TYPE']);
            $mime = $mime[0];
            if ($mime == UrlEncodedFormat::MIME) {
                $format = new UrlEncodedFormat();
            } else {
                if (isset($this->format_map[$mime])) {
                    $format = $this->format_map[$mime];
                    $format = is_string($format) ? new $format : $format;
                    $format->setMIME($mime);
                }
            }
        }
        return $format;
    }


    /**
     * Parses the request to figure out the best format for response
     * @return iFormat any class that implements iFormat
     * @example JsonFormat
     */
    protected function getResponseFormat()
    {
        //check if client has specified an extension
        /**
         * @var iFormat
         */
        $format = null;
        $extensions = explode('.',
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        while ($extensions) {
            $extension = array_pop($extensions);
            $extension = explode('/', $extension);
            $extension = array_shift($extension);
            if ($extension && isset($this->format_map[$extension])) {
                $format = $this->format_map[$extension];
                $format = is_string($format) ? new $format : $format;
                $format->setExtension($extension);
                //echo "Extension $extension";
                return $format;
            }
        }
        //check if client has sent list of accepted data formats
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $acceptList = array();
            $accepts = explode(',', strtolower($_SERVER['HTTP_ACCEPT']));
            if (!is_array($accepts)) {
                $accepts = array($accepts);
            }
            foreach ($accepts as $pos => $accept) {
                $parts = explode(';q=', trim($accept));
                $type = array_shift($parts);
                $quality = count($parts) ? 
                    floatval(array_shift($parts)) : 
                    (1000 - $pos) / 1000;
                $acceptList[$type] = $quality;
            }
            arsort($acceptList);
            foreach ($acceptList as $accept => $quality) {
                if (isset($this->format_map[$accept])) {
                    $format = $this->format_map[$accept];
                    $format = is_string($format) ? new $format : $format;
                    $format->setMIME($accept);
                    //echo "MIME $accept";
                    // Tell cache content is based on Accept header
                    header("Vary: Accept"); 
                    return $format;
                }
            }
        } else {
            // RFC 2616: If no Accept header field is
            // present, then it is assumed that the
            // client accepts all media types.
            $_SERVER['HTTP_ACCEPT'] = '*/*';
        }
        if (strpos($_SERVER['HTTP_ACCEPT'], '*') !== false) {
            if (strpos($_SERVER['HTTP_ACCEPT'], 'application/*') !== false) {
                $format = new JsonFormat;
            } else if (strpos($_SERVER['HTTP_ACCEPT'], 'text/*') !== false) {
                $format = new XmlFormat;
            } else if (strpos($_SERVER['HTTP_ACCEPT'], '*/*') !== false) {
                $format = new $this->format_map['default'];
            }
        }
        if (empty($format)) {
            // RFC 2616: If an Accept header field is present, and if the 
            // server cannot send a response which is acceptable according to 
            // the combined Accept field value, then the server SHOULD send 
            // a 406 (not acceptable) response.
            header('HTTP/1.1 406 Not Acceptable');
            die('406 Not Acceptable: The server was unable to ' . 
                    'negotiate content for this request.');
        } else {
            // Tell cache content is based ot Accept header
            header("Vary: Accept"); 
            return $format;
        }
    }


    /**
     * Parses the request data and returns it
     * @return array php data
     */
    protected function getRequestData()
    {
        try {
            $r = file_get_contents('php://input');
            if (is_null($r)) {
                return $_GET;
            }
            $r = $this->request_format->decode($r);
            return is_null($r) ? array() : $r;
        } catch (RestException $e) {
            $this->handleError($e->getCode(), $e->getMessage());
        }
    }


    protected function mapUrlToMethod()
    {
        if (!isset($this->routes[$this->request_method])) {
            return array();
        }
        $urls = $this->routes[$this->request_method];
        if (!$urls) {
            return array();
        }

        $found = false;
        $this->request_data += $_GET;
        $params = array('request_data' => $this->request_data);
        $params += $this->request_data;
        $lc = strtolower($this->url);
        foreach ($urls as $url => $call) {
            //echo PHP_EOL.$url.' = '.$this->url.PHP_EOL;
            $call = (object) $call;
            if (strstr($url, ':')) {
                $regex = preg_replace('/\\\:([^\/]+)/', '(?P<$1>[^/]+)',
                    preg_quote($url));
                if (preg_match(":^$regex$:i", $this->url, $matches)) {
                    foreach ($matches as $arg => $match) {
                        if (isset($call->arguments[$arg])) {
                            //flog("$arg => $match $args[$arg]");
                            $params[$arg] = $match;
                        }
                    }
                    $found = true;
                    break;
                }
            } else if ($url == $lc) {
                $found = true;
                break;
            }
        }
        if ($found) {
            //echo PHP_EOL."Found $url ";
            //print_r($call);
            $p = $call->defaults;
            foreach ($call->arguments as $key => $value) {
                //echo "$key => $value \n";
                if (isset($params[$key])) {
                    $p[$value] = $params[$key];
                }
            }
            $call->arguments = $p;
            return $call;
        }
    }


    /**
     * Apply static and non-static properties defined in
     * the method information anotation
     * @param String $class_name
     * @param Object $instance instance of that class
     * @param Object $method_info method information and metadata
     */
    protected function applyClassMetadata($class_name, $instance, $method_info)
    {
        if (isset($method_info->metadata[$class_name])
            && is_array($method_info->metadata[$class_name])
        ) {
            foreach ($method_info->metadata[$class_name] as
                    $property => $value) {
                if (property_exists($class_name, $property)) {
                    $reflection_property = 
                        new ReflectionProperty($class_name, $property);
                    $reflection_property->setValue($instance, $value);
                }
            }
        }
    }


    protected function loadCache()
    {
        if ($this->cached !== null) {
            return;
        }
        $file = $this->cache_dir . '/routes.php';
        $this->cached = false;

        if ($this->production_mode) {
            if (file_exists($file)) {
                $routes = include($file);
            }
            if (isset($routes) && is_array($routes)) {
                $this->routes = $routes;
                $this->cached = true;
            }
        } else {
            //@unlink($this->cache_dir . "/$name.php");
        }
    }


    /**
     * Generates cachable url to method mapping
     * @param string $class_name
     * @param string $base_path
     */
    protected function generateMap($class_name, $base_path = '')
    {
        $reflection = new ReflectionClass($class_name);
        $class_metadata = parse_doc($reflection->getDocComment());
        $methods = $reflection->getMethods(
            ReflectionMethod::IS_PUBLIC + ReflectionMethod::IS_PROTECTED
        );
        foreach ($methods as $method) {
            $doc = $method->getDocComment();
            $arguments = array();
            $defaults = array();
            $metadata = $class_metadata + parse_doc($doc);
            $params = $method->getParameters();
            $position = 0;
            foreach ($params as $param) {
                $arguments[$param->getName()] = $position;
                $defaults[$position] = $param->isDefaultValueAvailable() ? 
                    $param->getDefaultValue() : null;
                $position++;
            }
            $method_flag = $method->isProtected() ? 
                (isRestlerCompatibilityModeEnabled() ? 2 : 3) : 
                (isset($metadata['protected']) ? 1 : 0);

            //take note of the order
            $call = array(
                'class_name' => $class_name,
                'method_name' => $method->getName(),
                'arguments' => $arguments,
                'defaults' => $defaults,
                'metadata' => $metadata,
                'method_flag' => $method_flag
            );
            $method_url = strtolower($method->getName());
            if (preg_match_all(
                '/@url\s+(GET|POST|PUT|DELETE|HEAD|OPTIONS)[ \t]*\/?(\S*)/s',
                    $doc, $matches, PREG_SET_ORDER)
            ) {
                foreach ($matches as $match) {
                    $http_method = $match[1];
                    $url = rtrim($base_path . $match[2], '/');
                    $this->routes[$http_method][$url] = $call;
                }
            } elseif ($method_url[0] != '_') { 
                //not prefixed with underscore
                // no configuration found so use convention
                if (preg_match_all('/^(GET|POST|PUT|DELETE|HEAD|OPTIONS)/i',
                        $method_url, $matches)
                ) {
                    $http_method = strtoupper($matches[0][0]);
                    $method_url = substr($method_url, strlen($http_method));
                } else {
                    $http_method = 'GET';
                }
                $url = $base_path
                    . ($method_url == 'index' || $method_url == 'default' ? '' :
                        $method_url);
                $url = rtrim($url, '/');
                $this->routes[$http_method][$url] = $call;
                foreach ($params as $param) {
                    if ($param->getName() == 'request_data') {
                        break;
                    }
                    $url .= $url == '' ? ':' : '/:';
                    $url .= $param->getName();
                    $this->routes[$http_method][$url] = $call;
                }
            }
        }
    }

}

if (version_compare(PHP_VERSION, '5.3.0') < 0) {
    require_once 'compat.php';
}

// ==================================================================
//
// Secondary classes
//
// ------------------------------------------------------------------

/**
 * Special Exception for raising API errors
 * that can be used in API methods
 * @category   Framework
 * @package    restler
 * @subpackage exception
 * @author     R.Arul Kumaran <arul@luracast.com>
 * @copyright  2010 Luracast
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://luracast.com/products/restler/
 */
class RestException extends Exception
{


    public function __construct($http_status_code, $error_message = null)
    {
        parent::__construct($error_message, $http_status_code);
    }

}

/**
 * Interface for creating response classes
 * @category   Framework
 * @package    restler
 * @subpackage result
 * @author     R.Arul Kumaran <arul@luracast.com>
 * @copyright  2010 Luracast
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://luracast.com/products/restler/
 */
interface iRespond
{


    /**
     * Result of an api call is passed to this method
     * to create a standard structure for the data
     * @param unknown_type $result can be a primitive or array or object
     */
    public function __formatResponse($result);


    /**
     * When the api call results in RestException this method
     * will be called to return the error message
     * @param int $status_code
     * @param String $message
     */
    public function __formatError($status_code, $message);
}

/**
 * Default response formating class
 * @category   Framework
 * @package    restler
 * @subpackage result
 * @author     R.Arul Kumaran <arul@luracast.com>
 * @copyright  2010 Luracast
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://luracast.com/products/restler/
 */
class DefaultResponse implements iRespond
{


    function __formatResponse($result)
    {
        return $result;
    }


    function __formatError($statusCode, $message)
    {
        return array(
            'error' => array(
                'code' => $statusCode,
                'message' => $message
            )
        );
    }

}

/**
 * Interface for creating authentication classes
 * @category   Framework
 * @package    restler
 * @subpackage auth
 * @author     R.Arul Kumaran <arul@luracast.com>
 * @copyright  2010 Luracast
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://luracast.com/products/restler/
 */
interface iAuthenticate
{


    /**
     * Auth function that is called when a protected method is requested
     * @return boolean true or false
     */
    public function __isAuthenticated();
}

/**
 * Interface for creating custom data formats
 * like xml, json, yaml, amf etc
 * @category   Framework
 * @package    restler
 * @subpackage format
 * @author     R.Arul Kumaran <arul@luracast.com>
 * @copyright  2010 Luracast
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://luracast.com/products/restler/
 */
interface iFormat
{


    /**
     * Get Extension => MIME type mappings as an associative array
     * @return array list of mime strings for the format
     * @example array('json'=>'application/json');
     */
    public function getMIMEMap();


    /**
     * Set the selected MIME type
     * @param string $mime MIME type
     */
    public function setMIME($mime);


    /**
     * Get selected MIME type
     */
    public function getMIME();


    /**
     * Set the selected file extension
     * @param string $extension file extension
     */
    public function setExtension($extension);


    /**
     * Get the selected file extension
     * @return string file extension
     */
    public function getExtension();


    /**
     * Encode the given data in the format
     * @param array $data resulting data that needs to
     * be encoded in the given format
     * @param boolean $human_readable set to true when restler
     * is not running in production mode. Formatter has to
     * make the encoded output more human readable
     * @return string encoded string
     */
    public function encode($data, $human_readable = false);


    /**
     * Decode the given data from the format
     * @param string $data data sent from client to
     * the api in the given format.
     * @return array associative array of the parsed data
     */
    public function decode($data);
}

/**
 * URL Encoded String Format
 * @category   Framework
 * @package    restler
 * @subpackage format
 * @author     R.Arul Kumaran <arul@luracast.com>
 * @copyright  2010 Luracast
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://luracast.com/products/restler/
 */
class UrlEncodedFormat implements iFormat
{

    const MIME = 'application/x-www-form-urlencoded';
    const EXTENSION = 'post';


    public function getMIMEMap()
    {
        return array(self::EXTENSION => self::MIME);
    }


    public function getMIME()
    {
        return self::MIME;
    }


    public function getExtension()
    {
        return self::EXTENSION;
    }


    public function setMIME($mime)
    {
        //do nothing
    }


    public function setExtension($extension)
    {
        //do nothing
    }


    public function encode($data, $human_readable = false)
    {
        return http_build_query($data);
    }


    public function decode($data)
    {
        parse_str($data, $r);
        return $r;
    }


    public function __toString()
    {
        return $this->getExtension();
    }

}

/**
 * Javascript Object Notation Format
 * @category   Framework
 * @package    restler
 * @subpackage format
 * @author     R.Arul Kumaran <arul@luracast.com>
 * @copyright  2010 Luracast
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://luracast.com/products/restler/
 */
class JsonFormat implements iFormat
{

    const MIME = 'application/json,application/javascript';

    static $mime = 'application/json';

    const EXTENSION = 'json';


    public function getMIMEMap()
    {
        return array(self::EXTENSION => self::MIME);
    }


    public function getMIME()
    {
        return self::$mime;
    }


    public function getExtension()
    {
        return self::EXTENSION;
    }


    public function setMIME($mime)
    {
        self::$mime = $mime;
    }


    public function setExtension($extension)
    {
        //do nothing
    }


    public function encode($data, $human_readable = false)
    {
        return $human_readable ? 
            $this->json_format(json_encode(object_to_array($data))) : 
            json_encode(object_to_array($data));
    }


    public function decode($data)
    {
        $decoded = json_decode($data);
        if (function_exists('json_last_error')) {
            $message = '';
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    return object_to_array($decoded);
                    break;
                case JSON_ERROR_DEPTH:
                    $message = 'maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $message = 'underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $message = 'unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $message = 'malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $message = 'malformed UTF-8 characters, '.
                        'possibly incorrectly encoded';
                    break;
                default:
                    $message = 'unknown error';
                    break;
            }
            throw new RestException(400, 'Error parsing JSON, ' . $message);
        } else if (strlen($data) && $decoded === null || $decoded === $data) {
            throw new RestException(400, 'Error parsing JSON');
        }
        return object_to_array($decoded);
    }


    /**
     * Pretty print JSON string
     * @param string $json
     * @return string formated json
     */
    private function json_format($json)
    {
        $tab = "  ";
        $new_json = "";
        $indent_level = 0;
        $in_string = false;
        $len = strlen($json);

        for ($c = 0; $c < $len; $c++) {
            $char = $json[$c];
            switch ($char) {
                case '{':
                case '[':
                    if (!$in_string) {
                        $new_json .= $char . "\n" .
                            str_repeat($tab, $indent_level + 1);
                        $indent_level++;
                    } else {
                        $new_json .= $char;
                    }
                    break;
                case '}':
                case ']':
                    if (!$in_string) {
                        $indent_level--;
                        $new_json .= "\n" . str_repeat($tab, $indent_level) 
                            . $char;
                    } else {
                        $new_json .= $char;
                    }
                    break;
                case ',':
                    if (!$in_string) {
                        $new_json .= ",\n" . str_repeat($tab, $indent_level);
                    } else {
                        $new_json .= $char;
                    }
                    break;
                case ':':
                    if (!$in_string) {
                        $new_json .= ": ";
                    } else {
                        $new_json .= $char;
                    }
                    break;
                case '"':
                    if ($c == 0) {
                        $in_string = true;
                    } else if ($c > 0 && $json[$c - 1] != '\\') {
                        $in_string = !$in_string;
                    }
                default:
                    $new_json .= $char;
                    break;
            }
        }

        return $new_json;
    }


    public function __toString()
    {
        return $this->getExtension();
    }

}

/**
 * Parses the PHPDoc comments for metadata. Inspired by Documentor code base
 * @category   Framework
 * @package    restler
 * @subpackage helper
 * @author     Murray Picton <info@murraypicton.com>
 * @author     R.Arul Kumaran <arul@luracast.com>
 * @copyright  2010 Luracast
 * @license    http://www.gnu.org/licenses/ GNU General Public License
 * @link       https://github.com/murraypicton/Doqumentor
 */
class DocParser
{

    private $params = array();


    function parse($doc = '')
    {
        if ($doc == '') {
            return $this->params;
        }
        //Get the comment
        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment) === false) {
            return $this->params;
        }
        $comment = trim($comment[1]);
        //Get all the lines and strip the * from the first character
        if (preg_match_all('#^\s*\*(.*)#m', $comment, $lines) === false) {
            return $this->params;
        }
        $this->parseLines($lines[1]);
        return $this->params;
    }


    private function parseLines($lines)
    {
        foreach ($lines as $line) {
            $parsedLine = $this->parseLine($line); //Parse the line

            if ($parsedLine === false && !isset($this->params['description'])) {
                if (isset($desc)) {
                    //Store the first line in the short description
                    $this->params['description'] = implode(PHP_EOL, $desc);
                }
                $desc = array();
            } else if ($parsedLine !== false) {
                $desc[] = $parsedLine; //Store the line in the long description
            }
        }
        $desc = implode(' ', $desc);
        if (!empty($desc)) {
            $this->params['long_description'] = $desc;
        }
    }


    private function parseLine($line)
    {
        //trim the whitespace from the line
        $line = trim($line);

        if (empty($line)) {
            return false; //Empty line
        }

        if (strpos($line, '@') === 0) {
            if (strpos($line, ' ') > 0) {
                //Get the parameter name
                $param = substr($line, 1, strpos($line, ' ') - 1);
                $value = substr($line, strlen($param) + 2); //Get the value
            } else {
                $param = substr($line, 1);
                $value = '';
            }
            //Parse the line and return false if the parameter is valid
            if ($this->setParam($param, $value)) {
                return false;
            }
        }
        return $line;
    }


    private function setParam($param, $value)
    {
        if ($param == 'param' || $param == 'return') {
            $value = $this->formatParamOrReturn($value);
        }
        if ($param == 'class') {
            list($param, $value) = $this->formatClass($value);
        }

        if (empty($this->params[$param])) {
            $this->params[$param] = $value;
        } else if ($param == 'param') {
            $arr = array($this->params[$param], $value);
            $this->params[$param] = $arr;
        } else {
            $this->params[$param] = $value + $this->params[$param];
        }
        return true;
    }


    private function formatClass($value)
    {
        $r = preg_split("[\(|\)]", $value);
        if (count($r) > 1) {
            $param = $r[0];
            parse_str($r[1], $value);
            foreach ($value as $key => $val) {
                $val = explode(',', $val);
                if (count($val) > 1) {
                    $value[$key] = $val;
                }
            }
        } else {
            $param = 'Unknown';
        }
        return array($param, $value);
    }


    private function formatParamOrReturn($string)
    {
        $pos = strpos($string, ' ');
        $type = substr($string, 0, $pos);
        return '(' . $type . ')' . substr($string, $pos + 1);
    }

}


// ==================================================================
//
// Individual functions
//
// ------------------------------------------------------------------

function parse_doc($php_doc_comment)
{
    $p = new DocParser();
    return $p->parse($php_doc_comment);

    $p = new Parser($php_doc_comment);
    return $p;

    $php_doc_comment = preg_replace(
        "/(^[\\s]*\\/\\*\\*)
        |(^[\\s]\\*\\/)
        |(^[\\s]*\\*?\\s)
        |(^[\\s]*)
        |(^[\\t]*)/ixm",
        "", $php_doc_comment);
    $php_doc_comment = str_replace("\r", "", $php_doc_comment);
    $php_doc_comment = preg_replace("/([\\t])+/", "\t", $php_doc_comment);
    return explode("\n", $php_doc_comment);

    $php_doc_comment = trim(preg_replace('/\r?\n *\* */', ' ', 
            $php_doc_comment));
    return $php_doc_comment;

    preg_match_all('/@([a-z]+)\s+(.*?)\s*(?=$|@[a-z]+\s)/s', $php_doc_comment,
        $matches);
    return array_combine($matches[1], $matches[2]);
}


/**
 * Conveniance function that converts the given object
 * in to associative array, leaves object alone if
 * JsonSerializable interface is detected
 * @param object $object that needs to be converted
 * @category   Framework
 * @package    restler
 * @subpackage format
 * @author     R.Arul Kumaran <arul@luracast.com>
 * @copyright  2010 Luracast
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://luracast.com/products/restler/
 */
function object_to_array($object, $utf_encode = false)
{
    if (is_array($object)
        || (is_object($object)
        && !($object instanceof JsonSerializable))
    ) {
        $array = array();
        foreach ($object as $key => $value) {
            $value = object_to_array($value, $utf_encode);
            if ($utf_encode && is_string($value)) {
                $value = utf8_encode($value);
            }
            $array[$key] = $value;
        }
        return $array;
    }
    return $object;
}


/**
 * an autoloader function for loading format classes
 * @param String $class_name class name of a class that implements iFormat
 */
function autoload_formats($class_name)
{
    $class_name = strtolower($class_name);
	
    $file = RESTLER_PATH . "../../../api/mobile_services/$class_name.php";
    if (file_exists($file)) {
        require_once ($file);
    } else {
		$file = RESTLER_PATH . "/../../api/mobile_services/$class_name.php";
        if (file_exists($file)) {
            require_once ($file);
        } elseif (file_exists(RESTLER_PATH . "/../api/mobile_services/$class_name.php")) {
            require_once ("/../api/mobile_services/$class_name.php");
        } elseif (file_exists("$class_name.php")) {
            require_once ("$class_name.php");
        }
    }
}

// ==================================================================
//
// Autoload
//
// ------------------------------------------------------------------

spl_autoload_register('autoload_formats');

/**
 * Manage compatibility with PHP 5 < PHP 5.3
 */
if (!function_exists('isRestlerCompatibilityModeEnabled')) {


    function isRestlerCompatibilityModeEnabled()
    {
        return false;
    }

}
define('RESTLER_PATH', dirname(__FILE__));