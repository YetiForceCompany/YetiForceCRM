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
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__));

require_once(LOG4PHP_DIR . '/spi/LoggerConfigurator.php');
require_once(LOG4PHP_DIR . '/LoggerLayout.php');
require_once(LOG4PHP_DIR . '/LoggerAppender.php');
require_once(LOG4PHP_DIR . '/LoggerManager.php');

/**
 * Use this class to quickly configure the package.
 *
 * <p>For file based configuration see {@link LoggerPropertyConfigurator}. 
 * <p>For XML based configuration see {@link LoggerDOMConfigurator}.
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.2 $
 * @package log4php
 * @since 0.5
 */
class LoggerBasicConfigurator extends LoggerConfigurator {

    function LoggerBasicConfigurator() 
    {
        return;
    }

    /**
     * Add a {@link LoggerAppenderConsole} that uses 
     * the {@link LoggerLayoutTTCC} to the root category.
     * 
     * @param string $url not used here
     * @static  
     */
    function configure($url = null)
    {
        $root =& LoggerManager::getRootLogger();
        
        $appender =& LoggerAppender::singleton('A1', 'LoggerAppenderConsole');
        $layout = LoggerLayout::factory('LoggerLayoutTTCC');
        $appender->setLayout($layout);

        $root->addAppender($appender);
    }

    /**
     * Reset the default hierarchy to its defaut. 
     * It is equivalent to
     * <code>
     * LoggerManager::resetConfiguration();
     * </code>
     *
     * @see LoggerHierarchy::resetConfiguration()
     * @static
     */
    function resetConfiguration()
    {
        LoggerManager::resetConfiguration();
    }
}
?>
