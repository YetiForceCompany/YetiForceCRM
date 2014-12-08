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
 * @subpackage appenders
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');

define('LOG4PHP_LOGGER_APPENDER_SOCKET_DEFAULT_PORT',       4446);
define('LOG4PHP_LOGGER_APPENDER_SOCKET_DEFAULT_TIMEOUT',    30);

require_once(LOG4PHP_DIR . '/LoggerAppenderSkeleton.php');
require_once(LOG4PHP_DIR . '/helpers/LoggerOptionConverter.php');
require_once(LOG4PHP_DIR . '/LoggerLayout.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * Serialize events and send them to a network socket.
 *
 * Parameters are {@link $remoteHost}, {@link $port}, {@link $timeout}, 
 * {@link $locationInfo}, {@link $useXml} and {@link $log4jNamespace}.
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.17 $
 * @package log4php
 * @subpackage appenders
 */ 
class LoggerAppenderSocket extends LoggerAppenderSkeleton {

    /**
     * @var mixed socket connection resource
     * @access private
     */
    var $sp = false;
    
    /**
     * Target host. On how to define remote hostaname see 
     * {@link PHP_MANUAL#fsockopen}
     * @var string 
     */
    var $remoteHost     = '';
    
    /**
     * @var integer the network port.
     */
    var $port           = LOG4PHP_LOGGER_APPENDER_SOCKET_DEFAULT_PORT;
    
    /**
     * @var boolean get event's location info.
     */
    var $locationInfo   = false;
    
    /**
     * @var integer connection timeout
     */
    var $timeout        = LOG4PHP_LOGGER_APPENDER_SOCKET_DEFAULT_TIMEOUT;
    
    /**
     * @var boolean output events via {@link LoggerXmlLayout}
     */
    var $useXml         = false;
    
    /**
     * @var boolean forward this option to {@link LoggerXmlLayout}. 
     *              Ignored if {@link $useXml} is <i>false</i>.
     */
    var $log4jNamespace = false;

    /**
     * @var LoggerXmlLayout
     * @access private
     */
    var $xmlLayout      = null;
    
    /**
     * @var boolean
     * @access private
     */
    var $requiresLayout = false;
    
    /**
     * Constructor
     *
     * @param string $name appender name
     */
    function LoggerAppenderSocket($name)
    {
        $this->LoggerAppenderSkeleton($name);
    }

    /**
     * Create a socket connection using defined parameters
     */
    function activateOptions()
    {
        LoggerLog::debug("LoggerAppenderSocket::activateOptions() creating a socket...");        
        $errno = 0;
        $errstr = '';
        $this->sp = @fsockopen($this->getRemoteHost(), $this->getPort(), $errno, $errstr, $this->getTimeout());
        if ($errno) {
            LoggerLog::debug("LoggerAppenderSocket::activateOptions() socket error [$errno] $errstr");
            $this->closed = true;
        } else {
            LoggerLog::debug("LoggerAppenderSocket::activateOptions() socket created [".$this->sp."]");
            if ($this->getUseXml()) {
                $this->xmlLayout = LoggerLayout::factory('LoggerXmlLayout');
                if ($this->xmlLayout === null) {
                    LoggerLog::debug("LoggerAppenderSocket::activateOptions() useXml is true but layout is null");
                    $this->setUseXml(false);
                } else {
                    $this->xmlLayout->setLocationInfo($this->getLocationInfo());
                    $this->xmlLayout->setLog4jNamespace($this->getLog4jNamespace());
                    $this->xmlLayout->activateOptions();
                }            
            }
            $this->closed = false;
        }
    }
    
    function close()
    {
        @fclose($this->sp);
        $this->closed = true;
    }

    /**
     * @return string
     */
    function getHostname()
    {
        return $this->getRemoteHost();
    }
    
    /**
     * @return boolean
     */
    function getLocationInfo()
    {
        return $this->locationInfo;
    } 
     
    /**
     * @return boolean
     */
    function getLog4jNamespace()
    {
        return $this->log4jNamespace;
    }

    /**
     * @return integer
     */
    function getPort()
    {
        return $this->port;
    }
    
    function getRemoteHost()
    {
        return $this->remoteHost;
    }
    
    /**
     * @return integer
     */
    function getTimeout()
    {
        return $this->timeout;
    }
    
    /**
     * @var boolean
     */
    function getUseXml()
    {
        return $this->useXml;
    } 
     
    function reset()
    {
        $this->close();
        parent::reset();
    }

    /**
     * @param string
     * @deprecated Please, use {@link setRemoteHost}
     */
    function setHostname($hostname)
    {
        $this->setRemoteHost($hostname);
    }
    
    /**
     * @param mixed
     */
    function setLocationInfo($flag)
    {
        $this->locationInfo = LoggerOptionConverter::toBoolean($flag, $this->getLocationInfo());
    } 

    /**
     * @param mixed
     */
    function setLog4jNamespace($flag)
    {
        $this->log4jNamespace = LoggerOptionConverter::toBoolean($flag, $this->getLog4jNamespace());
    } 
            
    /**
     * @param integer
     */
    function setPort($port)
    {
        $port = LoggerOptionConverter::toInt($port, 0);
        if ($port > 0 and $port < 65535)
            $this->port = $port;    
    }
    
    /**
     * @param string
     */
    function setRemoteHost($hostname)
    {
        $this->remoteHost = $hostname;
    }
    
    /**
     * @param integer
     */
    function setTimeout($timeout)
    {
        $this->timeout = LoggerOptionConverter::toInt($timeout, $this->getTimeout());
    }
    
    /**
     * @param mixed
     */
    function setUseXml($flag)
    {
        $this->useXml = LoggerOptionConverter::toBoolean($flag, $this->getUseXml());
    } 
 
    /**
     * @param LoggerLoggingEvent
     */
    function append($event)
    {
        if ($this->sp) {
        
            LoggerLog::debug("LoggerAppenderSocket::append()");
            
            if ($this->getLocationInfo())
                $event->getLocationInfo();
        
            if (!$this->getUseXml()) {
                $sEvent = serialize($event);
                @fwrite($this->sp, $sEvent, strlen($sEvent));
            } else {
                @fwrite($this->sp, $this->xmlLayout->format($event));
            }            

            // not sure about it...
            @fflush ($this->sp);
        } 
    }
}

?>