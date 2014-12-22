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
 
require_once(LOG4PHP_DIR . '/LoggerAppenderSkeleton.php');
require_once(LOG4PHP_DIR . '/LoggerLevel.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * Log events using php {@link PHP_MANUAL#trigger_error} function and a {@link LoggerLayoutTTCC} default layout.
 *
 * <p>Levels are mapped as follows:</p>
 * - <b>level &lt; WARN</b> mapped to E_USER_NOTICE
 * - <b>WARN &lt;= level &lt; ERROR</b> mapped to E_USER_WARNING
 * - <b>level &gt;= ERROR</b> mapped to E_USER_ERROR  
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.11 $
 * @package log4php
 * @subpackage appenders
 */ 
class LoggerAppenderPhp extends LoggerAppenderSkeleton {

    /**
     * @access private
     */
    var $requiresLayout = false;
    
    /**
     * Constructor
     *
     * @param string $name appender name
     */
    function LoggerAppenderPhp($name)
    {
        $this->LoggerAppenderSkeleton($name);
    }

    function activateOptions()
    {
        $this->layout = LoggerLayout::factory('LoggerLayoutTTCC');
        $this->closed = false;
    }

    function close() 
    {
        $this->closed = true;
    }

    function append($event)
    {
        if ($this->layout !== null) {
            LoggerLog::debug("LoggerAppenderPhp::append()");
            $level = $event->getLevel();
            if ($level->isGreaterOrEqual(LoggerLevel::getLevelError())) {
                trigger_error($this->layout->format($event), E_USER_ERROR);
            } elseif ($level->isGreaterOrEqual(LoggerLevel::getLevelWarn())) {
                trigger_error($this->layout->format($event), E_USER_WARNING);
            } else {
                trigger_error($this->layout->format($event), E_USER_NOTICE);
            }
        }
    }
}
?>