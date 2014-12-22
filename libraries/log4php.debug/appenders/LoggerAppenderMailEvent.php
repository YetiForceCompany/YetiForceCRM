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

/**
 */
require_once(LOG4PHP_DIR . '/LoggerAppenderSkeleton.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * Log events to an email address. It will be created an email for each event. 
 *
 * <p>Parameters are 
 * {@link $smtpHost} (optional), 
 * {@link $port} (optional), 
 * {@link $from} (optional), 
 * {@link $to}, 
 * {@link $subject} (optional).</p>
 * <p>A layout is required.</p>
 *
 * @author Domenico Lordi <lordi@interfree.it>
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.10 $
 * @package log4php
 * @subpackage appenders
 */
class LoggerAppenderMailEvent extends LoggerAppenderSkeleton {

    /**
     * @var string 'from' field
     */
    var $from           = null;

    /**
     * @var integer 'from' field
     */
    var $port           = 25;

    /**
     * @var string hostname. 
     */
    var $smtpHost       = null;

    /**
     * @var string 'subject' field
     */
    var $subject        = '';

    /**
     * @var string 'to' field
     */
    var $to             = null;
    
    /**
     * @access private
     */
    var $requiresLayout = true;

    /**
     * Constructor.
     *
     * @param string $name appender name
     */
    function LoggerAppenderMailEvent($name)
    {
        $this->LoggerAppenderSkeleton($name);
    }

    function activateOptions()
    { 
        $this->closed = false;
    }
    
    function close()
    {
        $this->closed = true;
    }

    /**
     * @return string
     */
    function getFrom()      { return $this->from; }
    
    /**
     * @return integer
     */
    function getPort()      { return $this->port; }
    
    /**
     * @return string
     */
    function getSmtpHost()  { return $this->smtpHost; }
    
    /**
     * @return string
     */
    function getSubject()   { return $this->subject; }

    /**
     * @return string
     */
    function getTo()        { return $this->to; }

    function setFrom($from)             { $this->from = $from; }
    function setPort($port)             { $this->port = (int)$port; }
    function setSmtpHost($smtphost)     { $this->smtpHost = $smtpHost; }
    function setSubject($subject)       { $this->subject = $subject; }
    function setTo($to)                 { $this->to = $to; }

    function append($event)
    {
        $from = $this->getFrom();
        $to   = $this->getTo();
        if (empty($from) or empty($to))
            return;
    
        $smtpHost = $this->getSmtpHost();
        $prevSmtpHost = ini_get('SMTP');
        if (!empty($smtpHost)) {
            ini_set('SMTP', $smtpHost);
        } else {
            $smtpHost = $prevSmtpHost;
        } 

        $smtpPort = $this->getPort();
        $prevSmtpPort= ini_get('smtp_port');        
        if ($smtpPort > 0 and $smtpPort < 65535) {
            ini_set('smtp_port', $smtpPort);
        } else {
            $smtpPort = $prevSmtpPort;
        } 
        
        LoggerLog::debug(
            "LoggerAppenderMailEvent::append()" . 
            ":from=[{$from}]:to=[{$to}]:smtpHost=[{$smtpHost}]:smtpPort=[{$smtpPort}]"
        ); 
        
        if (!@mail( $to, $this->getSubject(), 
            $this->layout->getHeader() . $this->layout->format($event) . $this->layout->getFooter($event), 
            "From: {$from}\r\n"
        )) {
            LoggerLog::debug("LoggerAppenderMailEvent::append() mail error");
        }
            
        ini_set('SMTP',         $prevSmtpHost);
        ini_set('smtp_port',    $prevSmtpPort);
    }
}

?>