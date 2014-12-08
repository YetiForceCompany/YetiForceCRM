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
require_once(LOG4PHP_DIR . '/helpers/LoggerOptionConverter.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * FileAppender appends log events to a file.
 *
 * Parameters are ({@link $fileName} but option name is <b>file</b>), 
 * {@link $append}.
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.15 $
 * @package log4php
 * @subpackage appenders
 */
class LoggerAppenderFile extends LoggerAppenderSkeleton {

    /**
     * @var boolean if {@link $file} exists, appends events.
     */
    var $append = true;  

    /**
     * @var string the file name used to append events
     */
    var $fileName;

    /**
     * @var mixed file resource
     * @access private
     */
    var $fp = false;
    
    /**
     * @access private
     */
    var $requiresLayout = true;
    
    /**
     * Constructor.
     *
     * @param string $name appender name
     */
    function LoggerAppenderFile($name)
    {
        $this->LoggerAppenderSkeleton($name);
    }

    function activateOptions()
    {
        $fileName = $this->getFile();
        LoggerLog::debug("LoggerAppenderFile::activateOptions() opening file '{$fileName}'");
        $this->fp = @fopen($fileName, ($this->getAppend()? 'a':'w'));

	// Denying read option for log file. Added for Vulnerability fix
	if (is_readable($fileName)) chmod ($fileName,0222);

        if ($this->fp) {
            if ($this->getAppend())
                fseek($this->fp, 0, SEEK_END);
            @fwrite($this->fp, $this->layout->getHeader());
            $this->closed = false;
        } else {
            $this->closed = true;
        }
    }
    
    function close()
    {
        if ($this->fp and $this->layout !== null)
            @fwrite($this->fp, $this->layout->getFooter());
            
        $this->closeFile();
        $this->closed = true;
    }

    /**
     * Closes the previously opened file.
     */
    function closeFile() 
    {
        if ($this->fp)
            @fclose($this->fp);
    }
    
    /**
     * @return boolean
     */
    function getAppend()
    {
        return $this->append;
    }

    /**
     * @return string
     */
    function getFile()
    {
        return $this->getFileName();
    }
    
    /**
     * @return string
     */
    function getFileName()
    {
        return $this->fileName;
    } 
 
    /**
     * Close any previously opened file and call the parent's reset.
     */
    function reset()
    {
        $this->closeFile();
        $this->fileName = null;
        parent::reset();
    }

    function setAppend($flag)
    {
        $this->append = LoggerOptionConverter::toBoolean($flag, true);        
    } 
  
    /**
     * Sets and opens the file where the log output will go.
     *
     * This is an overloaded method. It can be called with:
     * - setFile(string $fileName) to set filename.
     * - setFile(string $fileName, boolean $append) to set filename and append.
     */
    function setFile()
    {
        $numargs = func_num_args();
        $args    = func_get_args();

        if ($numargs == 1 and is_string($args[0])) {
            $this->setFileName($args[0]);
        } elseif ($numargs >=2 and is_string($args[0]) and is_bool($args[1])) {
            $this->setFile($args[0]);
            $this->setAppend($args[1]);
        }
    }
    
    function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    function append($event)
    {
        if ($this->fp and $this->layout !== null) {

            LoggerLog::debug("LoggerAppenderFile::append()");
        
            @fwrite($this->fp, $this->layout->format($event));
        } 
    }
}
?>
