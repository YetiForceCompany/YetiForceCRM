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

require_once(LOG4PHP_DIR . '/config/LoggerPropertySetter.php');
require_once(LOG4PHP_DIR . '/helpers/LoggerOptionConverter.php');
require_once(LOG4PHP_DIR . '/or/LoggerObjectRenderer.php');
require_once(LOG4PHP_DIR . '/or/LoggerRendererMap.php');
require_once(LOG4PHP_DIR . '/spi/LoggerConfigurator.php');
require_once(LOG4PHP_DIR . '/spi/LoggerFilter.php');
require_once(LOG4PHP_DIR . '/LoggerAppender.php');
require_once(LOG4PHP_DIR . '/LoggerDefaultCategoryFactory.php');
require_once(LOG4PHP_DIR . '/LoggerLayout.php');
require_once(LOG4PHP_DIR . '/LoggerLevel.php');
require_once(LOG4PHP_DIR . '/LoggerManager.php');

define('LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_CATEGORY_PREFIX',      "log4php.category.");
define('LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_PREFIX',        "log4php.logger.");
define('LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_FACTORY_PREFIX',       "log4php.factory");
define('LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_ADDITIVITY_PREFIX',    "log4php.additivity.");
define('LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_ROOT_CATEGORY_PREFIX', "log4php.rootCategory");
define('LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_ROOT_LOGGER_PREFIX',   "log4php.rootLogger");
define('LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_APPENDER_PREFIX',      "log4php.appender.");
define('LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_RENDERER_PREFIX',      "log4php.renderer.");
define('LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_THRESHOLD_PREFIX',     "log4php.threshold");

/** 
 * Key for specifying the {@link LoggerFactory}.  
 */
define('LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_FACTORY_KEY',   "log4php.loggerFactory");
define('LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_DEBUG_KEY',     "log4php.debug");
define('LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_INTERNAL_ROOT_NAME',   "root");



/**
 * Allows the configuration of log4php from an external file.
 * 
 * See {@link doConfigure()} for the expected format.
 * 
 * <p>It is sometimes useful to see how log4php is reading configuration
 * files. You can enable log4php internal logging by defining the
 * <b>log4php.debug</b> variable.</p>
 *
 * <p>The <i>LoggerPropertyConfigurator</i> does not handle the
 * advanced configuration features supported by the {@link LoggerDOMConfigurator} 
 * such as support for {@link LoggerFilter}, 
   custom {@link LoggerErrorHandlers}, nested appenders such as the 
   {@link Logger AsyncAppender}, 
 * etc.
 * 
 * <p>All option <i>values</i> admit variable substitution. The
 * syntax of variable substitution is similar to that of Unix
 * shells. The string between an opening <b>&quot;${&quot;</b> and
 * closing <b>&quot;}&quot;</b> is interpreted as a key. The value of
 * the substituted variable can be defined as a system property or in
 * the configuration file itself. The value of the key is first
 * searched in the defined constants, in the enviroments variables
 * and if not found there, it is
 * then searched in the configuration file being parsed.  The
 * corresponding value replaces the ${variableName} sequence.</p>
 * <p>For example, if <b>$_ENV['home']</b> env var is set to
 * <b>/home/xyz</b>, then every occurrence of the sequence
 * <b>${home}</b> will be interpreted as
 * <b>/home/xyz</b>. See {@link LoggerOptionConverter::getSystemProperty()}
 * for details.</p>
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.6 $
 * @package log4php
 * @since 0.5 
 */
class LoggerPropertyConfigurator extends LoggerConfigurator {

    /**
     * @var LoggerFactory
     */
    var $loggerFactory = null;
    
    /**
     * Constructor
     */
    function LoggerPropertyConfigurator()
    {
        $this->loggerFactory = new LoggerDefaultCategoryFactory();
    }
    
    /**
     * Configure the default repository using the resource pointed by <b>url</b>.
     * <b>Url</b> is any valid resurce as defined in {@link PHP_MANUAL#file} function.
     * Note that the resource will be search with <i>use_include_path</i> parameter 
     * set to "1".
     *
     * @param string $url
     * @return boolean configuration result
     * @static
     */
    function configure($url = '')
    {
        $configurator = new LoggerPropertyConfigurator();
        $repository =& LoggerManager::getLoggerRepository();
        return $configurator->doConfigure($url, $repository);
    }

    /**
     * Read configuration from a file.
     *
     * <p>The function {@link PHP_MANUAL#parse_ini_file} is used to read the
     * file.</p>
     *
     * <b>The existing configuration is not cleared nor reset.</b> 
     * If you require a different behavior, then call 
     * {@link  LoggerManager::resetConfiguration()} 
     * method before calling {@link doConfigure()}.
     * 
     * <p>The configuration file consists of statements in the format
     * <b>key=value</b>. The syntax of different configuration
     * elements are discussed below.
     * 
     * <p><b>Repository-wide threshold</b></p>
     * 
     * <p>The repository-wide threshold filters logging requests by level
     * regardless of logger. The syntax is:
     * 
     * <pre>
     * log4php.threshold=[level]
     * </pre>
     * 
     * <p>The level value can consist of the string values OFF, FATAL,
     * ERROR, WARN, INFO, DEBUG, ALL or a <i>custom level</i> value. A
     * custom level value can be specified in the form
     * <samp>level#classname</samp>. By default the repository-wide threshold is set
     * to the lowest possible value, namely the level <b>ALL</b>.
     * </p>
     * 
     * 
     * <p><b>Appender configuration</b></p>
     * 
     * <p>Appender configuration syntax is:</p>
     * <pre>
     * ; For appender named <i>appenderName</i>, set its class.
     * ; Note: The appender name can contain dots.
     * log4php.appender.appenderName=name_of_appender_class
     * 
     * ; Set appender specific options.
     * 
     * log4php.appender.appenderName.option1=value1
     * log4php.appender.appenderName.optionN=valueN
     * </pre>
     * 
     * For each named appender you can configure its {@link LoggerLayout}. The
     * syntax for configuring an appender's layout is:
     * <pre>
     * log4php.appender.appenderName.layout=name_of_layout_class
     * log4php.appender.appenderName.layout.option1=value1
     *  ....
     * log4php.appender.appenderName.layout.optionN=valueN
     * </pre>
     * 
     * <p><b>Configuring loggers</b></p>
     * 
     * <p>The syntax for configuring the root logger is:
     * <pre>
     * log4php.rootLogger=[level], appenderName, appenderName, ...
     * </pre>
     * 
     * <p>This syntax means that an optional <i>level</i> can be
     * supplied followed by appender names separated by commas.
     * 
     * <p>The level value can consist of the string values OFF, FATAL,
     * ERROR, WARN, INFO, DEBUG, ALL or a <i>custom level</i> value. A
     * custom level value can be specified in the form</p>
     *
     * <pre>level#classname</pre>
     * 
     * <p>If a level value is specified, then the root level is set
     * to the corresponding level.  If no level value is specified,
     * then the root level remains untouched.
     * 
     * <p>The root logger can be assigned multiple appenders.
     * 
     * <p>Each <i>appenderName</i> (separated by commas) will be added to
     * the root logger. The named appender is defined using the
     * appender syntax defined above.
     * 
     * <p>For non-root categories the syntax is almost the same:
     * <pre>
     * log4php.logger.logger_name=[level|INHERITED|NULL], appenderName, appenderName, ...
     * </pre>
     * 
     * <p>The meaning of the optional level value is discussed above
     * in relation to the root logger. In addition however, the value
     * INHERITED can be specified meaning that the named logger should
     * inherit its level from the logger hierarchy.</p>
     * 
     * <p>If no level value is supplied, then the level of the
     * named logger remains untouched.</p>
     * 
     * <p>By default categories inherit their level from the
     * hierarchy. However, if you set the level of a logger and later
     * decide that that logger should inherit its level, then you should
     * specify INHERITED as the value for the level value. NULL is a
     * synonym for INHERITED.</p>
     * 
     * <p>Similar to the root logger syntax, each <i>appenderName</i>
     * (separated by commas) will be attached to the named logger.</p>
     * 
     * <p>See the <i>appender additivity rule</i> in the user manual for 
     * the meaning of the <b>additivity</b> flag.
     * 
     * <p><b>ObjectRenderers</b></p>
     * 
     * <p>You can customize the way message objects of a given type are
     * converted to String before being logged. This is done by
     * specifying a {@link LoggerObjectRenderer}
     * for the object type would like to customize.</p>
     * 
     * <p>The syntax is:
     * 
     * <pre>
     * log4php.renderer.name_of_rendered_class=name_of_rendering.class
     * </pre>
     * 
     * As in,
     * <pre>
     * log4php.renderer.myFruit=myFruitRenderer
     * </pre>
     * 
     * <p><b>Logger Factories</b></p>
     * 
     * The usage of custom logger factories is discouraged and no longer
     * documented.
     * 
     * <p><b>Example</b></p>
     * 
     * <p>An example configuration is given below. Other configuration
     * file examples are given in the <b>tests</b> folder.
     * 
     * <pre>
     * ; Set options for appender named "A1".
     * ; Appender "A1" will be a SyslogAppender
     * log4php.appender.A1=SyslogAppender
     * 
     * ; The syslog daemon resides on www.abc.net
     * log4php.appender.A1.SyslogHost=www.abc.net
     * 
     * ; A1's layout is a LoggerPatternLayout, using the conversion pattern
     * ; <b>%r %-5p %c{2} %M.%L %x - %m%n</b>. Thus, the log output will
     * ; include the relative time since the start of the application in
     * ; milliseconds, followed by the level of the log request,
     * ; followed by the two rightmost components of the logger name,
     * ; followed by the callers method name, followed by the line number,
     * ; the nested disgnostic context and finally the message itself.
     * ; Refer to the documentation of LoggerPatternLayout} for further information
     * ; on the syntax of the ConversionPattern key.
     * log4php.appender.A1.layout=LoggerPatternLayout
     * log4php.appender.A1.layout.ConversionPattern="%-4r %-5p %c{2} %M.%L %x - %m%n"
     * 
     * ; Set options for appender named "A2"
     * ; A2 should be a LoggerAppenderRollingFile, with maximum file size of 10 MB
     * ; using at most one backup file. A2's layout is TTCC, using the
     * ; ISO8061 date format with context printing enabled.
     * log4php.appender.A2=LoggerAppenderRollingFile
     * log4php.appender.A2.MaxFileSize=10MB
     * log4php.appender.A2.MaxBackupIndex=1
     * log4php.appender.A2.layout=LoggerLayoutTTCC
     * log4php.appender.A2.layout.ContextPrinting="true"
     * log4php.appender.A2.layout.DateFormat="%c"
     * 
     * ; Root logger set to DEBUG using the A2 appender defined above.
     * log4php.rootLogger=DEBUG, A2
     * 
     * ; Logger definitions:
     * ; The SECURITY logger inherits is level from root. However, it's output
     * ; will go to A1 appender defined above. It's additivity is non-cumulative.
     * log4php.logger.SECURITY=INHERIT, A1
     * log4php.additivity.SECURITY=false
     * 
     * ; Only warnings or above will be logged for the logger "SECURITY.access".
     * ; Output will go to A1.
     * log4php.logger.SECURITY.access=WARN
     * 
     * 
     * ; The logger "class.of.the.day" inherits its level from the
     * ; logger hierarchy.  Output will go to the appender's of the root
     * ; logger, A2 in this case.
     * log4php.logger.class.of.the.day=INHERIT
     * </pre>
     * 
     * <p>Refer to the <b>setOption</b> method in each Appender and
     * Layout for class specific options.</p>
     * 
     * <p>Use the <b>&quot;;&quot;</b> character at the
     * beginning of a line for comments.</p>
     * 
     * @param string $url The name of the configuration file where the
     *                    configuration information is stored.
     * @param LoggerHierarchy &$repository the repository to apply the configuration
     */
    function doConfigure($url, &$repository)
    {
        $properties = @parse_ini_file($url);
        if ($properties === false) {
            LoggerLog::warn("LoggerPropertyConfigurator::doConfigure() cannot load '$url' configuration.");
            return false; 
        }
        return $this->doConfigureProperties($properties, $repository);
    }


    /**
     * Read configuration options from <b>properties</b>.
     *
     * @see doConfigure().
     * @param array $properties
     * @param LoggerHierarchy &$hierarchy
     */
    function doConfigureProperties($properties, &$hierarchy)
    {
        $value = @$properties[LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_DEBUG_KEY];
        
        if (!empty($value)) {
            LoggerLog::internalDebugging(LoggerOptionConverter::toBoolean($value, LoggerLog::internalDebugging()));
        }

        $thresholdStr = @$properties[LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_THRESHOLD_PREFIX];
        $hierarchy->setThreshold(LoggerOptionConverter::toLevel($thresholdStr, LoggerLevel::getLevelAll()));
        
        $this->configureRootCategory($properties, $hierarchy);
        $this->configureLoggerFactory($properties);
        $this->parseCatsAndRenderers($properties, $hierarchy);

        LoggerLog::debug("LoggerPropertyConfigurator::doConfigureProperties() Finished configuring.");
        
        return true;
    }

    // --------------------------------------------------------------------------
    // Internal stuff
    // --------------------------------------------------------------------------

    /**
     * Check the provided <b>Properties</b> object for a
     * {@link LoggerFactory} entry specified by 
     * {@link LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_FACTORY_KEY}.
     *  
     * If such an entry exists, an attempt is made to create an instance using 
     * the default constructor.  
     * This instance is used for subsequent Category creations
     * within this configurator.
     *
     * @see parseCatsAndRenderers()
     * @param array $props array of properties
     */
    function configureLoggerFactory($props)
    {
        $factoryFqcn = @$props[LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_FACTORY_KEY];
        if(!empty($factoryFqcn)) {
            $factoryClassName = basename($factoryFqcn);
            LoggerLog::debug(
                "LoggerPropertyConfigurator::configureLoggerFactory() Trying to load factory [" .
                $factoryClassName . 
                "]."
            );
            
            if (!class_exists($factoryClassName))
                @include_once("{$factoryFqcn}.php");
            if (class_exists($factoryClassName)) {
                $loggerFactory = new $factoryClassName();
            } else {
                LoggerLog::debug(
                    "LoggerPropertyConfigurator::configureLoggerFactory() Unable to load factory [" .
                    $factoryClassName . 
                    "]. Using default."
                );
                $loggerFactory = $this->loggerFactory;
            }

            LoggerLog::debug(
                "LoggerPropertyConfigurator::configureLoggerFactory() ".
                "Setting properties for category factory [" . get_class($loggerFactory) . "]."
            );
            
            LoggerPropertySetter::setPropertiesByObject($loggerFactory, $props, LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_FACTORY_PREFIX . ".");
        }
    }
    
    /**
     * @param array $props array of properties
     * @param LoggerHierarchy &$hierarchy
     */
    function configureRootCategory($props, &$hierarchy)
    {
        $effectivePrefix = LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_ROOT_LOGGER_PREFIX;
        $value = @$props[LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_ROOT_LOGGER_PREFIX];

        if(empty($value)) {
            $value = @$props[LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_ROOT_CATEGORY_PREFIX];
            $effectivePrefix = LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_ROOT_CATEGORY_PREFIX;
        }

        if (empty($value)) {
            LoggerLog::debug(
                "LoggerPropertyConfigurator::configureRootCategory() ".
                "Could not find root logger information. Is this OK?"
            );
        } else {
            $root =& $hierarchy->getRootLogger();
            // synchronized(root) {
        	$this->parseCategory(
                $props, 
                $root, 
                $effectivePrefix, 
                LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_INTERNAL_ROOT_NAME, 
                $value
            );
            // }
        }
    }

    /**
     * Parse non-root elements, such non-root categories and renderers.
     *
     * @param array $props array of properties
     * @param LoggerHierarchy &$hierarchy
     */
    function parseCatsAndRenderers($props, &$hierarchy)
    {
        while(list($key,$value) = each($props)) {
            if( strpos($key, LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_CATEGORY_PREFIX) === 0 or 
                strpos($key, LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_PREFIX) === 0) {
	            if(strpos($key, LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_CATEGORY_PREFIX) === 0) {
                    $loggerName = substr($key, strlen(LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_CATEGORY_PREFIX));
	            } elseif (strpos($key, LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_PREFIX) === 0) {
                    $loggerName = substr($key, strlen(LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_LOGGER_PREFIX));
                }
                $logger =& $hierarchy->getLogger($loggerName, $this->loggerFactory);
	            // synchronized(logger) {
	            $this->parseCategory($props, $logger, $key, $loggerName, $value);
        	    $this->parseAdditivityForLogger($props, $logger, $loggerName);
	            // }
            } elseif (strpos($key, LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_RENDERER_PREFIX) === 0) {
                $renderedClass = substr($key, strlen(LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_RENDERER_PREFIX));
	            $renderingClass = $value;
            	if (method_exists($hierarchy, 'addrenderer')) {
	                LoggerRendererMap::addRenderer($hierarchy, $renderedClass, $renderingClass);
                }
	        }
        }
    }

    /**
     * Parse the additivity option for a non-root category.
     *
     * @param array $props array of properties
     * @param Logger &$cat
     * @param string $loggerName
     */
    function parseAdditivityForLogger($props, &$cat, $loggerName)
    {
        $value = LoggerOptionConverter::findAndSubst(
                    LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_ADDITIVITY_PREFIX . $loggerName,
					$props
                 );
        LoggerLog::debug(
            "LoggerPropertyConfigurator::parseAdditivityForLogger() ".
            "Handling " . LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_ADDITIVITY_PREFIX . $loggerName . "=[{$value}]"
        );
        // touch additivity only if necessary
        if(!empty($value)) {
            $additivity = LoggerOptionConverter::toBoolean($value, true);
            LoggerLog::debug(
                "LoggerPropertyConfigurator::parseAdditivityForLogger() ".
                "Setting additivity for [{$loggerName}] to [{$additivity}]"
            );
            $cat->setAdditivity($additivity);
        }
    }

    /**
     * This method must work for the root category as well.
     *
     * @param array $props array of properties
     * @param Logger &$logger
     * @param string $optionKey
     * @param string $loggerName
     * @param string $value
     * @return Logger
     */
    function &parseCategory($props, &$logger, $optionKey, $loggerName, $value)
    {
        LoggerLog::debug(
            "LoggerPropertyConfigurator::parseCategory() ".
            "Parsing for [{$loggerName}] with value=[{$value}]."
        );
        
        // We must skip over ',' but not white space
        $st = explode(',', $value);

        // If value is not in the form ", appender.." or "", then we should set
        // the level of the loggeregory.

        if(!(@$value{0} == ',' || empty($value))) {
            // just to be on the safe side...
            if(sizeof($st) == 0)
	            return;
                
            $levelStr = current($st);
            LoggerLog::debug(
                "LoggerPropertyConfigurator::parseCategory() ".
                "Level token is [$levelStr]."
            );

            // If the level value is inherited, set category level value to
            // null. We also check that the user has not specified inherited for the
            // root category.
            if('INHERITED' == strtoupper($levelStr) || 'NULL' == strtoupper($levelStr)) {
        	    if ($loggerName == LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_INTERNAL_ROOT_NAME) {
                    LoggerLog::warn(
                        "LoggerPropertyConfigurator::parseCategory() ".
                        "The root logger cannot be set to null."
                    );
	            } else {
	                $logger->setLevel(null);
	            }
            } else {
	            $logger->setLevel(LoggerOptionConverter::toLevel($levelStr, LoggerLevel::getLevelDebug()));
            }
        }

        // Begin by removing all existing appenders.
        $logger->removeAllAppenders();
        while($appenderName = next($st)) {
            $appenderName = trim($appenderName);
            if(empty($appenderName))
                continue;
            LoggerLog::debug(
                "LoggerPropertyConfigurator::parseCategory() ".
                "Parsing appender named [{$appenderName}]."
            );
            $appender =& $this->parseAppender($props, $appenderName);
            if($appender !== null) {
	            $logger->addAppender($appender);
            }
        }
    }

    /**
     * @param array $props array of properties
     * @param string $appenderName
     * @return LoggerAppender
     */
    function &parseAppender($props, $appenderName)
    {
        $appender =& LoggerAppender::singleton($appenderName);
        if($appender !== null) {
            LoggerLog::debug(
                "LoggerPropertyConfigurator::parseAppender() ".
                "Appender [{$appenderName}] was already parsed."
            );
            return $appender;
        }
        // Appender was not previously initialized.
        $prefix = LOG4PHP_LOGGER_PROPERTY_CONFIGURATOR_APPENDER_PREFIX . $appenderName;
        $layoutPrefix = $prefix . ".layout";
        $appenderClass = @$props[$prefix];
        if (!empty($appenderClass)) {
            $appender =& LoggerAppender::singleton($appenderName, $appenderClass);
            if($appender === null) {
                LoggerLog::warn(
                    "LoggerPropertyConfigurator::parseAppender() ".
                    "Could not instantiate appender named [$appenderName]."
                );
                return null;
            }
        } else {
            LoggerLog::warn(
                "LoggerPropertyConfigurator::parseAppender() ".
                "Could not instantiate appender named [$appenderName] with null className."
            );
            return null;
        }
        
        $appender->setName($appenderName);
        if( $appender->requiresLayout() ) {
            LoggerLog::debug(
                "LoggerPropertyConfigurator::parseAppender() ".
                "Parsing layout section for [$appenderName]."
            );
            $layoutClass = @$props[$layoutPrefix];
            $layoutClass = LoggerOptionConverter::substVars($layoutClass, $props);
            if (empty($layoutClass)) {
                LoggerLog::warn(
                    "LoggerPropertyConfigurator::parseAppender() ".
                    "layout class is empty in '$layoutPrefix'. Using Simple layout"
                );
                $layout = LoggerLayout::factory('LoggerLayoutSimple');
            } else {
        	    $layout = LoggerLayout::factory($layoutClass);
                
	            if($layout === null) {
	                LoggerLog::warn(
                        "LoggerPropertyConfigurator::parseAppender() ".
                        "cannot create layout '$layoutClass'. Using Simple layout"
                    );
                    $layout = LoggerLayout::factory('LoggerLayoutSimple');
                }
            }
            
            LoggerLog::debug(
                "LoggerPropertyConfigurator::parseAppender() ".
                "Parsing layout options for [$appenderName]."
            );
            LoggerPropertySetter::setPropertiesByObject($layout, $props, $layoutPrefix . ".");                
            LoggerLog::debug(
                "LoggerPropertyConfigurator::parseAppender() ".
                "End Parsing layout options for [$appenderName]."
            );
            $appender->setLayout($layout);
            
        }
        LoggerPropertySetter::setPropertiesByObject($appender, $props, $prefix . ".");
        LoggerLog::debug(
            "LoggerPropertyConfigurator::parseAppender() ".        
            "Parsed [{$appenderName}] options."
        );
        return $appender;        
    }

}
?>