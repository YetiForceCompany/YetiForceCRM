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
 */
require_once(LOG4PHP_DIR . '/or/LoggerDefaultRenderer.php');
require_once(LOG4PHP_DIR . '/or/LoggerObjectRenderer.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * Map class objects to an {@link LoggerObjectRenderer}.
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.4 $
 * @package log4php
 * @subpackage or
 * @since 0.3
 */
class LoggerRendererMap {

    /**
     * @var array
     */
    var $map;

    /**
     * @var LoggerDefaultRenderer
     */
    var $defaultRenderer;

    /**
     * Constructor
     */
    function LoggerRendererMap()
    {
        $this->map = array();
        $this->defaultRenderer = new LoggerDefaultRenderer();
    }

    /**
     * Add a renderer to a hierarchy passed as parameter.
     * Note that hierarchy must implement getRendererMap() and setRenderer() methods.
     *
     * @param LoggerHierarchy &$repository a logger repository.
     * @param string &$renderedClassName
     * @param string &$renderingClassName
     * @static
     */
    function addRenderer(&$repository, $renderedClassName, $renderingClassName)
    {
        LoggerLog::debug("LoggerRendererMap::addRenderer() Rendering class: [{$renderingClassName}], Rendered class: [{$renderedClassName}].");
        $renderer = LoggerObjectRenderer::factory($renderingClassName);
        if($renderer == null) {
            LoggerLog::warn("LoggerRendererMap::addRenderer() Could not instantiate renderer [{$renderingClassName}].");
            return;
        } else {
            $repository->setRenderer($renderedClassName, $renderer);
        }
    }


    /**
     * Find the appropriate renderer for the class type of the
     * <var>o</var> parameter. 
     *
     * This is accomplished by calling the {@link getByObject()} 
     * method if <var>o</var> is object or using {@link LoggerDefaultRenderer}. 
     * Once a renderer is found, it is applied on the object <var>o</var> and 
     * the result is returned as a string.
     *
     * @param mixed $o
     * @return string 
     */
    function findAndRender($o)
    {
        if($o == null) {
            return null;
        } else {
            if (is_object($o)) {
                $renderer = $this->getByObject($o);
                if ($renderer !== null) {
                    return $renderer->doRender($o);
                } else {
                    return null;
                }
            } else {
                $renderer = $this->defaultRenderer;
                return $renderer->doRender($o);
            }
        }
    }

    /**
     * Syntactic sugar method that calls {@link PHP_MANUAL#get_class} with the
     * class of the object parameter.
     * 
     * @param mixed $o
     * @return string
     */
    function &getByObject($o)
    {
        return ($o == null) ? null : $this->getByClassName(get_class($o));
    }


    /**
     * Search the parents of <var>clazz</var> for a renderer. 
     *
     * The renderer closest in the hierarchy will be returned. If no
     * renderers could be found, then the default renderer is returned.
     *
     * @param string $class
     * @return LoggerObjectRenderer
     */
    function &getByClassName($class)
    {
        $r = null;
        for($c = strtolower($class); !empty($c); $c = get_parent_class($c)) {
            if (isset($this->map[$c])) {
                return  $this->map[$c];
            }
        }
        return $this->defaultRenderer;
    }

    /**
     * @return LoggerDefaultRenderer
     */
    function &getDefaultRenderer()
    {
        return $this->defaultRenderer;
    }


    function clear()
    {
        $this->map = array();
    }

    /**
     * Register a {@link LoggerObjectRenderer} for <var>clazz</var>.
     * @param string $class
     * @param LoggerObjectRenderer $or
     */
    function put($class, $or)
    {
        $this->map[strtolower($class)] = $or;
    }
    
    /**
     * @param string $class
     * @return boolean
     */
    function rendererExists($class)
    {
        $class = basename($class);
        if (!class_exists($class)) {
            @include_once(LOG4PHP_DIR ."/or/{$class}.php");
        }
        return class_exists($class);
    }
}
?>