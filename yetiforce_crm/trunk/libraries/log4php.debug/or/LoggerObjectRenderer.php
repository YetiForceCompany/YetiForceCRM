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
 * @subpackage or
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');
 
/**
 * Subclass this abstract class in order to render objects as strings.
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.3 $
 * @package log4php
 * @subpackage or
 * @abstract
 * @since 0.3
 */
class LoggerObjectRenderer {

    /**
     * @param string $class classname
     * @return LoggerObjectRenderer create LoggerObjectRenderer instances
     */
    function factory($class)
    {
        if (!empty($class)) {
            $class = basename($class);
            @include_once(LOG4PHP_DIR . "/or/{$class}.php");
            if (class_exists($class)) {
                return new $class();
            }
        }
        return null;
    }

    /**
     * Render the entity passed as parameter as a String.
     * @param mixed $o entity to render
     * @return string
     */
    function doRender($o)
    {
        // abstract
    }
}
?>