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
require_once(LOG4PHP_DIR . '/LoggerLog.php');

define('LOG4PHP_LEVEL_OFF_INT',     2147483647); 
define('LOG4PHP_LEVEL_FATAL_INT',        50000);
define('LOG4PHP_LEVEL_ERROR_INT',        40000);
define('LOG4PHP_LEVEL_WARN_INT',         30000);
define('LOG4PHP_LEVEL_INFO_INT',         20000);
define('LOG4PHP_LEVEL_DEBUG_INT',        10000);
define('LOG4PHP_LEVEL_ALL_INT',    -2147483648);

/**
 * Defines the minimum set of levels recognized by the system, that is
 * <i>OFF</i>, <i>FATAL</i>, <i>ERROR</i>,
 * <i>WARN</i>, <i>INFO</i, <i>DEBUG</i> and
 * <i>ALL</i>.
 *
 * <p>The <i>LoggerLevel</i> class may be subclassed to define a larger
 * level set.</p>
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.11 $
 * @package log4php
 * @since 0.5
 */
class LoggerLevel {

    /**
     * @var integer
     */
    var $level;
  
    /**
     * @var string
     */
    var $levelStr;
  
    /**
     * @var integer
     */
    var $syslogEquivalent;

    /**
     * Constructor
     *
     * @param integer $level
     * @param string $levelStr
     * @param integer $syslogEquivalent
     */
    function LoggerLevel($level, $levelStr, $syslogEquivalent)
    {
        $this->level = $level;
        $this->levelStr = $levelStr;
        $this->syslogEquivalent = $syslogEquivalent;
    }

    /**
     * Two priorities are equal if their level fields are equal.
     *
     * @param object $o
     * @return boolean 
     */
    function equals($o)
    {
        if (is_a($o, 'loggerlevel')) {
            return ($this->level == $o->level);
        } else {
            return false;
        }
    }
    
    /**
     * Returns an Off Level
     * @static
     * @return LoggerLevel
     */
    function &getLevelOff()
    {
        static $level;
        if (!isset($level)) $level = new LoggerLevel(LOG4PHP_LEVEL_OFF_INT, 'OFF', 0);
        return $level;
    }

    /**
     * Returns a Fatal Level
     * @static
     * @return LoggerLevel
     */
    function &getLevelFatal()
    {
        static $level;
        if (!isset($level)) $level = new LoggerLevel(LOG4PHP_LEVEL_FATAL_INT, 'FATAL', 0);
        return $level;
    }
    
    /**
     * Returns an Error Level
     * @static
     * @return LoggerLevel
     */
    function &getLevelError()
    {
        static $level;
        if (!isset($level)) $level = new LoggerLevel(LOG4PHP_LEVEL_ERROR_INT, 'ERROR', 3);
        return $level;
    }
    
    /**
     * Returns a Warn Level
     * @static
     * @return LoggerLevel
     */
    function &getLevelWarn()
    {
        static $level;
        if (!isset($level)) $level = new LoggerLevel(LOG4PHP_LEVEL_WARN_INT, 'WARN', 4);
        return $level;
    }

    /**
     * Returns an Info Level
     * @static
     * @return LoggerLevel
     */
    function &getLevelInfo()
    {
        static $level;
        if (!isset($level)) $level = new LoggerLevel(LOG4PHP_LEVEL_INFO_INT, 'INFO', 6);
        return $level;
    }

    /**
     * Returns a Debug Level
     * @static
     * @return LoggerLevel
     */
    function &getLevelDebug()
    {
        static $level;
        if (!isset($level)) $level = new LoggerLevel(LOG4PHP_LEVEL_DEBUG_INT, 'DEBUG', 7);
        return $level;
    }

    /**
     * Returns an All Level
     * @static
     * @return LoggerLevel
     */
    function &getLevelAll()
    {
        static $level;
        if (!isset($level)) $level = new LoggerLevel(LOG4PHP_LEVEL_ALL_INT, 'ALL', 7);
        return $level;
    }
    
    /**
     * Return the syslog equivalent of this priority as an integer.
     * @final
     * @return integer
     */
    function getSyslogEquivalent()
    {
        return $this->syslogEquivalent;
    }

    /**
     * Returns <i>true</i> if this level has a higher or equal
     * level than the level passed as argument, <i>false</i>
     * otherwise.  
     * 
     * <p>You should think twice before overriding the default
     * implementation of <i>isGreaterOrEqual</i> method.
     *
     * @param LoggerLevel $r
     * @return boolean
     */
    function isGreaterOrEqual($r)
    {
        return $this->level >= $r->level;
    }

    /**
     * Returns the string representation of this priority.
     * @return string
     * @final
     */
    function toString()
    {
        return $this->levelStr;
    }

    /**
     * Returns the integer representation of this level.
     * @return integer
     */
    function toInt()
    {
        return $this->level;
    }

    /**
     * Convert the string passed as argument to a level. If the
     * conversion fails, then this method returns a DEBUG Level.
     *
     * @param mixed $arg
     * @param LoggerLevel $default
     * @static 
     */
    function &toLevel($arg, $defaultLevel = null)
    {
        if ($defaultLevel === null) {
            return LoggerLevel::toLevel($arg, LoggerLevel::getLevelDebug());
        } else {
            if (is_int($arg)) {
                switch($arg) {
                    case LOG4PHP_LEVEL_ALL_INT:     return LoggerLevel::getLevelAll();
                    case LOG4PHP_LEVEL_DEBUG_INT:   return LoggerLevel::getLevelDebug();
                    case LOG4PHP_LEVEL_INFO_INT:    return LoggerLevel::getLevelInfo();
                    case LOG4PHP_LEVEL_WARN_INT:    return LoggerLevel::getLevelWarn();
                    case LOG4PHP_LEVEL_ERROR_INT:   return LoggerLevel::getLevelError();
                    case LOG4PHP_LEVEL_FATAL_INT:   return LoggerLevel::getLevelFatal();
                    case LOG4PHP_LEVEL_OFF_INT:     return LoggerLevel::getLevelOff();
                    default:                        return $defaultLevel;
                }
            } else {
                switch(strtoupper($arg)) {
                    case 'ALL':     return LoggerLevel::getLevelAll();
                    case 'DEBUG':   return LoggerLevel::getLevelDebug();
                    case 'INFO':    return LoggerLevel::getLevelInfo();
                    case 'WARN':    return LoggerLevel::getLevelWarn();
                    case 'ERROR':   return LoggerLevel::getLevelError();
                    case 'FATAL':   return LoggerLevel::getLevelFatal();
                    case 'OFF':     return LoggerLevel::getLevelOff();
                    default:        return $defaultLevel;
                }
            }
        }
    }
}
?>