<?php
/**
 * log4php is a PHP port of the log4j java logging package.
 * 
 * <p>This framework is based on log4j (see {@link http://jakarta.apache.org/log4j log4j} for details).</p>
 * <p>Design, strategies and part of the methods documentation are developed by log4j team 
 * (Ceki Gülcü as log4j project founder and 
 * {@link http://jakarta.apache.org/log4j/docs/contributors.html contributors}).</p>
 *
 * <p>PHP port, extensions and modifications by VxR. All rights reserved.<br>
 * For more information, please see {@link http://www.vxr.it/log4php/}.</p>
 *
 * <p>This software is published under the terms of the LGPL License
 * a copy of which has been included with this distribution in the LICENSE file.</p>
 * 
 * @package log4php
 * @subpackage xml
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');
 
require_once(LOG4PHP_DIR . '/helpers/LoggerOptionConverter.php');
require_once(LOG4PHP_DIR . '/or/LoggerObjectRenderer.php');
require_once(LOG4PHP_DIR . '/spi/LoggerConfigurator.php');
require_once(LOG4PHP_DIR . '/LoggerAppender.php');
require_once(LOG4PHP_DIR . '/LoggerLayout.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');
require_once(LOG4PHP_DIR . '/LoggerManager.php');

define('LOG4PHP_LOGGER_DOM_CONFIGURATOR_APPENDER_STATE',    1000);
define('LOG4PHP_LOGGER_DOM_CONFIGURATOR_LAYOUT_STATE',      1010);
define('LOG4PHP_LOGGER_DOM_CONFIGURATOR_ROOT_STATE',        1020);
define('LOG4PHP_LOGGER_DOM_CONFIGURATOR_LOGGER_STATE',      1030);
define('LOG4PHP_LOGGER_DOM_CONFIGURATOR_FILTER_STATE',      1040);

define('LOG4PHP_LOGGER_DOM_CONFIGURATOR_DEFAULT_FILENAME',  './log4php.xml');

/**
 * @var string the default configuration document
 */
define('LOG4PHP_LOGGER_DOM_CONFIGURATOR_DEFAULT_CONFIGURATION', 
'<?xml version="1.0" ?>
<log4php:configuration threshold="all">
    <appender name="A1" class="LoggerAppenderEcho">
        <layout class="LoggerLayoutSimple" />
    </appender>
    <root>
        <level value="debug" />
        <appender_ref ref="A1" />
    </root>
</log4php:configuration>');

/**
 * @var string the elements namespace
 */
define('LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS', 'HTTP://WWW.VXR.IT/LOG4PHP/'); 

/**
 * Use this class to initialize the log4php environment using expat parser.
 *
 * <p>Read the log4php.dtd included in the documentation directory. Note that
 * php parser does not validate the document.</p>
 *
 * <p>Sometimes it is useful to see how log4php is reading configuration
 * files. You can enable log4php internal logging by setting the <var>debug</var> 
 * attribute in the <var>log4php:configuration</var> element. As in
 * <pre>
 * &lt;log4php:configuration <b>debug="true"</b> xmlns:log4php="http://www.vxr.it/log4php/">
 * ...
 * &lt;/log4php:configuration>
 * </pre>
 *
 * <p>There are sample XML files included in the package under <b>tests/</b> 
 * subdirectories.</p>
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.12 $
 * @package log4php
 * @subpackage xml
 * @since 0.4 
 */
class LoggerDOMConfigurator extends LoggerConfigurator {

    /**
     * @var LoggerHierarchy
     */
    var $repository;
    
    /**
     * @var array state stack 
     */
    var $state;

    /**
     * @var Logger parsed Logger  
     */
    var $logger;
    
    /**
     * @var LoggerAppender parsed LoggerAppender 
     */
    var $appender;
    
    /**
     * @var LoggerFilter parsed LoggerFilter 
     */
    var $filter;
    
    /**
     * @var LoggerLayout parsed LoggerLayout 
     */
    var $layout;
    
    /**
     * Constructor
     */
    function LoggerDOMConfigurator()
    {
        $this->state    = array();
        $this->logger   = null;
        $this->appender = null;
        $this->filter   = null;
        $this->layout   = null;
    }
    
    /**
     * Configure the default repository using the resource pointed by <b>url</b>.
     * <b>Url</b> is any valid resurce as defined in {@link PHP_MANUAL#file} function.
     * Note that the resource will be search with <i>use_include_path</i> parameter 
     * set to "1".
     *
     * @param string $url
     * @static
     */
    function configure($url = '')
    {
        $configurator = new LoggerDOMConfigurator();
        $repository =& LoggerManager::getLoggerRepository();
        return $configurator->doConfigure($url, $repository);
    }
    
    /**
     * Configure the given <b>repository</b> using the resource pointed by <b>url</b>.
     * <b>Url</b> is any valid resurce as defined in {@link PHP_MANUAL#file} function.
     * Note that the resource will be search with <i>use_include_path</i> parameter 
     * set to "1".
     *
     * @param string $url
     * @param LoggerHierarchy &$repository
     */
    function doConfigure($url = '', &$repository)
    {
        $xmlData = '';
        if (!empty($url))
            $xmlData = implode('', file($url, 1));
        return $this->doConfigureByString($xmlData, $repository);
    }
    
    /**
     * Configure the given <b>repository</b> using the configuration written in <b>xmlData</b>.
     * Do not call this method directly. Use {@link doConfigure()} instead.
     * @param string $xmlData
     * @param LoggerHierarchy &$repository
     */
    function doConfigureByString($xmlData, &$repository)
    {
        return $this->parse($xmlData, $repository);
    }
    
    /**
     * @param LoggerHierarchy &$repository
     */
    function doConfigureDefault(&$repository)
    {
        return $this->doConfigureByString(LOG4PHP_LOGGER_DOM_CONFIGURATOR_DEFAULT_CONFIGURATION, $repository);
    }
    
    /**
     * @param string $xmlData
     */
    function parse($xmlData, &$repository)
    {
        // LoggerManager::resetConfiguration();
        $this->repository =& $repository;

        $parser = xml_parser_create_ns();
    
        xml_set_object($parser, &$this);
        xml_set_element_handler($parser, "tagOpen", "tagClose");
        
        $result = xml_parse($parser, $xmlData, true);
        if (!$result) {
            $errorCode = xml_get_error_code($parser);
            $errorStr = xml_error_string($errorCode);
            $errorLine = xml_get_current_line_number($parser);
            LoggerLog::warn(
                "LoggerDOMConfigurator::parse() ".
                "Parsing error [{$errorCode}] {$errorStr}, line {$errorLine}"
            );
            $this->repository->resetConfiguration();
        } else {
            xml_parser_free($parser);
        }
        return $result;
    }
    
    /**
     * @param mixed $parser
     * @param string $tag
     * @param array $attribs
     *
     * @todo In 'LOGGER' case find a better way to detect 'getLogger()' method
     */
    function tagOpen($parser, $tag, $attribs)
    {
        switch ($tag) {
        
            case 'CONFIGURATION' :
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':CONFIGURATION':
            
                LoggerLog::debug("LoggerDOMConfigurator::tagOpen() CONFIGURATION");

                if (isset($attribs['THRESHOLD'])) {
                
                    $this->repository->setThreshold(
                        LoggerOptionConverter::toLevel(
                            $this->subst($attribs['THRESHOLD']), 
                            $this->repository->getThreshold()
                        )
                    );
                }
                if (isset($attribs['DEBUG'])) {
                    $debug = LoggerOptionConverter::toBoolean($this->subst($attribs['DEBUG']), LoggerLog::internalDebugging());
                    $this->repository->debug = $debug;
                    LoggerLog::internalDebugging($debug);
                    LoggerLog::debug("LoggerDOMConfigurator::tagOpen() LOG4PHP:CONFIGURATION. Internal Debug turned ".($debug ? 'on':'off'));
                    
                }
                break;
                
            case 'APPENDER' :
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':APPENDER':
            
                unset($this->appender);
                $this->appender = null;
                
                $name  = $this->subst(@$attribs['NAME']);
                $class = $this->subst(@$attribs['CLASS']);
                
                LoggerLog::debug("LoggerDOMConfigurator::tagOpen():tag=[$tag]:name=[$name]:class=[$class]");
                
                $this->appender =& LoggerAppender::singleton($name, $class);
                if ($this->appender === null) {
                    LoggerLog::warn("LoggerDOMConfigurator::tagOpen() APPENDER cannot instantiate appender '$name'");
                }
                $this->state[] = LOG4PHP_LOGGER_DOM_CONFIGURATOR_APPENDER_STATE;
                break;
                
            case 'APPENDER_REF' :
            case 'APPENDER-REF' :
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':APPENDER_REF':
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':APPENDER-REF':
            
            
                if (isset($attribs['REF']) and !empty($attribs['REF'])) {
                    $appenderName = $this->subst($attribs['REF']);
                    
                    LoggerLog::debug("LoggerDOMConfigurator::tagOpen() APPENDER-REF ref='$appenderName'");        
                    
                    $appender =& LoggerAppender::singleton($appenderName);
                    if ($appender !== null) {
                        switch (end($this->state)) {
                            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_LOGGER_STATE:
                            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_ROOT_STATE:                
                                $this->logger->addAppender($appender);
                                break;
                        }
                    } else {
                        LoggerLog::warn("LoggerDOMConfigurator::tagOpen() APPENDER-REF ref '$appenderName' points to a null appender");
                    }
                } else {
                    LoggerLog::warn("LoggerDOMConfigurator::tagOpen() APPENDER-REF ref not set or empty");            
                }
                break;
                
            case 'FILTER' :
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':FILTER':
            
                LoggerLog::debug("LoggerDOMConfigurator::tagOpen() FILTER");
                            
                unset($this->filter);
                $this->filter = null;

                $filterName = basename($this->subst(@$attribs['CLASS']));
                if (!empty($filterName)) {
                    if (!class_exists($filterName)) {
                        @include_once(LOG4PHP_DIR . "/varia/{$filterName}.php");
                    }
                    if (class_exists($filterName)) {
                        $this->filter = new $filterName();
                    } else {
                        LoggerLog::warn("LoggerDOMConfigurator::tagOpen() FILTER. class '$filterName' doesnt exist");
                    }
                    $this->state[] = LOG4PHP_LOGGER_DOM_CONFIGURATOR_FILTER_STATE;
                } else {
                    LoggerLog::warn("LoggerDOMConfigurator::tagOpen() FILTER filter name cannot be empty");
                }
                break;
                
            case 'LAYOUT':
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':LAYOUT':
            
                $class = @$attribs['CLASS'];

                LoggerLog::debug("LoggerDOMConfigurator::tagOpen() LAYOUT class='{$class}'");

                $this->layout = LoggerLayout::factory($this->subst($class));
                if ($this->layout === null)
                    LoggerLog::warn("LoggerDOMConfigurator::tagOpen() LAYOUT unable to instanciate class='{$class}'");
                
                $this->state[] = LOG4PHP_LOGGER_DOM_CONFIGURATOR_LAYOUT_STATE;
                break;
            
            case 'LOGGER':
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':LOGGER':
            
                // $this->logger is assigned by reference.
                // Only '$this->logger=null;' destroys referenced object
                unset($this->logger);
                $this->logger = null;
                
                $loggerName = $this->subst(@$attribs['NAME']);
                if (!empty($loggerName)) {
                    LoggerLog::debug("LoggerDOMConfigurator::tagOpen() LOGGER. name='$loggerName'");        
                    
                    $class = $this->subst(@$attribs['CLASS']);
                    if (empty($class)) {
                        $this->logger =& $this->repository->getLogger($loggerName);
                    } else {
                        $className = basename($class);
                        if (!class_exists($className))  
                            @include_once("{$class}.php");
                        if (!class_exists($className)) {
                            LoggerLog::warn(
                                "LoggerDOMConfigurator::tagOpen() LOGGER. ".
                                "cannot find '$className'."
                            );                        
                        } else {
                        
                            if (in_array('getlogger', get_class_methods($className))) {
                                $this->logger =& call_user_func(array($className, 'getlogger'), $loggerName);
                            } else {
                                LoggerLog::warn(
                                    "LoggerDOMConfigurator::tagOpen() LOGGER. ".
                                    "class '$className' doesnt implement 'getLogger()' method."
                                );                        
                            }
                        }
                    }    
                    if ($this->logger !== null and isset($attribs['ADDITIVITY'])) {
                        $additivity = LoggerOptionConverter::toBoolean($this->subst($attribs['ADDITIVITY']), true);     
                        $this->logger->setAdditivity($additivity);
                    }
                } else {
                    LoggerLog::warn("LoggerDOMConfigurator::tagOpen() LOGGER. Attribute 'name' is not set or is empty.");
                }
                $this->state[] = LOG4PHP_LOGGER_DOM_CONFIGURATOR_LOGGER_STATE;;
                break;
            
            case 'LEVEL':
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':LEVEL':
            case 'PRIORITY':
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':PRIORITY':
            
                if (!isset($attribs['VALUE'])) {
                    LoggerLog::debug("LoggerDOMConfigurator::tagOpen() LEVEL value not set");
                    break;
                }
                    
                LoggerLog::debug("LoggerDOMConfigurator::tagOpen() LEVEL value={$attribs['VALUE']}");
                
                if ($this->logger === null) { 
                    LoggerLog::warn("LoggerDOMConfigurator::tagOpen() LEVEL. parent logger is null");
                    break;
                }
        
                switch (end($this->state)) {
                    case LOG4PHP_LOGGER_DOM_CONFIGURATOR_ROOT_STATE:
                        $this->logger->setLevel(
                            LoggerOptionConverter::toLevel(
                                $this->subst($attribs['VALUE']), 
                                $this->logger->getLevel()
                            )
                        );
                        LoggerLog::debug("LoggerDOMConfigurator::tagOpen() LEVEL root level is now '{$attribs['VALUE']}' ");                
                        break;
                    case LOG4PHP_LOGGER_DOM_CONFIGURATOR_LOGGER_STATE:
                        $this->logger->setLevel(
                            LoggerOptionConverter::toLevel(
                                $this->subst($attribs['VALUE']), 
                                $this->logger->getLevel()
                            )
                        );
                        break;
                    default:
                        LoggerLog::warn("LoggerDOMConfigurator::tagOpen() LEVEL state '{$this->state}' not allowed here");
                }
                break;
            
            case 'PARAM':
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':PARAM':

                LoggerLog::debug("LoggerDOMConfigurator::tagOpen() PARAM");
                
                if (!isset($attribs['NAME'])) {
                    LoggerLog::warn(
                        "LoggerDOMConfigurator::tagOpen() PARAM. ".
                        "attribute 'name' not defined."
                    );
                    break;
                }
                if (!isset($attribs['VALUE'])) {
                    LoggerLog::warn(
                        "LoggerDOMConfigurator::tagOpen() PARAM. ".
                        "attribute 'value' not defined."
                    );
                    break;
                }
                    
                switch (end($this->state)) {
                    case LOG4PHP_LOGGER_DOM_CONFIGURATOR_APPENDER_STATE:
                        if ($this->appender !== null) {
                            $this->setter($this->appender, $this->subst($attribs['NAME']), $this->subst($attribs['VALUE']));
                        } else {
                            LoggerLog::warn(
                                "LoggerDOMConfigurator::tagOpen() PARAM. ".
                                " trying to set property to a null appender."
                            );
                        }
                        break;
                    case LOG4PHP_LOGGER_DOM_CONFIGURATOR_LAYOUT_STATE:
                        if ($this->layout !== null) {
                            $this->setter($this->layout, $this->subst($attribs['NAME']), $this->subst($attribs['VALUE']));                
                        } else {
                            LoggerLog::warn(
                                "LoggerDOMConfigurator::tagOpen() PARAM. ".
                                " trying to set property to a null layout."
                            );
                        }
                        break;
                    case LOG4PHP_LOGGER_DOM_CONFIGURATOR_FILTER_STATE:
                        if ($this->filter !== null) {
                            $this->setter($this->filter, $this->subst($attribs['NAME']), $this->subst($attribs['VALUE']));
                        } else {
                            LoggerLog::warn(
                                "LoggerDOMConfigurator::tagOpen() PARAM. ".
                                " trying to set property to a null filter."
                            );
                        }
                        break;
                    default:
                        LoggerLog::warn("LoggerDOMConfigurator::tagOpen() PARAM state '{$this->state}' not allowed here");
                }
                break;
            
            case 'RENDERER':
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':RENDERER':

                $renderedClass   = $this->subst(@$attribs['RENDEREDCLASS']);
                $renderingClass  = $this->subst(@$attribs['RENDERINGCLASS']);
        
                LoggerLog::debug("LoggerDOMConfigurator::tagOpen() RENDERER renderedClass='$renderedClass' renderingClass='$renderingClass'");
        
                if (!empty($renderedClass) and !empty($renderingClass)) {
                    $renderer = LoggerObjectRenderer::factory($renderingClass);
                    if ($renderer === null) {
                        LoggerLog::warn("LoggerDOMConfigurator::tagOpen() RENDERER cannot instantiate '$renderingClass'");
                    } else { 
                        $this->repository->setRenderer($renderedClass, $renderer);
                    }
                } else {
                    LoggerLog::warn("LoggerDOMConfigurator::tagOpen() RENDERER renderedClass or renderingClass is empty");        
                }
                break;
            
            case 'ROOT':
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':ROOT':
            
                LoggerLog::debug("LoggerDOMConfigurator::tagOpen() ROOT");
                
                $this->logger =& LoggerManager::getRootLogger();
                
                $this->state[] = LOG4PHP_LOGGER_DOM_CONFIGURATOR_ROOT_STATE;
                break;
                
        }
         
    }


    /**
     * @param mixed $parser
     * @param string $tag
     */
    function tagClose($parser, $tag)
    {
        switch ($tag) {
        
            case 'CONFIGURATION' : 
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':CONFIGURATION':
          
                LoggerLog::debug("LoggerDOMConfigurator::tagClose() CONFIGURATION");
                break;
                
            case 'APPENDER' :
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':APPENDER':
            
                LoggerLog::debug("LoggerDOMConfigurator::tagClose() APPENDER");
                
                if ($this->appender !== null) {
                    if ($this->appender->requiresLayout() and $this->appender->layout === null) {
                        $appenderName = $this->appender->getName();
                        LoggerLog::warn(
                            "LoggerDOMConfigurator::tagClose() APPENDER. ".
                            "'$appenderName' requires a layout that is not defined. ".
                            "Using a simple layout"
                        );
                        $this->appender->setLayout(LoggerLayout::factory('LoggerLayoutSimple'));
                    }                    
                    $this->appender->activateOptions();
                }        
                array_pop($this->state);        
                break;
                
            case 'FILTER' :
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':FILTER':
            
                LoggerLog::debug("LoggerDOMConfigurator::tagClose() FILTER");
                            
                if ($this->filter !== null) {
                    $this->filter->activateOptions();
                    $this->appender->addFilter($this->filter);
                    $this->filter = null;
                }
                array_pop($this->state);        
                break;
                
            case 'LAYOUT':
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':LAYOUT':

                LoggerLog::debug("LoggerDOMConfigurator::tagClose() LAYOUT");

                if ($this->appender !== null and $this->layout !== null and $this->appender->requiresLayout()) {
                    $this->layout->activateOptions();
                    $this->appender->setLayout($this->layout);
                    $this->layout = null;
                }
                array_pop($this->state);
                break;
            
            case 'LOGGER':
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':LOGGER':
            
                LoggerLog::debug("LoggerDOMConfigurator::tagClose() LOGGER");        

                array_pop($this->state);
                break;
            
            case 'ROOT':
            case LOG4PHP_LOGGER_DOM_CONFIGURATOR_XMLNS.':ROOT':
            
                LoggerLog::debug("LoggerDOMConfigurator::tagClose() ROOT");

                array_pop($this->state);
                break;
        }
    }
    
    /**
     * @param object $object
     * @param string $name
     * @param mixed $value
     */
    function setter(&$object, $name, $value)
    {
        if (empty($name)) {
            LoggerLog::debug("LoggerDOMConfigurator::setter() 'name' param cannot be empty");        
            return false;
        }
        $methodName = 'set'.ucfirst($name);
        if (method_exists($object, $methodName)) {
            LoggerLog::debug("LoggerDOMConfigurator::setter() Calling ".get_class($object)."::{$methodName}({$value})");
            return call_user_func(array(&$object, $methodName), $value);
        } else {
            LoggerLog::warn("LoggerDOMConfigurator::setter() ".get_class($object)."::{$methodName}() does not exists");
            return false;
        }
    }
    
    function subst($value)
    {
        return LoggerOptionConverter::substVars($value);
    }

}
?>