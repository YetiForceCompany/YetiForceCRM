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
 * @subpackage helpers
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');
 
/**
 */
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * This class encapsulates the information obtained when parsing
 * formatting modifiers in conversion modifiers.
 * 
 * @author VxR <vxr@vxr.it>
 * @package log4php
 * @subpackage spi
 * @since 0.3
 */
class LoggerFormattingInfo {

    var $min        = -1;
    var $max        = 0x7FFFFFFF;
    var $leftAlign  = false;

    /**
     * Constructor
     */
    function LoggerFormattingInfo() {}
    
    function reset()
    {
        $this->min          = -1;
        $this->max          = 0x7FFFFFFF;
        $this->leftAlign    = false;      
    }

    function dump()
    {
        LoggerLog::debug("LoggerFormattingInfo::dump() min={$this->min}, max={$this->max}, leftAlign={$this->leftAlign}");
    }
} 
?>