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
 
/**
 */
require_once(LOG4PHP_DIR . '/Logger.php');
require_once(LOG4PHP_DIR . '/LoggerLevel.php');

/**
 * The root logger.
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.9 $
 * @package log4php
 * @see Logger
 */
class LoggerRoot extends Logger {

    /**
     * @var string name of logger 
     */
    var $name   = 'root';

    /**
     * @var object must be null for LoggerRoot
     */
    var $parent = null;
    

    /**
     * Constructor
     *
     * @param integer $level initial log level
     */
    function LoggerRoot($level = null)
    {
        $this->Logger($this->name);
        if ($level == null)
            $level = LoggerLevel::getLevelAll();
        $this->setLevel($level);
    } 
    
    /**
     * @return integer the level
     */
    function getChainedLevel()
    {
        return $this->level;
    } 
    
    /**
     * Setting a null value to the level of the root category may have catastrophic results.
     * @param LoggerLevel $level
     */
    function setLevel($level)
    {
        $this->level = $level;
    }    
 
    /**
     * Please use setLevel() instead.
     * @param LoggerLevel $level
     * @deprecated
     */
    function setPriority($level)
    {
        $this->setLevel($level); 
    }
    
    /**
     * Always returns false.
     * Because LoggerRoot has no parents, it returns false.
     * @param Logger $parent
     * @return boolean
     */
    function setParent($parent)
    {
        return false;
    }  
}
?>