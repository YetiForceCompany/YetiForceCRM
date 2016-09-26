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
    public $sp = false;
    
    /**
     * Target host. On how to define remote hostaname see 
     * {@link PHP_MANUAL#fsockopen}
     * @var string 
     */
    public $remoteHost     = '';
    
    /**
     * @var integer the network port.
     */
    public $port           = LOG4PHP_LOGGER_APPENDER_SOCKET_DEFAULT_PORT;
    
    /**
     * @var boolean get event's location info.
     */
    public $locationInfo   = false;
    
    /**
     * @var integer connection timeout
     */
    public $timeout        = LOG4PHP_LOGGER_APPENDER_SOCKET_DEFAULT_TIMEOUT;
    
    /**
     * @var boolean output events via {@link LoggerXmlLayout}
     */
    public $useXml         = false;
    
    /**
     * @var boolean forward this option to {@link LoggerXmlLayout}. 
     *              Ignored if {@link $useXml} is <i>false</i>.
     */
    public $log4jNamespace = false;

    /**
     * @var LoggerXmlLayout
     * @access private
     */
    public $xmlLayout      = null;
    
    /**
     * @var boolean
     * @access private
     */
    public $requiresLayout = false;
    
    /**
     * Constructor
     *
     * @param string $name appender name
     */
    public function LoggerAppenderSocket($name)
    {
        $this->LoggerAppenderSkeleton($name);
    }

    /**
     * Create a socket connection using defined parameters
     */
    public function activateOptions()
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
    
    public function close()
    {
        @fclose($this->sp);
        $this->closed = true;
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->getRemoteHost();
    }
    
    /**
     * @return boolean
     */
    public function getLocationInfo()
    {
        return $this->locationInfo;
    } 
     
    /**
     * @return boolean
     */
    public function getLog4jNamespace()
    {
        return $this->log4jNamespace;
    }

    /**
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }
    
    public function getRemoteHost()
    {
        return $this->remoteHost;
    }
    
    /**
     * @return integer
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
    
    /**
     * @var boolean
     */
    public function getUseXml()
    {
        return $this->useXml;
    } 
     
    public function reset()
    {
        $this->close();
        parent::reset();
    }

    /**
     * @param string
     * @deprecated Please, use {@link setRemoteHost}
     */
    public function setHostname($hostname)
    {
        $this->setRemoteHost($hostname);
    }
    
    /**
     * @param mixed
     */
    public function setLocationInfo($flag)
    {
        $this->locationInfo = LoggerOptionConverter::toBoolean($flag, $this->getLocationInfo());
    } 

    /**
     * @param mixed
     */
    public function setLog4jNamespace($flag)
    {
        $this->log4jNamespace = LoggerOptionConverter::toBoolean($flag, $this->getLog4jNamespace());
    } 
            
    /**
     * @param integer
     */
    public function setPort($port)
    {
        $port = LoggerOptionConverter::toInt($port, 0);
        if ($port > 0 && $port < 65535)
            $this->port = $port;    
    }
    
    /**
     * @param string
     */
    public function setRemoteHost($hostname)
    {
        $this->remoteHost = $hostname;
    }
    
    /**
     * @param integer
     */
    public function setTimeout($timeout)
    {
        $this->timeout = LoggerOptionConverter::toInt($timeout, $this->getTimeout());
    }
    
    /**
     * @param mixed
     */
    public function setUseXml($flag)
    {
        $this->useXml = LoggerOptionConverter::toBoolean($flag, $this->getUseXml());
    } 
 
    /**
     * @param LoggerLoggingEvent
     */
    public function append($event)
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