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
 * @subpackage spi
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');
 
/**
 */
require_once(LOG4PHP_DIR . '/spi/LoggerLocationInfo.php');
require_once(LOG4PHP_DIR . '/LoggerManager.php');
require_once(LOG4PHP_DIR . '/LoggerMDC.php');
require_once(LOG4PHP_DIR . '/LoggerNDC.php');

/**
 * The internal representation of logging event.
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.16 $
 * @package log4php
 * @subpackage spi 
 */
class LoggerLoggingEvent {

    /** 
    * @var string Fully Qualified Class Name of the calling category class.
    */
    var $fqcn;
    
    /**
    * @var Logger reference
    */
    var $logger = null;
    
    /** 
    * The category (logger) name.
    * This field will be marked as private in future
    * releases. Please do not access it directly. 
    * Use the {@link getLoggerName()} method instead.
    * @deprecated 
    */
    var $categoryName;
    
    /** 
    * Level of logging event.
    * <p> This field should not be accessed directly. You shoud use the
    * {@link getLevel()} method instead.
    *
    * @deprecated
    * @var LoggerLevel
    */
    var $level;
    
    /** 
     * @var string The nested diagnostic context (NDC) of logging event. 
     */
    var $ndc;
    
    /** 
     * Have we tried to do an NDC lookup? If we did, there is no need
     * to do it again.  Note that its value is always false when
     * serialized. Thus, a receiving SocketNode will never use it's own
     * (incorrect) NDC. See also writeObject method.
     * @var boolean
     */
    var $ndcLookupRequired = true;
    
    /** 
     * Have we tried to do an MDC lookup? If we did, there is no need
     * to do it again.  Note that its value is always false when
     * serialized. See also the getMDC and getMDCCopy methods.
     * @var boolean  
     */
    var $mdcCopyLookupRequired = true;
    
    /** 
     * @var mixed The application supplied message of logging event. 
     */
    var $message;
    
    /** 
     * The application supplied message rendered through the log4php
     * objet rendering mechanism. At present renderedMessage == message.
     * @var string
     */
    var $renderedMessage;
    
    /** 
     * The name of thread in which this logging event was generated.
     * log4php saves here the process id via {@link PHP_MANUAL#getmypid getmypid()} 
     * @var mixed
     */
    var $threadName = null;
    
    /** 
    * The number of seconds elapsed from 1/1/1970 until logging event
    * was created plus microseconds if available.
    * @var float
    */
    var $timeStamp;
    
    /** 
    * @var LoggerLocationInfo Location information for the caller. 
    */
    var $locationInfo = null;
    
    // Serialization
    /*
    var $serialVersionUID = -868428216207166145L;
    var $PARAM_ARRAY = array();
    var $TO_LEVEL = "toLevel";
    var $TO_LEVEL_PARAMS = null;
    var $methodCache = array(); // use a tiny table
    */

    /**
    * Instantiate a LoggingEvent from the supplied parameters.
    *
    * <p>Except {@link $timeStamp} all the other fields of
    * LoggerLoggingEvent are filled when actually needed.
    *
    * @param string $fqcn name of the caller class.
    * @param mixed &$logger The {@link Logger} category of this event or the logger name.
    * @param LoggerLevel $priority The level of this event.
    * @param mixed $message The message of this event.
    * @param integer $timeStamp the timestamp of this logging event.
    */
    function LoggerLoggingEvent($fqcn, &$logger, $priority, $message, $timeStamp = null)
    {
        $this->fqcn = $fqcn;
        if (is_a($logger, 'logger')) {
            $this->logger =& $logger;
            $this->categoryName = $logger->getName();
        } else {
            $this->categoryName = (string)$logger;
        }
        $this->level = $priority;
        $this->message = $message;
        if ($timeStamp !== null and is_float($timeStamp)) {
            $this->timeStamp = $timeStamp;
        } else {
            if (function_exists('microtime')) {
                list($usecs, $secs) = explode(' ', microtime());
                $this->timeStamp = ((float)$usecs + (float)$secs);
            } else {
                $this->timeStamp = time();
            }
        }
    }

    /**
     * Set the location information for this logging event. The collected
     * information is cached for future use.
     *
     * <p>This method uses {@link PHP_MANUAL#debug_backtrace debug_backtrace()} function (if exists)
     * to collect informations about caller.</p>
     * <p>It only recognize informations generated by {@link Logger} and its subclasses.</p>
     * @return LoggerLocationInfo
     */
    function getLocationInformation()
    {
        if($this->locationInfo === null) {

            $locationInfo = array();

            if (function_exists('debug_backtrace')) {
                $trace = debug_backtrace();
                $prevHop = null;
                // make a downsearch to identify the caller
                $hop = array_pop($trace);
                while ($hop !== null) {
                    $className = @$hop['class'];
                    if ( !empty($className) and ($className == 'logger' or get_parent_class($className) == 'logger') ) {
                        $locationInfo['line'] = $hop['line'];
                        $locationInfo['file'] = $hop['file'];                         
                        break;
                    }
                    $prevHop = $hop;
                    $hop = array_pop($trace);
                }
                $locationInfo['class'] = isset($prevHop['class']) ? $prevHop['class'] : 'main';
                if (isset($prevHop['function']) and
                    $prevHop['function'] !== 'include' and
                    $prevHop['function'] !== 'include_once' and
                    $prevHop['function'] !== 'require' and
                    $prevHop['function'] !== 'require_once') {                                        
    
                    $locationInfo['function'] = $prevHop['function'];
                } else {
                    $locationInfo['function'] = 'main';
                }
            }
                     
            $this->locationInfo = new LoggerLocationInfo($locationInfo, $this->fqcn);
        }
        return $this->locationInfo;
    }

    /**
     * Return the level of this event. Use this form instead of directly
     * accessing the {@link $level} field.
     * @return LoggerLevel  
     */
    function getLevel()
    {
        return $this->level;
    }

    /**
     * Return the name of the logger. Use this form instead of directly
     * accessing the {@link $categoryName} field.
     * @return string  
     */
    function getLoggerName()
    {
        return $this->categoryName;
    }

    /**
     * Return the message for this logging event.
     *
     * <p>Before serialization, the returned object is the message
     * passed by the user to generate the logging event. After
     * serialization, the returned value equals the String form of the
     * message possibly after object rendering.
     * @return mixed
     */
    function getMessage()
    {
        if($this->message !== null) {
            return $this->message;
        } else {
            return $this->getRenderedMessage();
        }
    }

    /**
     * This method returns the NDC for this event. It will return the
     * correct content even if the event was generated in a different
     * thread or even on a different machine. The {@link LoggerNDC::get()} method
     * should <b>never</b> be called directly.
     * @return string  
     */
    function getNDC()
    {
        if ($this->ndcLookupRequired) {
            $this->ndcLookupRequired = false;
            $this->ndc = implode(' ',LoggerNDC::get());
        }
        return $this->ndc;
    }


    /**
     * Returns the the context corresponding to the <code>key</code>
     * parameter.
     * @return string
     */
    function getMDC($key)
    {
        return LoggerMDC::get($key);
    }

    /**
     * Render message.
     * @return string
     */
    function getRenderedMessage()
    {
        if($this->renderedMessage === null and $this->message !== null) {
            if (is_string($this->message)) {
	            $this->renderedMessage = $this->message;
            } else {
                if ($this->logger !== null) {
                    $repository =& $this->logger->getLoggerRepository();
                } else {
                    $repository =& LoggerManager::getLoggerRepository();
                }
                if (method_exists($repository, 'getrenderermap')) {
                    $rendererMap =& $repository->getRendererMap();
	                $this->renderedMessage= $rendererMap->findAndRender($this->message);
	            } else {
	                $this->renderedMessage = (string)$this->message;
	            }
            }
        }
        return $this->renderedMessage;
    }

    /**
     * Returns the time when the application started, in seconds
     * elapsed since 01.01.1970 plus microseconds if available.
     *
     * @return float
     * @static
     */
    function getStartTime()
    {
        static $startTime;
        
        if (!isset($startTime)) {
            if (function_exists('microtime')) {
                list($usec, $sec) = explode(' ', microtime()); 
                $startTime = ((float)$usec + (float)$sec);
            } else {
                $startTime = time();
            }
        }
        return $startTime; 
    }
    
    /**
     * @return float
     */
    function getTimeStamp()
    {
        return $this->timeStamp;
    }
    
    /**
     * @return mixed
     */
    function getThreadName()
    {
        if ($this->threadName === null)
            $this->threadName = (string)getmypid();
        return $this->threadName;
    }

    /**
     * @return mixed null
     */
    function getThrowableInformation()
    {
        return null;
    }
    
    /**
     * Serialize this object
     * @return string
     */
    function toString()
    {
        serialize($this);
    }
    
    /**
     * Avoid serialization of the {@link $logger} object
     */
    function __sleep()
    {
        return array(
            'fqcn','categoryName',
            'level',
            'ndc','ndcLookupRequired',
            'message','renderedMessage',
            'threadName',
            'timestamp',
            'locationInfo'
        );
    }

}

LoggerLoggingEvent::getStartTime();

?>