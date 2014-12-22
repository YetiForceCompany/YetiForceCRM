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
 
require_once(LOG4PHP_DIR . '/appenders/LoggerAppenderFile.php');

/**
 * LoggerAppenderDailyFile appends log events to a file ne.
 *
 * A formatted version of the date pattern is used as to create the file name
 * using the {@link PHP_MANUAL#sprintf} function.
 * <p>Parameters are {@link $datePattern}, {@link $file}. Note that file 
 * parameter should include a '%s' identifier and should always be set 
 * before {@link $file} param.</p>
 *
 * @author Abel Gonzalez <agonzalez@lpsz.org>
 * @version $Revision: 1.7 $
 * @package log4php
 * @subpackage appenders
 */                      
class LoggerAppenderDailyFile extends LoggerAppenderFile {

    /**
     * Format date. 
     * It follows the {@link PHP_MANUAL#date()} formatting rules and <b>should always be set before {@link $file} param</b>.
     * @var string
     */
    var $datePattern = "Ymd";
    
    /**
    * Constructor
    *
    * @param string $name appender name
    */
    function LoggerAppenderDailyFile($name)
    {
        $this->LoggerAppenderFile($name); 
    }
    
    /**
    * Sets date format for the file name.
    * @param string $format a regular date() string format
    */
    function setDatePattern ( $format )
    {
        $this->datePattern = $format;
    }
    
    /**
    * @return string returns date format for the filename
    */
    function getDatePattern ( )
    {
        return $this->datePattern;
    }
    
    /**
    * The File property takes a string value which should be the name of the file to append to.
    * Sets and opens the file where the log output will go.
    *
    * @see LoggerAppenderFile::setFile()
    */
    function setFile()
    {
        $numargs = func_num_args();
        $args    = func_get_args();
        
        if ($numargs == 1 and is_string($args[0])) {
            parent::setFile( sprintf((string)$args[0], date($this->getDatePattern())) );
        } elseif ($numargs == 2 and is_string($args[0]) and is_bool($args[1])) {
            parent::setFile( sprintf((string)$args[0], date($this->getDatePattern())), $args[1] );
        }
    } 

}

?>