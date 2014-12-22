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
require_once(LOG4PHP_DIR . '/LoggerAppender.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');
require_once(LOG4PHP_DIR . '/helpers/LoggerOptionConverter.php');

/**
 * Abstract superclass of the other appenders in the package.
 *  
 * This class provides the code for common functionality, such as
 * support for threshold filtering and support for general filters.
 *
 * @author  VxR <vxr@vxr.it>
 * @author  Sergio Strampelli <sergio@ascia.net> 
 * @version $Revision: 1.15 $
 * @package log4php
 * @abstract
 */
class LoggerAppenderSkeleton extends LoggerAppender {

    /**
     * @var boolean closed appender flag
     */
    var $closed;
    
    /**
     * @var object unused
     */
    var $errorHandler;
           
    /**
     * The first filter in the filter chain
     * @var LoggerFilter
     */
    var $headFilter = null;
            
    /**
     * LoggerLayout for this appender. It can be null if appender has its own layout
     * @var LoggerLayout
     */
    var $layout = null; 
           
    /**
     * @var string Appender name
     */
    var $name;
           
    /**
     * The last filter in the filter chain
     * @var LoggerFilter
     */
    var $tailFilter = null; 
           
    /**
     * @var LoggerLevel There is no level threshold filtering by default.
     */
    var $threshold = null;
    
    /**
     * @var boolean needs a layout formatting ?
     */
    var $requiresLayout = false;
    
/* --------------------------------------------------------------------------*/
/* --------------------------------------------------------------------------*/
/* --------------------------------------------------------------------------*/
    
    /**
     * Constructor
     *
     * @param string $name appender name
     */
    function LoggerAppenderSkeleton($name)
    {
        $this->name = $name;
        $this->clearFilters();
    }

    /**
     * @param LoggerFilter $newFilter add a new LoggerFilter
     * @see LoggerAppender::addFilter()
     */
    function addFilter($newFilter)
    {
        if($this->headFilter === null) {
            $this->headFilter = $newFilter;
            $this->tailFilter =& $this->headFilter;
        } else {
            $this->tailFilter->next = $newFilter;
            $this->tailFilter =& $this->tailFilter->next;
        }
    }
    
    /**
     * Derived appenders should override this method if option structure
     * requires it.
     */
    function activateOptions() 
    { 

    }
    
    /**
     * Subclasses of {@link LoggerAppenderSkeleton} should implement 
     * this method to perform actual logging.
     *
     * @param LoggerLoggingEvent $event
     * @see doAppend()
     * @abstract
     */
    function append($event)
    { 
        // override me
    }
 
    /**
     * @see LoggerAppender::clearFilters()
     */
    function clearFilters()
    {
        unset($this->headFilter);
        unset($this->tailFilter);
        $this->headFilter = null;
        $this->tailFilter = null;
    }
           
    /**
     * @see LoggerAppender::close()
     */
    function close()
    {
        //override me
    }
            
    /**
     * Finalize this appender by calling the derived class' <i>close()</i> method.
     */
    function finalize() 
    {
        // An appender might be closed then garbage collected. There is no
        // point in closing twice.
        if ($this->closed) return;
        
        LoggerLog::debug("LoggerAppenderSkeleton::finalize():name=[{$this->name}].");
        
        $this->close();
    }
    
    /**
     * Do not use this method.
     * @see LoggerAppender::getErrorHandler()
     * @return object
     */
    function &getErrorHandler()
    {
        return $this->errorHandler;
    } 
           
    /**
     * @see LoggerAppender::getFilter()
     * @return Filter
     */
    function &getFilter()
    {
        return $this->headFilter;
    } 

    /** 
     * Return the first filter in the filter chain for this Appender. 
     * The return value may be <i>null</i> if no is filter is set.
     * @return Filter
     */
    function &getFirstFilter()
    {
        return $this->headFilter;
    }
            
    /**
     * @see LoggerAppender::getLayout()
     * @return LoggerLayout
     */
    function &getLayout()
    {
        return $this->layout;
    }
           
    /**
     * @see LoggerAppender::getName()
     * @return string
     */
    function getName()
    {
        return $this->name;
    }
    
    /**
     * Returns this appenders threshold level. 
     * See the {@link setThreshold()} method for the meaning of this option.
     * @return LoggerLevel
     */
    function &getThreshold()
    { 
        return $this->threshold;
    }
    
    /**
     * Check whether the message level is below the appender's threshold. 
     *
     *
     * If there is no threshold set, then the return value is always <i>true</i>.
     * @param LoggerLevel $priority
     * @return boolean true if priority is greater or equal than threshold  
     */
    function isAsSevereAsThreshold($priority)
    {
        if ($this->threshold === null)
            return true;
            
        return $priority->isGreaterOrEqual($this->getThreshold());
    }
    
    /**
     * @see LoggerAppender::doAppend()
     * @param LoggerLoggingEvent $event
     */
    function doAppend($event)
    {
        LoggerLog::debug("LoggerAppenderSkeleton::doAppend()"); 

        if ($this->closed) {
            LoggerLog::debug("LoggerAppenderSkeleton::doAppend() Attempted to append to closed appender named [{$this->name}].");
            return;
        }
        if(!$this->isAsSevereAsThreshold($event->getLevel())) {
            LoggerLog::debug("LoggerAppenderSkeleton::doAppend() event level is less severe than threshold.");
            return;
        }

        $f = $this->getFirstFilter();
    
        while($f !== null) {
            switch ($f->decide($event)) {
                case LOG4PHP_LOGGER_FILTER_DENY: return;
                case LOG4PHP_LOGGER_FILTER_ACCEPT: return $this->append($event);
                case LOG4PHP_LOGGER_FILTER_NEUTRAL: $f = $f->next;
            }
        }
        $this->append($event);    
    }    
        
            
    /**
     * @see LoggerAppender::requiresLayout()
     * @return boolean
     */
    function requiresLayout()
    {
        return $this->requiresLayout;
    }
            
    /**
     * @see LoggerAppender::setErrorHandler()
     * @param object
     */
    function setErrorHandler($errorHandler)
    {
        if($errorHandler == null) {
          // We do not throw exception here since the cause is probably a
          // bad config file.
            LoggerLog::warn("You have tried to set a null error-handler.");
        } else {
            $this->errorHandler = $errorHandler;
        }
    } 
           
    /**
     * @see LoggerAppender::setLayout()
     * @param LoggerLayout $layout
     */
    function setLayout($layout)
    {
        if ($this->requiresLayout())
            $this->layout = $layout;
    } 
 
    /**
     * @see LoggerAppender::setName()
     * @param string $name
     */
    function setName($name) 
    {
        $this->name = $name;    
    }
    
    /**
     * Set the threshold level of this appender.
     *
     * @param mixed $threshold can be a {@link LoggerLevel} object or a string.
     * @see LoggerOptionConverter::toLevel()
     */
    function setThreshold($threshold)
    {
        if (is_string($threshold)) {
           $this->threshold = LoggerOptionConverter::toLevel($threshold, null);
        }elseif (is_a($threshold, 'loggerlevel')) {
           $this->threshold = $threshold;
        }
    }
    
    /**
     * Perform actions before object serialization.
     *
     * Call {@link finalize()} to properly close the appender.
     */
    function __sleep()
    {
        $this->finalize();
        return array_keys(get_object_vars($this)); 
    }
    
    /**
     * Perform actions after object deserialization.
     *
     * Call {@link activateOptions()} to properly setup the appender.
     */
    function __wakeup()
    {
        $this->activateOptions();
    }
    
}
?>