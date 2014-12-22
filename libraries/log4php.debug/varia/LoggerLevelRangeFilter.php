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
 * @subpackage varia
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');
 
/**
 */
require_once(LOG4PHP_DIR . '/helpers/LoggerOptionConverter.php');
require_once(LOG4PHP_DIR . '/spi/LoggerFilter.php');

/**
 * This is a very simple filter based on level matching, which can be
 * used to reject messages with priorities outside a certain range.
 *  
 * <p>The filter admits three options <b><var>LevelMin</var></b>, <b><var>LevelMax</var></b>
 * and <b><var>AcceptOnMatch</var></b>.</p>
 *
 * <p>If the level of the {@link LoggerLoggingEvent} is not between Min and Max
 * (inclusive), then {@link LOG4PHP_LOGGER_FILTER_DENY} is returned.</p>
 *  
 * <p>If the Logging event level is within the specified range, then if
 * <b><var>AcceptOnMatch</var></b> is <i>true</i>, 
 * {@link LOG4PHP_LOGGER_FILTER_ACCEPT} is returned, and if
 * <b><var>AcceptOnMatch</var></b> is <i>false</i>, 
 * {@link LOG4PHP_LOGGER_FILTER_NEUTRAL} is returned.</p>
 *  
 * <p>If <b><var>LevelMin</var></b> is not defined, then there is no
 * minimum acceptable level (ie a level is never rejected for
 * being too "low"/unimportant).  If <b><var>LevelMax</var></b> is not
 * defined, then there is no maximum acceptable level (ie a
 * level is never rejected for beeing too "high"/important).</p>
 *
 * <p>Refer to the {@link LoggerAppenderSkeleton::setThreshold()} method
 * available to <b>all</b> appenders extending {@link LoggerAppenderSkeleton} 
 * for a more convenient way to filter out events by level.</p>
 *
 * @log4j-class org.apache.log4j.varia.LevelRangeFilter
 * @log4j-author Simon Kitching
 * @log4j-author based on code by Ceki G&uuml;lc&uuml; 
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.2 $
 * @package log4php
 * @subpackage varia
 * @since 0.6
 */
class LoggerLevelRangeFilter extends LoggerFilter {
  
    /**
     * @var boolean
     */
    var $acceptOnMatch = true;

    /**
     * @var LoggerLevel
     */
    var $levelMin;
  
    /**
     * @var LoggerLevel
     */
    var $levelMax;

    /**
     * @return boolean
     */
    function getAcceptOnMatch()
    {
        return $this->acceptOnMatch;
    }
    
    /**
     * @param boolean $acceptOnMatch
     */
    function setAcceptOnMatch($acceptOnMatch)
    {
        $this->acceptOnMatch = LoggerOptionConverter::toBoolean($acceptOnMatch, true); 
    }
    
    /**
     * @return LoggerLevel
     */
    function getLevelMin()
    {
        return $this->levelMin;
    }
    
    /**
     * @param string $l the level min to match
     */
    function setLevelMin($l)
    {
        $this->levelMin = LoggerOptionConverter::toLevel($l, null);
    }

    /**
     * @return LoggerLevel
     */
    function getLevelMax()
    {
        return $this->levelMax;
    }
    
    /**
     * @param string $l the level max to match
     */
    function setLevelMax($l)
    {
        $this->levelMax = LoggerOptionConverter::toLevel($l, null);
    }

    /**
     * Return the decision of this filter.
     *
     * @param LoggerLoggingEvent $event
     * @return integer
     */
    function decide($event)
    {
        $level = $event->getLevel();
        
        if($this->levelMin !== null) {
            if ($level->isGreaterOrEqual($this->levelMin) == false) {
                // level of event is less than minimum
                return LOG4PHP_LOGGER_FILTER_DENY;
            }
        }

        if($this->levelMax !== null) {
            if ($level->toInt() > $this->levelMax->toInt()) {
                // level of event is greater than maximum
                // Alas, there is no Level.isGreater method. and using
                // a combo of isGreaterOrEqual && !Equal seems worse than
                // checking the int values of the level objects..
                return LOG4PHP_LOGGER_FILTER_DENY;
            }
        }

        if ($this->getAcceptOnMatch()) {
            // this filter set up to bypass later filters and always return
            // accept if level in range
            return  LOG4PHP_LOGGER_FILTER_ACCEPT;
        } else {
            // event is ok for this filter; allow later filters to have a look..
            return LOG4PHP_LOGGER_FILTER_NEUTRAL;
        }
    }
}
?>