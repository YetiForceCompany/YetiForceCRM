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
 * @subpackage layouts
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');

if (!defined('LOG4PHP_LINE_SEP')) {
    if (substr(php_uname(), 0, 7) == "Windows") { 
        define('LOG4PHP_LINE_SEP', "\r\n");
    } else {
        /**
         * @ignore
         */
        define('LOG4PHP_LINE_SEP', "\n");
    }
}

 
/**
 */
require_once(LOG4PHP_DIR . '/LoggerLayout.php');

/**
 * A simple layout.
 *
 * Returns the log statement in a format consisting of the
 * <b>level</b>, followed by " - " and then the <b>message</b>. 
 * For example, 
 * <samp> INFO - "A message" </samp>
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.8 $
 * @package log4php
 * @subpackage layouts
 */  
class LoggerLayoutSimple extends LoggerLayout {
    
    /**
     * Constructor
     */
    function LoggerLayoutSimple()
    {
        return;
    }

    function activateOptions() 
    {
        return;
    }

    /**
     * Returns the log statement in a format consisting of the
     * <b>level</b>, followed by " - " and then the
     * <b>message</b>. For example, 
     * <samp> INFO - "A message" </samp>
     *
     * @param LoggerLoggingEvent $event
     * @return string
     */
    function format($event)
    {
        $level = $event->getLevel();
        return $level->toString() . ' - ' . $event->getRenderedMessage(). LOG4PHP_LINE_SEP;
    }
}
?>