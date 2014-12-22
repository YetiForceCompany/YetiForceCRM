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
 
require_once(LOG4PHP_DIR . '/LoggerCategory.php');
require_once(LOG4PHP_DIR . '/LoggerManager.php');

/**
 * Main class for logging operations  
 *
 * @author       VxR <vxr@vxr.it>
 * @version      $Revision: 1.9 $
 * @package log4php
 */
class Logger extends LoggerCategory {

    /**
     * Constructor
     * @param string $name logger name 
     */    
    function Logger($name)
    {
        $this->LoggerCategory($name);
    }
    
    /**
     * Get a Logger by name (Delegate to {@link LoggerManager})
     * @param string $name logger name
     * @param LoggerFactory $factory a {@link LoggerFactory} instance or null
     * @return Logger
     * @static 
     */    
    function &getLogger($name, $factory = null)
    {
        return LoggerManager::getLogger($name, $factory);
    }
    
    /**
     * get the Root Logger (Delegate to {@link LoggerManager})
     * @return LoggerRoot
     * @static 
     */    
    function &getRootLogger()
    {
        return LoggerManager::getRootLogger();    
    }
}
?>