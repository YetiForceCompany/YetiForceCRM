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
require_once(LOG4PHP_DIR . '/LoggerLevel.php');
require_once(LOG4PHP_DIR . '/LoggerRoot.php');
require_once(LOG4PHP_DIR . '/or/LoggerRendererMap.php');
require_once(LOG4PHP_DIR . '/LoggerDefaultCategoryFactory.php');

/**
 * This class is specialized in retrieving loggers by name and also maintaining 
 * the logger hierarchy.
 *
 * <p>The casual user does not have to deal with this class directly.</p>
 *
 * <p>The structure of the logger hierarchy is maintained by the
 * getLogger method. The hierarchy is such that children link
 * to their parent but parents do not have any pointers to their
 * children. Moreover, loggers can be instantiated in any order, in
 * particular descendant before ancestor.</p>
 *
 * <p>In case a descendant is created before a particular ancestor,
 * then it creates a provision node for the ancestor and adds itself
 * to the provision node. Other descendants of the same ancestor add
 * themselves to the previously created provision node.</p>
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.20 $
 * @package log4php
 */
class LoggerHierarchy {

    /**
     * @var object currently unused
     */
    var $defaultFactory;
    
    /**
     * @var boolean activate internal logging
     * @see LoggerLog
     */
    var $debug = false;

    /**
     * @var array hierarchy tree. saves here all loggers
     */
    var $ht = array();
    
    /**
     * @var LoggerRoot
     */
    var $root = null;
    
    /**
     * @var LoggerRendererMap
     */
    var $rendererMap;

    /**
     * @var LoggerLevel main level threshold
     */
    var $threshold;
    
    /**
     * @var boolean currently unused
     */
    var $emittedNoAppenderWarning       = false;

    /**
     * @var boolean currently unused
     */
    var $emittedNoResourceBundleWarning = false;
    
    
/* --------------------------------------------------------------------------*/
/* --------------------------------------------------------------------------*/
/* --------------------------------------------------------------------------*/

    function &singleton()
    {
        static $instance;
        
        if (!isset($instance))
            $instance = new LoggerHierarchy(new LoggerRoot());
        return $instance;
    }
    
    /**
     * Create a new logger hierarchy.
     * @param object $root the root logger
     */
    function LoggerHierarchy($root)
    {
        $this->root    =& $root;
        // Enable all level levels by default.
        $this->setThreshold(LoggerLevel::getLevelAll());
        $this->root->setHierarchy($this);
        $this->rendererMap = new LoggerRendererMap();
        $this->defaultFactory = new LoggerDefaultCategoryFactory();        
    }
     
    /**
     * Add a HierarchyEventListener event to the repository. 
     * Not Yet Impl.
     */
    function addHierarchyEventListener($listener)
    {
        return;
    }
     
    /**
     * Add an object renderer for a specific class.
     * Not Yet Impl.
     */
    function addRenderer($classToRender, $or)
    {
        $this->rendererMap->put($classToRender, $or);
    } 
    
    /**
     * This call will clear all logger definitions from the internal hashtable.
     */
    function clear()
    {
        $this->ht = array();
    }
      
    function emitNoAppenderWarning($cat)
    {
        return;
    }
    
    /**
     * Check if the named logger exists in the hierarchy.
     * @param string $name
     * @return boolean
     */
    function exists($name)
    {
        return in_array($name, array_keys($this->ht));
    }

    function fireAddAppenderEvent($logger, $appender)
    {
        return;
    }
    
    /**
     * @deprecated Please use {@link getCurrentLoggers()} instead.
     */
    function &getCurrentCategories()
    {
        return $this->getCurrentLoggers();
    }
    
    /**
     * Returns all the currently defined categories in this hierarchy as an array.
     * @return array
     */  
    function &getCurrentLoggers()
    {
        $loggers = array();
        $loggerNames = array_keys($this->ht);
        $enumLoggers = sizeof($loggerNames);
        for ($i = 0; $i < $enumLoggers; $i++) {
            $loggerName = $loggerNames[$i];
            $loggers[] =& $this->ht[$loggerName];
        }
        return $loggers; 
    }
    
    /**
     * Return a new logger instance named as the first parameter using the default factory.
     * 
     * @param string $name logger name
     * @param LoggerFactory $factory a {@link LoggerFactory} instance or null     
     * @return Logger
     */
    function &getLogger($name, $factory = null)
    {
        if ($factory === null) {
            return $this->getLoggerByFactory($name, $this->defaultFactory);
        } else {
            return $this->getLoggerByFactory($name, $factory);
        }
    } 
    
    /**
     * Return a new logger instance named as the first parameter using the default factory.
     * 
     * @param string $name logger name
     * @return Logger
     * @todo merge with {@link getLogger()}
     */
    function &getLoggerByFactory($name, $factory)
    {
        if (!isset($this->ht[$name])) {
            LoggerLog::debug("LoggerHierarchy::getLoggerByFactory():name=[$name]:factory=[".get_class($factory)."] creating a new logger...");
            $this->ht[$name] = $factory->makeNewLoggerInstance($name);
            $this->ht[$name]->setHierarchy($this);
            $nodes = explode('.', $name);
            $firstNode = array_shift($nodes);
            if ( $firstNode != $name and isset($this->ht[$firstNode])) {
                LoggerLog::debug("LoggerHierarchy::getLogger($name) parent is now [$firstNode]");            
                $this->ht[$name]->parent =& $this->ht[$firstNode];
            } else {
                LoggerLog::debug("LoggerHierarchy::getLogger($name) parent is now [root]");            
                $this->ht[$name]->parent =& $this->root;
            } 
            if (sizeof($nodes) > 0) {
                // find parent node
                foreach ($nodes as $node) {
                    $parentNode = "$firstNode.$node";
                    if (isset($this->ht[$parentNode]) and $parentNode != $name) {
                        LoggerLog::debug("LoggerHierarchy::getLogger($name) parent is now [$parentNode]");                    
                        $this->ht[$name]->parent =& $this->ht[$parentNode];
                    }
                    $firstNode .= ".$node";
                }
            }
            // update children
            /*
            $children = array();
            foreach (array_keys($this->ht) as $nodeName) {
                if ($nodeName != $name and substr($nodeName, 0, strlen($name)) == $name) {
                    $children[] = $nodeName;    
                }
            }
            */
        }            
        return $this->ht[$name];
    }
    
    /**
     * @return LoggerRendererMap Get the renderer map for this hierarchy.
     */
    function &getRendererMap()
    {
        return $this->rendererMap;
    }
    
    /**
     * @return LoggerRoot Get the root of this hierarchy.
     */ 
    function &getRootLogger()
    {
        if (!isset($this->root) or $this->root == null)
            $this->root = new LoggerRoot();
        return $this->root;
    }
     
    /**
     * @return LoggerLevel Returns the threshold Level.
     */
    function getThreshold()
    {
        return $this->threshold;
    } 

    /**
     * This method will return true if this repository is disabled 
     * for level object passed as parameter and false otherwise.
     * @return boolean
     */
    function isDisabled($level)
    {
        return ($this->threshold->level > $level->level);
    }
    
    /**
     * @deprecated Deprecated with no replacement.
     */
    function overrideAsNeeded($override)
    {
        return;
    } 
    
    /**
     * Reset all values contained in this hierarchy instance to their
     * default. 
     *
     * This removes all appenders from all categories, sets
     * the level of all non-root categories to <i>null</i>,
     * sets their additivity flag to <i>true</i> and sets the level
     * of the root logger to {@link LOGGER_LEVEL_DEBUG}.  Moreover,
     * message disabling is set its default "off" value.
     * 
     * <p>Existing categories are not removed. They are just reset.
     *
     * <p>This method should be used sparingly and with care as it will
     * block all logging until it is completed.</p>
     */
    function resetConfiguration()
    {
        $root =& $this->getRootLogger();
        
        $root->setLevel(LoggerLevel::getLevelDebug());
        $this->setThreshold(LoggerLevel::getLevelAll());
        $this->shutDown();
        $loggers =& $this->getCurrentLoggers();
        $enumLoggers = sizeof($loggers);
        for ($i = 0; $i < $enumLoggers; $i++) {
            $loggers[$i]->setLevel(null);
        	$loggers[$i]->setAdditivity(true);
        	$loggers[$i]->setResourceBundle(null);
        }
        $this->rendererMap->clear();
    }
      
    /**
     * @deprecated Deprecated with no replacement.
     */
    function setDisableOverride($override)
    {
        return;
    }
    
    /**
     * Used by subclasses to add a renderer to the hierarchy passed as parameter.
     * @param string $renderedClass a LoggerRenderer class name
     * @param LoggerRenderer $renderer
     *
     */
    function setRenderer($renderedClass, $renderer)
    {
        $this->rendererMap->put($renderedClass, $renderer);
    }
    
    /**
     * set a new threshold level
     *
     * @param LoggerLevel $l
     */
    function setThreshold($l)
    {
        if ($l !== null)
            $this->threshold = $l;
    }
    
    /**
     * Shutting down a hierarchy will <i>safely</i> close and remove
     * all appenders in all categories including the root logger.
     * 
     * <p>Some appenders such as {@link LoggerSocketAppender}
     * need to be closed before the
     * application exists. Otherwise, pending logging events might be
     * lost.
     * 
     * <p>The shutdown method is careful to close nested
     * appenders before closing regular appenders. This is allows
     * configurations where a regular appender is attached to a logger
     * and again to a nested appender.
     */
    function shutdown()
    {
        $this->root->removeAllAppenders();
        $cats =& $this->getCurrentLoggers();
        $enumCats = sizeof($cats);        
        if ($enumCats > 0) {
            for ($i = 0; $i < $enumCats; $i++) {
                $cats[$i]->removeAllAppenders();
            }
        }
    }  
} 
?>