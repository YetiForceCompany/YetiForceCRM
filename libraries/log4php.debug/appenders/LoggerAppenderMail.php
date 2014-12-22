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
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * Appends log events to mail using php function {@link PHP_MANUAL#mail}.
 *
 * <p>Parameters are {@link $from}, {@link $to}, {@link $subject}.</p>
 * <p>This appender requires a layout.</p>
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.8 $
 * @package log4php
 * @subpackage appenders
 */
class LoggerAppenderMail extends LoggerAppenderSkeleton {

    /**
     * @var string 'from' field
     */
    var $from = null;

    /**
     * @var string 'subject' field
     */
    var $subject = 'Log4php Report';
    
    /**
     * @var string 'to' field
     */
    var $to = null;

    /**
     * @var string used to create mail body
     * @access private
     */
    var $body = '';
    
    /**
     * @access private
     */
    var $requiresLayout = true;
    
    /**
     * Constructor.
     *
     * @param string $name appender name
     */
    function LoggerAppenderMail($name)
    {
        $this->LoggerAppenderSkeleton($name);
    }

    function activateOptions()
    {
        $this->closed = false;
        return;
    }
    
    function close()
    {
        $from       = $this->getFrom();
        $to         = $this->getTo();

        if (!empty($this->body) and $from !== null and $to !== null and $this->layout !== null) {

            $subject    = $this->getSubject();            

            LoggerLog::debug("LoggerAppenderMail::close() sending mail from=[{$from}] to=[{$to}] subject=[{$subject}]");
            
            @mail(
                $to, $subject, 
                $this->layout->getHeader() . $this->body . $this->layout->getFooter(),
                "From: {$from}\r\n"
            );
        }
        $this->closed = true;
    }
    
    /**
     * @return string
     */
    function getFrom()
    {
        return $this->from;
    }
    
    /**
     * @return string
     */
    function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    function getTo()
    {
        return $this->to;
    }
    
    function setSubject($subject)
    {
        $this->subject = $subject;
    }
    
    function setTo($to)
    {
        $this->to = $to;
    }

    function setFrom($from)
    {
        $this->from = $from;
    }  

    function append($event)
    {
        if ($this->layout !== null)
            $this->body .= $this->layout->format($event);
    }
}
?>