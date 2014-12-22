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
 
/**
 */
require_once(LOG4PHP_DIR . '/LoggerAppenderSkeleton.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * LoggerAppenderEcho uses {@link PHP_MANUAL#echo echo} function to output events. 
 * 
 * <p>This appender requires a layout.</p>  
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.5 $
 * @package log4php
 * @subpackage appenders
 */
class LoggerAppenderEcho extends LoggerAppenderSkeleton {

    /**
     * @access private 
     */
    var $requiresLayout = true;

    /**
     * @var boolean used internally to mark first append 
     * @access private 
     */
    var $firstAppend    = true;
    
    /**
     * Constructor.
     *
     * @param string $name appender name
     */
    function LoggerAppenderEcho($name)
    {
        $this->LoggerAppenderSkeleton($name);
    }

    function activateOptions()
    {
        return;
    }
    
    function close()
    {
        if (!$this->firstAppend)
            echo $this->layout->getFooter();
        $this->closed = true;    
    }

    function append($event)
    {
        LoggerLog::debug("LoggerAppenderEcho::append()");
        
        if ($this->layout !== null) {
            if ($this->firstAppend) {
                echo $this->layout->getHeader();
                $this->firstAppend = false;
            }
            echo $this->layout->format($event);
        } 
    }
}

?>