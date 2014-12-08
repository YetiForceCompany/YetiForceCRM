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
 * @subpackage layouts
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');

define('LOG4PHP_LOGGER_XML_LAYOUT_LOG4J_NS_PREFIX',     'log4j');
define('LOG4PHP_LOGGER_XML_LAYOUT_LOG4J_NS',            'http://jakarta.apache.org/log4j/');

define('LOG4PHP_LOGGER_XML_LAYOUT_LOG4PHP_NS_PREFIX',   'log4php');
define('LOG4PHP_LOGGER_XML_LAYOUT_LOG4PHP_NS',          'http://www.vxr.it/log4php/');

/**
 */
require_once(LOG4PHP_DIR . '/helpers/LoggerOptionConverter.php');
require_once(LOG4PHP_DIR . '/helpers/LoggerTransform.php');
require_once(LOG4PHP_DIR . '/LoggerLayout.php');

/**
 * The output of the LoggerXmlLayout consists of a series of log4php:event elements. 
 * 
 * <p>Parameters: {@link $locationInfo}.</p>
 *
 * <p>It does not output a complete well-formed XML file. 
 * The output is designed to be included as an external entity in a separate file to form
 * a correct XML file.</p>
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.16 $
 * @package log4php
 * @subpackage layouts
 */
class LoggerXmlLayout extends LoggerLayout {

    /**
     * The <b>LocationInfo</b> option takes a boolean value. By default,
     * it is set to false which means there will be no location
     * information output by this layout. If the the option is set to
     * true, then the file name and line number of the statement at the
     * origin of the log statement will be output.
     * @var boolean
     */
    var $locationInfo = true;
  
    /**
     * @var boolean set the elements namespace
     */
    var $log4jNamespace = false;
    
    
    /**
     * @var string namespace
     * @private
     */
    var $_namespace = LOG4PHP_LOGGER_XML_LAYOUT_LOG4PHP_NS;
    
    /**
     * @var string namespace prefix
     * @private
     */
    var $_namespacePrefix = LOG4PHP_LOGGER_XML_LAYOUT_LOG4PHP_NS_PREFIX;
     
    /** 
     * No options to activate. 
     */
    function activateOptions()
    {
        if ($this->getLog4jNamespace()) {
            $this->_namespace        = LOG4PHP_LOGGER_XML_LAYOUT_LOG4J_NS;
            $this->_namespacePrefix  = LOG4PHP_LOGGER_XML_LAYOUT_LOG4J_NS_PREFIX;
        } else {
            $this->_namespace        = LOG4PHP_LOGGER_XML_LAYOUT_LOG4PHP_NS;
            $this->_namespacePrefix  = LOG4PHP_LOGGER_XML_LAYOUT_LOG4PHP_NS_PREFIX;
        }     
    }
    
    /**
     * @return string
     */
    function getHeader()
    {
        return "<{$this->_namespacePrefix}:eventSet ".
                    "xmlns:{$this->_namespacePrefix}=\"{$this->_namespace}\" ".
                    "version=\"0.3\" ".
                    "includesLocationInfo=\"".($this->getLocationInfo() ? "true" : "false")."\"".
               ">\r\n";
    }

    /**
     * Formats a {@link LoggerLoggingEvent} in conformance with the log4php.dtd.
     *
     * @param LoggerLoggingEvent $event
     * @return string
     */
    function format($event)
    {
        $loggerName = $event->getLoggerName();
        $timeStamp  = number_format((float)($event->getTimeStamp() * 1000), 0, '', '');
        $thread     = $event->getThreadName();
        $level      = $event->getLevel();
        $levelStr   = $level->toString();

        $buf = "<{$this->_namespacePrefix}:event logger=\"{$loggerName}\" level=\"{$levelStr}\" thread=\"{$thread}\" timestamp=\"{$timeStamp}\">\r\n";
        $buf .= "<{$this->_namespacePrefix}:message><![CDATA["; 
        LoggerTransform::appendEscapingCDATA($buf, $event->getRenderedMessage()); 
        $buf .= "]]></{$this->_namespacePrefix}:message>\r\n";        

        $ndc = $event->getNDC();
        if($ndc != null) {
            $buf .= "<{$this->_namespacePrefix}:NDC><![CDATA[";
            LoggerTransform::appendEscapingCDATA($buf, $ndc);
            $buf .= "]]></{$this->_namespacePrefix}:NDC>\r\n";       
        }

        if ($this->getLocationInfo()) {
            $locationInfo = $event->getLocationInformation();
            $buf .= "<{$this->_namespacePrefix}:locationInfo ". 
                    "class=\"" . $locationInfo->getClassName() . "\" ".
                    "file=\"" .  htmlentities($locationInfo->getFileName(), ENT_QUOTES) . "\" ".
                    "line=\"" .  $locationInfo->getLineNumber() . "\" ".
                    "method=\"" . $locationInfo->getMethodName() . "\" ";
            $buf .= "/>\r\n";

        }

        $buf .= "</{$this->_namespacePrefix}:event>\r\n\r\n";
        
        return $buf;

    }
    
    /**
     * @return string
     */
    function getFooter()
    {

        return "</{$this->_namespacePrefix}:eventSet>\r\n";
    }
    
    /**
     * @return boolean
     */
    function getLocationInfo()
    {
        return $this->locationInfo;
    }
  
    /**
     * @return boolean
     */
    function getLog4jNamespace()
    {
        return $this->log4jNamespace;
    }

    /**
     * The XMLLayout prints and does not ignore exceptions. Hence the
     * return value <b>false</b>.
     * @return boolean
     */
    function ignoresThrowable()
    {
        return false;
    }
    
    /**
     * The {@link $locationInfo} option takes a boolean value. By default,
     * it is set to false which means there will be no location
     * information output by this layout. If the the option is set to
     * true, then the file name and line number of the statement at the
     * origin of the log statement will be output.
     */
    function setLocationInfo($flag)
    {
        $this->locationInfo = LoggerOptionConverter::toBoolean($flag, true);
    }
  
    /**
     * @param boolean
     */
    function setLog4jNamespace($flag)
    {
        $this->log4jNamespace = LoggerOptionConverter::toBoolean($flag, true);
    }
}

?>