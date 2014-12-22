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
 * Abstract class that defines output logs strategies.
 *
 * @author  VxR <vxr@vxr.it>
 * @version $Revision: 1.14 $
 * @package log4php
 * @abstract
 */
class LoggerAppender {

    /**
     * Factory
     *
     * @param string $name appender name
     * @param string $class create an instance of this appender class
     * @return LoggerAppender
     */
    function factory($name, $class)
    {
        $class = basename($class);
        if (!empty($class)) {
            if (!class_exists($class)) 
                @include_once(LOG4PHP_DIR . "/appenders/{$class}.php");
            if (class_exists($class))
                return new $class($name);
        }
        return null;
    }
    
    /**
     * Singleton
     *
     * @param string $name appender name
     * @param string $class create or get a reference instance of this class
     * @return LoggerAppender 
     */
    function &singleton($name, $class = '')
    {
        static $instances;
        
        if (!empty($name)) {
            if (!isset($instances[$name])) {
                if (!empty($class)) {
                    $appender = LoggerAppender::factory($name, $class);
                    if ($appender !== null) { 
                        $instances[$name] = $appender;
                        return $instances[$name];
                    }
                }
                return null;
            }
            return $instances[$name];                
        }        
        return null;        
    }
    
    /* --------------------------------------------------------------------------*/
    /* --------------------------------------------------------------------------*/
    /* --------------------------------------------------------------------------*/
    
    /**
     * Add a filter to the end of the filter list.
     *
     * @param LoggerFilter $newFilter add a new LoggerFilter
     * @abstract
     */
    function addFilter($newFilter)
    {
        // override 
    }
    
    /**
     * Clear the list of filters by removing all the filters in it.
     * @abstract
     */
    function clearFilters()
    {
        // override    
    }

    /**
     * Return the first filter in the filter chain for this Appender. 
     * The return value may be <i>null</i> if no is filter is set.
     * @return Filter
     */
    function &getFilter()
    {
        // override    
    } 
    
    /**
     * Release any resources allocated.
     * Subclasses of {@link LoggerAppender} should implement 
     * this method to perform proper closing procedures.
     * @abstract
     */
    function close()
    {
        //override me
    }

    /**
     * This method performs threshold checks and invokes filters before
     * delegating actual logging to the subclasses specific <i>append()</i> method.
     * @param LoggerLoggingEvent $event
     * @abstract
     */
    function doAppend($event)
    {
        //override me    
    }

    /**
     * Get the name of this appender.
     * @return string
     */
    function getName()
    {
        //override me    
    }

    /**
     * Do not use this method.
     *
     * @param object $errorHandler
     */
    function setErrorHandler($errorHandler)
    {
        // override me
    }
    
    /**
     * Do not use this method.
     * @return object Returns the ErrorHandler for this appender.
     */
    function &getErrorHandler()
    {
        return $this->errorHandler;
    } 

    /**
     * Set the Layout for this appender.
     *
     * @param LoggerLayout $layout
     */
    function setLayout($layout)
    {
        // override me
    }
    
    /**
     * Returns this appender layout.
     * @return LoggerLayout
     */
    function &getLayout()
    {
        // override me
    }

    /**
     * Set the name of this appender.
     *
     * The name is used by other components to identify this appender.
     *
     * @param string $name
     */
    function setName($name) 
    {
        // override me    
    }

    /**
     * Configurators call this method to determine if the appender
     * requires a layout. 
     *
     * <p>If this method returns <i>true</i>, meaning that layout is required, 
     * then the configurator will configure a layout using the configuration 
     * information at its disposal.  If this method returns <i>false</i>, 
     * meaning that a layout is not required, then layout configuration will be
     * skipped even if there is available layout configuration
     * information at the disposal of the configurator.</p>
     *
     * <p>In the rather exceptional case, where the appender
     * implementation admits a layout but can also work without it, then
     * the appender should return <i>true</i>.</p>
     *
     * @return boolean
     */
    function requiresLayout()
    {
        // override me
    }

}
?>