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

if (!defined('LOG4PHP_LINE_SEP')) {
    if (substr(php_uname(), 0, 7) == "Windows") { 
        /**
         * @ignore
         */
        define('LOG4PHP_LINE_SEP', "\r\n");
    } else {
        /**
         * @ignore
         */
        define('LOG4PHP_LINE_SEP', "\n");
    }
}
 
/**
 */
require_once(LOG4PHP_DIR . '/LoggerLayout.php');
require_once(LOG4PHP_DIR . '/spi/LoggerLoggingEvent.php');

/**
 * This layout outputs events in a HTML table.
 *
 * Parameters are: {@link $title}, {@link $locationInfo}.
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.14 $
 * @package log4php
 * @subpackage layouts
 */
class LoggerLayoutHtml extends LoggerLayout {

    /**
     * The <b>LocationInfo</b> option takes a boolean value. By
     * default, it is set to false which means there will be no location
     * information output by this layout. If the the option is set to
     * true, then the file name and line number of the statement
     * at the origin of the log statement will be output.
     *
     * <p>If you are embedding this layout within a {@link LoggerAppenderMail}
     * or a {@link LoggerAppenderMailEvent} then make sure to set the
     * <b>LocationInfo</b> option of that appender as well.
     * @var boolean
     */
    var $locationInfo = false;
    
    /**
     * The <b>Title</b> option takes a String value. This option sets the
     * document title of the generated HTML document.
     * Defaults to 'Log4php Log Messages'.
     * @var string
     */
    var $title = "Log4php Log Messages";
    
    /**
     * Constructor
     */
    function LoggerLayoutHtml()
    {
        return;
    }
    
    /**
     * The <b>LocationInfo</b> option takes a boolean value. By
     * default, it is set to false which means there will be no location
     * information output by this layout. If the the option is set to
     * true, then the file name and line number of the statement
     * at the origin of the log statement will be output.
     *
     * <p>If you are embedding this layout within a {@link LoggerAppenderMail}
     * or a {@link LoggerAppenderMailEvent} then make sure to set the
     * <b>LocationInfo</b> option of that appender as well.
     */
    function setLocationInfo($flag)
    {
        if (is_bool($flag)) {
            $this->locationInfo = $flag;
        } else {
            $this->locationInfo = (bool)(strtolower($flag) == 'true');
        }
    }

    /**
     * Returns the current value of the <b>LocationInfo</b> option.
     */
    function getLocationInfo()
    {
        return $this->locationInfo;
    }
    
    /**
     * The <b>Title</b> option takes a String value. This option sets the
     * document title of the generated HTML document.
     * Defaults to 'Log4php Log Messages'.
     */
    function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string Returns the current value of the <b>Title</b> option.
     */
    function getTitle()
    {
        return $this->title;
    }
    
    /**
     * @return string Returns the content type output by this layout, i.e "text/html".
     */
    function getContentType()
    {
        return "text/html";
    }
    
    /**
     * No options to activate.
     */
    function activateOptions()
    {
        return true;
    }
    
    /**
     * @param LoggerLoggingEvent $event
     * @return string
     */
    function format($event)
    {
        $sbuf = LOG4PHP_LINE_SEP . "<tr>" . LOG4PHP_LINE_SEP;
    
        $sbuf .= "<td>";
        
        $eventTime = (float)$event->getTimeStamp();
        $eventStartTime = (float)LoggerLoggingEvent::getStartTime();
        $sbuf .= number_format(($eventTime - $eventStartTime) * 1000, 0, '', '');
        $sbuf .= "</td>" . LOG4PHP_LINE_SEP;
    
        $sbuf .= "<td title=\"" . $event->getThreadName() . " thread\">";
        $sbuf .= $event->getThreadName();
        $sbuf .= "</td>" . LOG4PHP_LINE_SEP;
    
        $sbuf .= "<td title=\"Level\">";
        
        $level = $event->getLevel();
        
        if ($level->equals(LoggerLevel::getLevelDebug())) {
          $sbuf .= "<font color=\"#339933\">";
          $sbuf .= $level->toString();
          $sbuf .= "</font>";
        }elseif($level->equals(LoggerLevel::getLevelWarn())) {
          $sbuf .= "<font color=\"#993300\"><strong>";
          $sbuf .= $level->toString();
          $sbuf .= "</strong></font>";
        } else {
          $sbuf .= $level->toString();
        }
        $sbuf .= "</td>" . LOG4PHP_LINE_SEP;
    
        $sbuf .= "<td title=\"" . htmlentities($event->getLoggerName(), ENT_QUOTES) . " category\">";
        $sbuf .= htmlentities($event->getLoggerName(), ENT_QUOTES);
        $sbuf .= "</td>" . LOG4PHP_LINE_SEP;
    
        if ($this->locationInfo) {
            $locInfo = $event->getLocationInformation();
            $sbuf .= "<td>";
            $sbuf .= htmlentities($locInfo->getFileName(), ENT_QUOTES). ':' . $locInfo->getLineNumber();
            $sbuf .= "</td>" . LOG4PHP_LINE_SEP;
        }

        $sbuf .= "<td title=\"Message\">";
        $sbuf .= htmlentities($event->getRenderedMessage(), ENT_QUOTES);
        $sbuf .= "</td>" . LOG4PHP_LINE_SEP;

        $sbuf .= "</tr>" . LOG4PHP_LINE_SEP;
        
        if ($event->getNDC() != null) {
            $sbuf .= "<tr><td bgcolor=\"#EEEEEE\" style=\"font-size : xx-small;\" colspan=\"6\" title=\"Nested Diagnostic Context\">";
            $sbuf .= "NDC: " . htmlentities($event->getNDC(), ENT_QUOTES);
            $sbuf .= "</td></tr>" . LOG4PHP_LINE_SEP;
        }

        return $sbuf;
    }

    /**
     * @return string Returns appropriate HTML headers.
     */
    function getHeader()
    {
        $sbuf = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">" . LOG4PHP_LINE_SEP;
        $sbuf .= "<html>" . LOG4PHP_LINE_SEP;
        $sbuf .= "<head>" . LOG4PHP_LINE_SEP;
        $sbuf .= "<title>" . $this->title . "</title>" . LOG4PHP_LINE_SEP;
        $sbuf .= "<style type=\"text/css\">" . LOG4PHP_LINE_SEP;
        $sbuf .= "<!--" . LOG4PHP_LINE_SEP;
        $sbuf .= "body, table {font-family: arial,sans-serif; font-size: x-small;}" . LOG4PHP_LINE_SEP;
        $sbuf .= "th {background: #336699; color: #FFFFFF; text-align: left;}" . LOG4PHP_LINE_SEP;
        $sbuf .= "-->" . LOG4PHP_LINE_SEP;
        $sbuf .= "</style>" . LOG4PHP_LINE_SEP;
        $sbuf .= "</head>" . LOG4PHP_LINE_SEP;
        $sbuf .= "<body bgcolor=\"#FFFFFF\" topmargin=\"6\" leftmargin=\"6\">" . LOG4PHP_LINE_SEP;
        $sbuf .= "<hr size=\"1\" noshade>" . LOG4PHP_LINE_SEP;
        $sbuf .= "Log session start time " . strftime('%c', time()) . "<br>" . LOG4PHP_LINE_SEP;
        $sbuf .= "<br>" . LOG4PHP_LINE_SEP;
        $sbuf .= "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\" bordercolor=\"#224466\" width=\"100%\">" . LOG4PHP_LINE_SEP;
        $sbuf .= "<tr>" . LOG4PHP_LINE_SEP;
        $sbuf .= "<th>Time</th>" . LOG4PHP_LINE_SEP;
        $sbuf .= "<th>Thread</th>" . LOG4PHP_LINE_SEP;
        $sbuf .= "<th>Level</th>" . LOG4PHP_LINE_SEP;
        $sbuf .= "<th>Category</th>" . LOG4PHP_LINE_SEP;
        if ($this->locationInfo)
            $sbuf .= "<th>File:Line</th>" . LOG4PHP_LINE_SEP;
        $sbuf .= "<th>Message</th>" . LOG4PHP_LINE_SEP;
        $sbuf .= "</tr>" . LOG4PHP_LINE_SEP;

        return $sbuf;
    }

    /**
     * @return string Returns the appropriate HTML footers.
     */
    function getFooter()
    {
        $sbuf = "</table>" . LOG4PHP_LINE_SEP;
        $sbuf .= "<br>" . LOG4PHP_LINE_SEP;
        $sbuf .= "</body></html>";

        return $sbuf;
    }
}
?>