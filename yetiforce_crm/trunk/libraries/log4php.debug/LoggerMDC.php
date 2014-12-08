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


define('LOGGER_MDC_HT_SIZE', 7);

/**
 * This is the global repository of user mappings
 */
$GLOBALS['log4php.LoggerMDC.ht'] = array();

/**
 * The LoggerMDC class is similar to the {@link LoggerNDC} class except that it is
 * based on a map instead of a stack. It provides <i>mapped diagnostic contexts</i>.
 * 
 * A <i>Mapped Diagnostic Context</i>, or
 * MDC in short, is an instrument for distinguishing interleaved log
 * output from different sources. Log output is typically interleaved
 * when a server handles multiple clients near-simultaneously.
 *
 * <p><b><i>The MDC is managed on a per thread basis</i></b>.
 * 
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.4 $
 * @since 0.3
 * @package log4php
 */
class LoggerMDC {
  
    /**
     * Put a context value as identified with the key parameter into the current thread's
     *  context map.
     *
     * <p>If the current thread does not have a context map it is
     *  created as a side effect.</p>
     *
     * <p>Note that you cannot put more than {@link LOGGER_MDC_HT_SIZE} keys.</p>
     *
     * @param string $key the key
     * @param string $value the value
     * @static
     */
    function put($key, $value)
    {
        if ( sizeof($GLOBALS['log4php.LoggerMDC.ht']) < LOGGER_MDC_HT_SIZE ) 
            $GLOBALS['log4php.LoggerMDC.ht'][$key] = $value;
    }
  
    /**
     * Get the context identified by the key parameter.
     *
     * <p>You can use special key identifiers to map values in 
     * PHP $_SERVER and $_ENV vars. Just put a 'server.' or 'env.'
     * followed by the var name you want to refer.</p>
     *
     * <p>This method has no side effects.</p>
     *
     * @param string $key
     * @return string
     * @static
     */
    function get($key)
    {
        LoggerLog::debug("LoggerMDC::get() key='$key'");
    
        if (!empty($key)) {
            if (strpos($key, 'server.') === 0) {
                $varName = substr($key, 7);
                
                LoggerLog::debug("LoggerMDC::get() a _SERVER[$varName] is requested.");
                
                return $_SERVER[$varName];
            } elseif (strpos($key, 'env.') === 0) {

                $varName = substr($key, 4);
                
                LoggerLog::debug("LoggerMDC::get() a _ENV[$varName] is requested.");
                
                return $_ENV[$varName];
            } elseif (isset($GLOBALS['log4php.LoggerMDC.ht'][$key])) {
            
                LoggerLog::debug("LoggerMDC::get() a user key is requested.");
            
                return $GLOBALS['log4php.LoggerMDC.ht'][$key];
            }
        }
        return '';
    }

    /**
     * Remove the the context identified by the key parameter. 
     *
     * It only affects user mappings.
     *
     * @param string $key
     * @return string
     * @static
     */
    function remove($key)
    {
        unset($GLOBALS['log4php.LoggerMDC.ht'][$key]);
    }

}
?>