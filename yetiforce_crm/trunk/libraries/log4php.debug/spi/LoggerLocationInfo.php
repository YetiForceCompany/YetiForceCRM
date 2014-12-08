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
 * @subpackage spi
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');

/**
 * When location information is not available the constant
 * <i>NA</i> is returned. Current value of this string
 * constant is <b>?</b>.  
 */
define('LOG4PHP_LOGGER_LOCATION_INFO_NA',  'NA');

/**
 * The internal representation of caller location information.
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.5 $
 * @package log4php
 * @subpackage spi
 * @since 0.3
 */
class LoggerLocationInfo {

    /**
    * @var string Caller's line number.
    */
    var $lineNumber = null;
    
    /**
    * @var string Caller's file name.
    */
    var $fileName = null;
    
    /**
    * @var string Caller's fully qualified class name.
    */
    var $className = null;
    
    /**
    * @var string Caller's method name.
    */
    var $methodName = null;
    
    /**
    * @var string 
    */
    var $fullInfo = null;

    /**
     * Instantiate location information based on a {@link PHP_MANUAL#debug_backtrace}.
     *
     * @param array $trace
     * @param mixed $caller
     */
    function LoggerLocationInfo($trace, $fqcn = null)
    {
        $this->lineNumber   = isset($trace['line']) ? $trace['line'] : null;
        $this->fileName     = isset($trace['file']) ? $trace['file'] : null;
        $this->className    = isset($trace['class']) ? $trace['class'] : null;
        $this->methodName   = isset($trace['function']) ? $trace['function'] : null;
        
        $this->fullInfo = $this->getClassName() . '.' . $this->getMethodName() . 
                          '(' . $this->getFileName() . ':' . $this->getLineNumber() . ')';
    }

    function getClassName()
    {
        return ($this->className === null) ? LOG4PHP_LOGGER_LOCATION_INFO_NA : $this->className; 
    }

    /**
     *  Return the file name of the caller.
     *  <p>This information is not always available.
     */
    function getFileName()
    {
        return ($this->fileName === null) ? LOG4PHP_LOGGER_LOCATION_INFO_NA : $this->fileName; 
    }

    /**
     *  Returns the line number of the caller.
     *  <p>This information is not always available.
     */
    function getLineNumber()
    {
        return ($this->lineNumber === null) ? LOG4PHP_LOGGER_LOCATION_INFO_NA : $this->lineNumber; 
    }

    /**
     *  Returns the method name of the caller.
     */
    function getMethodName()
    {
        return ($this->methodName === null) ? LOG4PHP_LOGGER_LOCATION_INFO_NA : $this->methodName; 
    }
}
?>