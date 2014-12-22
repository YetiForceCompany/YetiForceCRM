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
 * Log events using php {@link PHP_MANUAL#syslog} function.
 *
 * Levels are mapped as follows:
 * - <b>level &gt;= FATAL</b> to LOG_ALERT
 * - <b>FATAL &gt; level &gt;= ERROR</b> to LOG_ERR 
 * - <b>ERROR &gt; level &gt;= WARN</b> to LOG_WARNING
 * - <b>WARN  &gt; level &gt;= INFO</b> to LOG_INFO
 * - <b>INFO  &gt; level &gt;= DEBUG</b> to LOG_DEBUG
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.11 $
 * @package log4php
 * @subpackage appenders
 */ 
class LoggerAppenderSyslog extends LoggerAppenderSkeleton {
    
    /**
     * Constructor
     *
     * @param string $name appender name
     */
    function LoggerAppenderSyslog($name)
    {
        $this->LoggerAppenderSkeleton($name);
    }

    function activateOptions()
    {
        define_syslog_variables();
        $this->closed = false;
    }

    function close() 
    {
        closelog();
        $this->closed = true;
    }

    function append($event)
    {
        $level   = $event->getLevel();
        $message = $event->getRenderedMessage();
        if ($level->isGreaterOrEqual(LoggerLevel::getLevelFatal())) {
            syslog(LOG_ALERT, $message);
        } elseif ($level->isGreaterOrEqual(LoggerLevel::getLevelError())) {
            syslog(LOG_ERR, $message);        
        } elseif ($level->isGreaterOrEqual(LoggerLevel::getLevelWarn())) {
            syslog(LOG_WARNING, $message);
        } elseif ($level->isGreaterOrEqual(LoggerLevel::getLevelInfo())) {
            syslog(LOG_INFO, $message);
        } elseif ($level->isGreaterOrEqual(LoggerLevel::getLevelDebug())) {
            syslog(LOG_DEBUG, $message);
        }
    }
}
?>