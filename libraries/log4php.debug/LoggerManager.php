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
 * LOG4PHP_DIR points to the log4php root directory.
 *
 * If not defined it will be set automatically when the first package classfile 
 * is included
 * 
 * @var string 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__));

require_once(LOG4PHP_DIR . '/LoggerHierarchy.php');

/**
 * Use the LoggerManager to get Logger instances.
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.18 $
 * @package log4php
 * @see Logger
 * @todo create a configurator selector  
 */
class LoggerManager {
	protected static $loggerCache = false;

	static function getInstance($name = 'ROOT')
	{
		if (!self::$loggerCache) {
			self::$loggerCache = self::getLogger($name);
		}
		return self::$loggerCache;
	}
    /**
     * check if a given logger exists.
     * 
     * @param string $name logger name 
     * @static
     * @return boolean
     */
    function exists($name)
    {
        $repository =& LoggerManager::getLoggerRepository();
        return $repository->exists($name);
    }

    /**
     * Returns an array this whole Logger instances.
     * 
     * @static
     * @see Logger
     * @return array
     */
    function getCurrentLoggers()
    {
        $repository =& LoggerManager::getLoggerRepository();
        return $repository->getCurrentLoggers();
    }
    
    /**
     * Returns the root logger.
     * 
     * @static
     * @return object
     * @see LoggerRoot
     */
    function &getRootLogger()
    {
        $repository =& LoggerManager::getLoggerRepository();
        return $repository->getRootLogger();
    }
    
    /**
     * Returns the specified Logger.
     * 
     * @param string $name logger name
     * @param LoggerFactory $factory a {@link LoggerFactory} instance or null
     * @static
     * @return Logger
     */
    function &getLogger($name, $factory = null)
    {
        $repository =& LoggerManager::getLoggerRepository();
        return $repository->getLogger($name, $factory);
    }
    
    /**
     * Returns the LoggerHierarchy.
     * 
     * @static
     * @return LoggerHierarchy
     */
    function &getLoggerRepository()
    {
        return LoggerHierarchy::singleton();    
    }
    

    /**
     * Destroy loggers object tree.
     * 
     * @static
     * @return boolean 
     */
    function resetConfiguration()
    {
        $repository =& LoggerManager::getLoggerRepository();    
        return $repository->resetConfiguration();    
    }
    
    /**
     * Does nothing.
     * @static
     */
    function setRepositorySelector($selector, $guard)
    {
        return;
    }
    
    /**
     * Safely close all appenders.
     * @static
     */
    function shutdown()
    {
        $repository =& LoggerManager::getLoggerRepository();    
        return $repository->shutdown();    
    }
}

// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------

if (!defined('LOG4PHP_DEFAULT_INIT_OVERRIDE')) {
    if (isset($_ENV['log4php.defaultInitOverride'])) {
        /**
         * @ignore
         */
        define('LOG4PHP_DEFAULT_INIT_OVERRIDE', 
            LoggerOptionConverter::toBoolean($_ENV['log4php.defaultInitOverride'], false)
        );
    } elseif (isset($GLOBALS['log4php.defaultInitOverride'])) {
        /**
         * @ignore
         */
        define('LOG4PHP_DEFAULT_INIT_OVERRIDE', 
            LoggerOptionConverter::toBoolean($GLOBALS['log4php.defaultInitOverride'], false)
        );
    } else {
        /**
         * Controls init execution
         *
         * With this constant users can skip the default init procedure that is
         * called when this file is included.
         *
         * <p>If it is not user defined, log4php tries to autoconfigure using (in order):</p>
         *
         * - the <code>$_ENV['log4php.defaultInitOverride']</code> variable.
         * - the <code>$GLOBALS['log4php.defaultInitOverride']</code> global variable.
         * - defaults to <i>false</i>
         *
         * @var boolean
         */
        define('LOG4PHP_DEFAULT_INIT_OVERRIDE', false);
    }
}

if (!defined('LOG4PHP_CONFIGURATION')) {
    if (isset($_ENV['log4php.configuration'])) {
        /**
         * @ignore
         */
        define('LOG4PHP_CONFIGURATION', trim($_ENV['log4php.configuration']));
    } else {
        /**
         * Configuration file.
         *
         * <p>This constant tells configurator classes where the configuration
         * file is located.</p>
         * <p>If not set by user, log4php tries to set it automatically using 
         * (in order):</p>
         *
         * - the <code>$_ENV['log4php.configuration']</code> enviroment variable.
         * - defaults to 'log4php.properties'.
         *
         * @var string
         */
        define('LOG4PHP_CONFIGURATION', 'config/log4php.properties');
    }
}

if (!defined('LOG4PHP_CONFIGURATOR_CLASS')) {
    if ( strtolower(substr( LOG4PHP_CONFIGURATION, -4 )) == '.xml') { 
        /**
         * @ignore
         */
        define('LOG4PHP_CONFIGURATOR_CLASS', LOG4PHP_DIR . '/xml/LoggerDOMConfigurator');
    } else {
        /**
         * Holds the configurator class name.
         *
         * <p>This constant is set with the fullname (path included but non the 
         * .php extension) of the configurator class that init procedure will use.</p>
         * <p>If not set by user, log4php tries to set it automatically.</p>
         * <p>If {@link LOG4PHP_CONFIGURATION} has '.xml' extension set the 
         * constants to '{@link LOG4PHP_DIR}/xml/{@link LoggerDOMConfigurator}'.</p>
         * <p>Otherwise set the constants to 
         * '{@link LOG4PHP_DIR}/{@link LoggerPropertyConfigurator}'.</p>
         *
         * <p><b>Security Note</b>: classfile pointed by this constant will be brutally
         * included with a:
         * <code>@include_once(LOG4PHP_CONFIGURATOR_CLASS . ".php");</code></p>
         *
         * @var string
         */
        define('LOG4PHP_CONFIGURATOR_CLASS', LOG4PHP_DIR . '/LoggerPropertyConfigurator');
    }
}

if (!LOG4PHP_DEFAULT_INIT_OVERRIDE) {
    if (!LoggerManagerDefaultInit())
        LoggerLog::warn("LOG4PHP main() Default Init failed.");
}

/**
 * Default init procedure.
 *
 * <p>This procedure tries to configure the {@link LoggerHierarchy} using the
 * configurator class defined via {@link LOG4PHP_CONFIGURATOR_CLASS} that tries
 * to load the configurator file defined in {@link LOG4PHP_CONFIGURATION}.
 * If something goes wrong a warn is raised.</p>
 * <p>Users can skip this procedure using {@link LOG4PHP_DEFAULT_INIT_OVERRIDE}
 * constant.</p> 
 *
 * @return boolean
 */
function LoggerManagerDefaultInit()
{
    $configuratorClass = basename(LOG4PHP_CONFIGURATOR_CLASS);
    if (!class_exists($configuratorClass)) {
        @include_once(LOG4PHP_CONFIGURATOR_CLASS . ".php");
    }
    if (class_exists($configuratorClass)) {
        
        return call_user_func(array($configuratorClass, 'configure'), LOG4PHP_CONFIGURATION);         

    } else {
        LoggerLog::warn("LoggerManagerDefaultInit() Configurator '{$configuratorClass}' doesnt exists");
        return false;
    }
}
