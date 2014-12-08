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
require_once(LOG4PHP_DIR . '/LoggerLevel.php');
require_once(LOG4PHP_DIR . '/spi/LoggerLoggingEvent.php');

/**
 * This class has been deprecated and replaced by the Logger subclass.
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.17 $
 * @package log4php
 * @see Logger
 */
class LoggerCategory {

    /**
     * Additivity is set to true by default, that is children inherit the 
     * appenders of their ancestors by default.
     * @var boolean
     */
    var $additive       = true;
    
    /**
     * @var string fully qualified class name
     */  
    var $fqcn           = 'LoggerCategory';

    /**
     * @var LoggerLevel The assigned level of this category.
     */
    var $level          = null;
    
    /**
     * @var string name of this category.
     */
    var $name           = '';
    
    /**
     * @var Logger The parent of this category.
     */
    var $parent         = null;

    /**
     * @var LoggerHierarchy the object repository
     */
    var $repository     = null; 

    /**
     * @var array collection of appenders
     * @see LoggerAppender
     */
    var $aai            = array();
    
/* --------------------------------------------------------------------------*/
/* --------------------------------------------------------------------------*/
/* --------------------------------------------------------------------------*/

    /**
     * Constructor.
     *
     * @param  string  $name  Category name   
     */
    function LoggerCategory($name)
    {
        $this->name = $name;
    }
    
    /**
     * Add a new Appender to the list of appenders of this Category instance.
     *
     * @param LoggerAppender $newAppender
     */
    function addAppender(&$newAppender)
    {
        $appenderName = $newAppender->getName();
        $this->aai[$appenderName] =& $newAppender;
    } 
            
    /**
     * If assertion parameter is false, then logs msg as an error statement.
     *
     * @param bool $assertion
     * @param string $msg message to log
     */
    function assertLog($assertion = true, $msg = '')
    {
        if ($assertion == false) {
            $this->error($msg);
        }
    } 

    /**
     * Call the appenders in the hierarchy starting at this.
     *
     * @param LoggerLoggingEvent $event 
     */
    function callAppenders($event) 
    {
        if (sizeof($this->aai) > 0) {
            foreach (array_keys($this->aai) as $appenderName) {
                $this->aai[$appenderName]->doAppend($event);
            }
        }
        if ($this->parent != null and $this->getAdditivity()) {
            $this->parent->callAppenders($event);
        }
    }
    
    /**
     * Log a message object with the DEBUG level including the caller.
     *
     * @param mixed $message message
     * @param mixed $caller caller object or caller string id
     */
    function debug($message, $caller = null)
    {
        $debugLevel = LoggerLevel::getLevelDebug();
        if ($this->repository->isDisabled($debugLevel)) {
            return;
        }
        if ($debugLevel->isGreaterOrEqual($this->getEffectiveLevel())) {
            $this->forcedLog($this->fqcn, $caller, $debugLevel, $message);
        }
    } 

    /**
     * Log a message object with the ERROR level including the caller.
     *
     * @param mixed $message message
     * @param mixed $caller caller object or caller string id
     */
    function error($message, $caller = null)
    {
        $errorLevel = LoggerLevel::getLevelError();
        if ($this->repository->isDisabled($errorLevel)) {
            return;
        }
        if ($errorLevel->isGreaterOrEqual($this->getEffectiveLevel())) {
            $this->forcedLog($this->fqcn, $caller, $errorLevel, $message);
        }
    }
  
    /**
     * Deprecated. Please use LoggerManager::exists() instead.
     *
     * @param string $name
     * @see LoggerManager::exists()
     * @deprecated
     */
    function exists($name)
    {
        return LoggerManager::exists($name);
    } 
 
    /**
     * Log a message object with the FATAL level including the caller.
     *
     * @param mixed $message message
     * @param mixed $caller caller object or caller string id
     */
    function fatal($message, $caller = null)
    {
        $fatalLevel = LoggerLevel::getLevelFatal();
        if ($this->repository->isDisabled($fatalLevel)) {
            return;
        }
        if ($fatalLevel->isGreaterOrEqual($this->getEffectiveLevel())) {
            $this->forcedLog($this->fqcn, $caller, $fatalLevel, $message);
        }
    } 
  
    /**
     * This method creates a new logging event and logs the event without further checks.
     *
     * It should not be called directly. Use {@link info()}, {@link debug()}, {@link warn()},
     * {@link error()} and {@link fatal()} wrappers.
     *
     * @param string $fqcn Fully Qualified Class Name of the Logger
     * @param mixed $caller caller object or caller string id
     * @param LoggerLevel $level log level     
     * @param mixed $message message
     * @see LoggerLoggingEvent          
     */
    function forcedLog($fqcn, $caller, $level, $message)
    {
        // $fqcn = is_object($caller) ? get_class($caller) : (string)$caller;
        $this->callAppenders(new LoggerLoggingEvent($fqcn, $this, $level, $message));
    } 

    /**
     * Get the additivity flag for this Category instance.
     * @return boolean
     */
    function getAdditivity()
    {
        return $this->additive;
    }
 
    /**
     * Get the appenders contained in this category as an array.
     * @return array collection of appenders
     */
    function &getAllAppenders() 
    {
        $appenders = array();
        $appenderNames = array_keys($this->aai);
        $enumAppenders = sizeof($appenderNames);
        for ($i = 0; $i < $enumAppenders; $i++) {
            $appenderName = $appenderNames[$i];
            $appenders[] =& $this->aai[$appenderName];
        }
        return $appenders; 
    }
    
    /**
     * Look for the appender named as name.
     * @return LoggerAppender
     */
    function &getAppender($name) 
    {
        return $this->aai[$name];
    }
    
    /**
     * Please use the {@link getEffectiveLevel()} method instead.
     * @deprecated
     */
    function getChainedPriority()
    {
        return $this->getEffectiveLevel();
    } 
 
    /**
     * Please use {@link LoggerManager::getCurrentLoggers()} instead.
     * @deprecated
     */
    function getCurrentCategories()
    {
        return LoggerManager::getCurrentLoggers();
    } 
 
    /**
     * Please use {@link LoggerManager::getLoggerRepository()} instead.
     * @deprecated 
     */
    function &getDefaultHierarchy()
    {
        return LoggerManager::getLoggerRepository();
    } 
 
    /**
     * @deprecated Use {@link getLoggerRepository()}
     * @return LoggerHierarchy 
     */
    function &getHierarchy()
    {
        return $this->getLoggerRepository();
    } 

    /**
     * Starting from this category, search the category hierarchy for a non-null level and return it.
     * @see LoggerLevel
     * @return LoggerLevel or null
     */
    function getEffectiveLevel()
    {
        for($c = $this; $c != null; $c = $c->parent) {
            if($c->level !== null)
            	return $c->level;
        }
        return null;
    }
  
    /**
     * Retrieve a category with named as the name parameter.
     * @return Logger
     */
    function &getInstance($name)
    {
        return LoggerManager::getLogger($name);
    }

    /**
     * Returns the assigned Level, if any, for this Category.
     * @return LoggerLevel or null 
     */
    function getLevel()
    {
        return $this->level;
    } 

    /**
     * Return the the repository where this Category is attached.
     * @return LoggerHierarchy
     */
    function &getLoggerRepository()
    {
        return $this->repository;
    } 

    /**
     * Return the category name.
     * @return string
     */
    function getName()
    {
        return $this->name;
    } 

    /**
     * Returns the parent of this category.
     * @return Logger
     */
    function &getParent() 
    {
        return $this->parent;
    }      

    /**
     * Please use getLevel() instead.
     * @deprecated
     */
    function getPriority()
    {
        return $this->getLevel();
    }
          
    /**
     * Return the inherited ResourceBundle for this category.
     */
    function getResourceBundle()
    {
        return;
    } 

    /**
     * Returns the string resource coresponding to key in this category's inherited resource bundle.
     */
    function getResourceBundleString($key)
    {
        return;
    } 

    /**
     * Return the root of the default category hierrachy.
     * @return LoggerRoot
     */
    function &getRoot()
    {
        return LoggerManager::getRootLogger();
    } 

    /**
     * Log a message object with the INFO Level.
     *
     * @param mixed $message message
     * @param mixed $caller caller object or caller string id
     */
    function info($message, $caller = null)
    {
        $infoLevel = LoggerLevel::getLevelInfo();
        if ($this->repository->isDisabled($infoLevel)) {
            return;
        }
        if ($infoLevel->isGreaterOrEqual($this->getEffectiveLevel())) {
            $this->forcedLog($this->fqcn, $caller, $infoLevel, $message);
        }
    }
     
    /**
     * Is the appender passed as parameter attached to this category?
     *
     * @param LoggerAppender $appender
     */
    function isAttached($appender)
    {
        return in_array($appender->getName(), array_keys($this->aai));
    } 
           
    /**
     * Check whether this category is enabled for the DEBUG Level.
     * @return boolean
     */
    function isDebugEnabled()
    {
        $debugLevel = LoggerLevel::getLevelDebug(); 
        if ($this->repository->isDisabled($debugLevel)) {
            return false;
        }
        return ($debugLevel->isGreaterOrEqual($this->getEffectiveLevel()));
    }       

    /**
     * Check whether this category is enabled for a given Level passed as parameter.
     *
     * @param LoggerLevel level
     * @return boolean
     */
    function isEnabledFor($level)
    {
        if ($this->repository->isDisabled($level)) {
            return false;
        }
        return (bool)($level->isGreaterOrEqual($this->getEffectiveLevel()));
    } 

    /**
     * Check whether this category is enabled for the info Level.
     * @return boolean
     * @see LoggerLevel
     */
    function isInfoEnabled()
    {
        $infoLevel = LoggerLevel::getLevelInfo();
        if ($this->repository->isDisabled($infoLevel)) {
            return false;
        }
        return ($infoLevel->isGreaterOrEqual($this->getEffectiveLevel()));
    } 

    /**
     * Log a localized and parameterized message.
     */
    function l7dlog($priority, $key, $params, $t)
    {
        return;
    } 

    /**
     * This generic form is intended to be used by wrappers.
     *
     * @param LoggerLevel $priority a valid level
     * @param mixed $message message
     * @param mixed $caller caller object or caller string id
     */
    function log($priority, $message, $caller = null)
    {
        if ($this->repository->isDisabled($priority)) {
            return;
        }
        if ($priority->isGreaterOrEqual($this->getEffectiveLevel())) {
            $this->forcedLog($this->fqcn, $caller, $priority, $message);
        }
    }

    /**
     * Remove all previously added appenders from this Category instance.
     */
    function removeAllAppenders()
    {
        $appenderNames = array_keys($this->aai);
        $enumAppenders = sizeof($appenderNames);
        for ($i = 0; $i < $enumAppenders; $i++) {
            $this->removeAppender($appenderNames[$i]); 
        }
    } 
            
    /**
     * Remove the appender passed as parameter form the list of appenders.
     *
     * @param mixed $appender can be an appender name or a {@link LoggerAppender} object
     */
    function removeAppender($appender)
    {
        if (is_a($appender, 'loggerappender')) {
            $appender->close();
            unset($this->aai[$appender->getName()]);
        } elseif (is_string($appender) and isset($this->aai[$appender])) {
            $this->aai[$appender]->close();
            unset($this->aai[$appender]);
        }
    } 

    /**
     * Set the additivity flag for this Category instance.
     *
     * @param boolean $additive
     */
    function setAdditivity($additive) 
    {
        $this->additive = (bool)$additive;
    }
    
    /**
     * @deprecated Please use {@link setLevel()} instead.
     * @see setLevel()
     */
    function setPriority($priority)
    {
        $this->setLevel($priority);
    } 

    /**
     * Only the Hiearchy class can set the hiearchy of a
     * category.
     *
     * @param LoggerHierarchy &$repository
     */
    function setHierarchy(&$repository)
    {
        $this->repository =& $repository;
    }

    /**
     * Set the level of this Category.
     *
     * @param LoggerLevel $level a level string or a level costant 
     */
    function setLevel($level)
    {
        $this->level = $level;
    } 

    /**
     * Set the resource bundle to be used with localized logging methods 
     */
    function setResourceBundle($bundle)
    {
        return;
    } 
           
    /**
     * @deprecated use {@link LoggerManager::shutdown()} instead.
     * @see LoggerManager::shutdown()
     */
    function shutdown()
    {
        LoggerManager::shutdown();
    } 
 
    /**
     * Log a message with the WARN level.
     *
     * @param mixed $message message
     * @param mixed $caller caller object or caller string id
     */
    function warn($message, $caller = null)
    {
        $warnLevel = LoggerLevel::getLevelWarn();
        if ($this->repository->isDisabled($warnLevel)) {
            return;
        }
        if ($warnLevel->isGreaterOrEqual($this->getEffectiveLevel())) {
            $this->forcedLog($this->fqcn, $caller, $warnLevel, $message);
        }
    }

}  
?>