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

require_once(LOG4PHP_DIR . '/spi/LoggerFactory.php');
require_once(LOG4PHP_DIR . '/Logger.php');

/**
 * Creates instances of {@link Logger} with a given name.
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.2 $
 * @package log4php
 * @since 0.5 
 */
class LoggerDefaultCategoryFactory extends LoggerFactory {
    
    function LoggerDefaultCategoryFactory()
    {
        return;
    }    
    
    /**
     * @param string $name
     * @return Logger
     */
    function makeNewLoggerInstance($name)
    {
        return new Logger($name);
    }
}

?>