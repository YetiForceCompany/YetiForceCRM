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
 
/**
 * String constant designating no time information. Current value of
 * this constant is <b>NULL</b>.
 */
define ('LOG4PHP_LOGGER_LAYOUT_NULL_DATE_FORMAT',   'NULL');

/**
 * String constant designating relative time. Current value of
 * this constant is <b>RELATIVE</b>.
 */
define ('LOG4PHP_LOGGER_LAYOUT_RELATIVE_TIME_DATE_FORMAT', 'RELATIVE');

/**
 * TTCC layout format consists of time, thread, category and nested
 * diagnostic context information, hence the name.
 * 
 * <p>Each of the four fields can be individually enabled or
 * disabled. The time format depends on the <b>DateFormat</b> used.</p>
 *
 * <p>If no dateFormat is specified it defaults to '%c'. 
 * See php {@link PHP_MANUAL#date} function for details.</p>
 *
 * Params:
 * - {@link $threadPrinting} (true|false) enable/disable pid reporting.
 * - {@link $categoryPrefixing} (true|false) enable/disable logger category reporting.
 * - {@link $contextPrinting} (true|false) enable/disable NDC reporting.
 * - {@link $microSecondsPrinting} (true|false) enable/disable micro seconds reporting in timestamp.
 * - {@link $dateFormat} (string) set date format. See php {@link PHP_MANUAL#date} function for details.
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.12 $
 * @package log4php
 * @subpackage layouts
 */
class LoggerLayoutTTCC extends LoggerLayout {

    // Internal representation of options
    var $threadPrinting    = true;
    var $categoryPrefixing = true;
    var $contextPrinting   = true;
    var $microSecondsPrinting = true;
    
    /**
     * @var string date format. See {@link PHP_MANUAL#strftime} for details
     */
    var $dateFormat = '%c';

    /**
     * Constructor
     *
     * @param string date format
     * @see dateFormat
     */
    function LoggerLayoutTTCC($dateFormat = '')
    {
        if (!empty($dateFormat))
            $this->dateFormat = $dateFormat;
        return;
    }

    /**
     * The <b>ThreadPrinting</b> option specifies whether the name of the
     * current thread is part of log output or not. This is true by default.
     */
    function setThreadPrinting($threadPrinting)
    {
        
        $this->threadPrinting = is_bool($threadPrinting) ? 
            $threadPrinting : 
            (bool)(strtolower($threadPrinting) == 'true'); 
    }

    /**
     * @return boolean Returns value of the <b>ThreadPrinting</b> option.
     */
    function getThreadPrinting() {
        return $this->threadPrinting;
    }

    /**
     * The <b>CategoryPrefixing</b> option specifies whether {@link Category}
     * name is part of log output or not. This is true by default.
     */
    function setCategoryPrefixing($categoryPrefixing)
    {
        $this->categoryPrefixing = is_bool($categoryPrefixing) ?
            $categoryPrefixing :
            (bool)(strtolower($categoryPrefixing) == 'true');
    }

    /**
     * @return boolean Returns value of the <b>CategoryPrefixing</b> option.
     */
    function getCategoryPrefixing() {
        return $this->categoryPrefixing;
    }

    /**
     * The <b>ContextPrinting</b> option specifies log output will include
     * the nested context information belonging to the current thread.
     * This is true by default.
     */
    function setContextPrinting($contextPrinting) {
        $this->contextPrinting = is_bool($contextPrinting) ? 
            $contextPrinting : 
            (bool)(strtolower($contextPrinting) == 'true'); 
    }

    /**
     * @return boolean Returns value of the <b>ContextPrinting</b> option.
     */
    function getContextPrinting()
    {
        return $this->contextPrinting;
    }
    
    /**
     * The <b>MicroSecondsPrinting</b> option specifies if microseconds infos
     * should be printed at the end of timestamp.
     * This is true by default.
     */
    function setMicroSecondsPrinting($microSecondsPrinting) {
        $this->microSecondsPrinting = is_bool($microSecondsPrinting) ? 
            $microSecondsPrinting : 
            (bool)(strtolower($microSecondsPrinting) == 'true'); 
    }

    /**
     * @return boolean Returns value of the <b>MicroSecondsPrinting</b> option.
     */
    function getMicroSecondsPrinting()
    {
        return $this->microSecondsPrinting;
    }
    
    
    function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }
    
    /**
     * @return string
     */
    function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * In addition to the level of the statement and message, the
     * returned string includes time, thread, category.
     * <p>Time, thread, category are printed depending on options.
     *
     * @param LoggerLoggingEvent $event
     * @return string
     */
    function format($event)
    {
        $timeStamp = (float)$event->getTimeStamp();
        $format = strftime($this->dateFormat, (int)$timeStamp);
        
        if ($this->microSecondsPrinting) {
            $usecs = round(($timeStamp - (int)$timeStamp) * 1000);
            $format .= sprintf(',%03d', $usecs);
        }
            
        $format .= ' ';
        
        if ($this->threadPrinting)
            $format .= '['.getmypid().'] ';
       
        $level = $event->getLevel();
        $format .= $level->toString().' ';
        
        if($this->categoryPrefixing) {
            $format .= $event->getLoggerName().' ';
        }
       
        if($this->contextPrinting) {
            $ndc = $event->getNDC();
            if($ndc != null) {
    	        $format .= $ndc.' ';
            }
        }
        
        $format .= '- '.$event->getRenderedMessage();
        $format .= LOG4PHP_LINE_SEP;
        
        return $format;
    }

    function ignoresThrowable()
    {
        return true;
    }
}
?>