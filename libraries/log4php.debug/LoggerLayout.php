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
 * Extend this abstract class to create your own log layout format.
 *  
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.10 $
 * @package log4php
 * @abstract
 */
class LoggerLayout {

    /**
     * Creates LoggerLayout instances with the given class name.
     *
     * @param string $class
     * @return LoggerLayout
     */
    function factory($class)
    {
        if (!empty($class)) {
            $class = basename($class);
            if (!class_exists($class))
                @include_once(LOG4PHP_DIR . "/layouts/{$class}.php");
            if (class_exists($class))
                return new $class();
        }
        return null;
    }

    /**
     * Override this method
     */
    function activateOptions() 
    {
        // override;
    }

    /**
     * Override this method to create your own layout format.
     *
     * @param LoggerLoggingEvent
     * @return string
     */
    function format($event)
    {
        return $event->getRenderedMessage();
    } 
    
    /**
     * Returns the content type output by this layout.
     * @return string
     */
    function getContentType()
    {
        return "text/plain";
    } 
            
    /**
     * Returns the footer for the layout format.
     * @return string
     */
    function getFooter()
    {
        return null;
    } 

    /**
     * Returns the header for the layout format.
     * @return string
     */
    function getHeader()
    {
        return null;
    }
}
?>