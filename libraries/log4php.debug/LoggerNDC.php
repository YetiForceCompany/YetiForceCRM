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

define('LOGGER_NDC_HT_SIZE', 7);

/**
 * This is the global repository of NDC stack
 */
$GLOBALS['log4php.LoggerNDC.ht'] = array();

/**
 * This is the max depth of NDC stack
 */
$GLOBALS['log4php.LoggerNDC.maxDepth'] = LOGGER_NDC_HT_SIZE;


/**
 * The NDC class implements <i>nested diagnostic contexts</i> as
 * defined by Neil Harrison in the article "Patterns for Logging
 * Diagnostic Messages" part of the book "<i>Pattern Languages of
 * Program Design 3</i>" edited by Martin et al.
 *
 * <p>A Nested Diagnostic Context, or NDC in short, is an instrument
 * to distinguish interleaved log output from different sources. Log
 * output is typically interleaved when a server handles multiple
 * clients near-simultaneously.
 *
 * <p>Interleaved log output can still be meaningful if each log entry
 * from different contexts had a distinctive stamp. This is where NDCs
 * come into play.
 *
 * <p><i><b>Note that NDCs are managed on a per thread
 * basis</b></i>. NDC operations such as {@link push()}, {@link pop()}, 
 * {@link clear()}, {@link getDepth()} and {@link setMaxDepth()}
 * affect the NDC of the <i>current</i> thread only. NDCs of other
 * threads remain unaffected.
 *
 * <p>For example, a servlet can build a per client request NDC
 * consisting the clients host name and other information contained in
 * the the request. <i>Cookies</i> are another source of distinctive
 * information. To build an NDC one uses the {@link push()}
 * operation.</p>
 * 
 * Simply put,
 *
 * - Contexts can be nested.
 * - When entering a context, call 
 *   <code>LoggerNDC::push()</code>
 *   As a side effect, if there is no nested diagnostic context for the
 *   current thread, this method will create it.
 * - When leaving a context, call 
 *   <code>LoggerNDC::pop()</code>
 * - <b>When exiting a thread make sure to call {@link remove()}</b>
 *   
 * <p>There is no penalty for forgetting to match each
 * <code>push</code> operation with a corresponding <code>pop</code>,
 * except the obvious mismatch between the real application context
 * and the context set in the NDC.</p>
 *
 * <p>If configured to do so, {@link LoggerPatternLayout} and {@link LoggerLayoutTTCC} 
 * instances automatically retrieve the nested diagnostic
 * context for the current thread without any user intervention.
 * Hence, even if a servlet is serving multiple clients
 * simultaneously, the logs emanating from the same code (belonging to
 * the same category) can still be distinguished because each client
 * request will have a different NDC tag.</p>
 *
 *  
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.5 $
 * @package log4php 
 * @since 0.3
 */
class LoggerNDC {

    /**
     * Clear any nested diagnostic information if any. This method is
     * useful in cases where the same thread can be potentially used
     * over and over in different unrelated contexts.
     *
     * <p>This method is equivalent to calling the {@link setMaxDepth()}
     * method with a zero <var>maxDepth</var> argument.
     *
     * @static  
     */
    function clear()
    {
        LoggerLog::debug("LoggerNDC::clear()");
        
        $GLOBALS['log4php.LoggerNDC.ht'] = array();
    }

    /**
     * Never use this method directly, use the {@link LoggerLoggingEvent::getNDC()} method instead.
     * @static
     * @return array
     */
    function get()
    {
        LoggerLog::debug("LoggerNDC::get()");
    
        return $GLOBALS['log4php.LoggerNDC.ht'];
    }
  
    /**
     * Get the current nesting depth of this diagnostic context.
     *
     * @see setMaxDepth()
     * @return integer
     * @static
     */
    function getDepth()
    {
        LoggerLog::debug("LoggerNDC::getDepth()");
    
        return sizeof($GLOBALS['log4php.LoggerNDC.ht']);      
    }

    /**
     * Clients should call this method before leaving a diagnostic
     * context.
     *
     * <p>The returned value is the value that was pushed last. If no
     * context is available, then the empty string "" is returned.</p>
     *
     * @return string The innermost diagnostic context.
     * @static
     */
    function pop()
    {
        LoggerLog::debug("LoggerNDC::pop()");
    
        if (sizeof($GLOBALS['log4php.LoggerNDC.ht']) > 0) {
            return array_pop($GLOBALS['log4php.LoggerNDC.ht']);
        } else {
            return '';
        }
    }

    /**
     * Looks at the last diagnostic context at the top of this NDC
     * without removing it.
     *
     * <p>The returned value is the value that was pushed last. If no
     * context is available, then the empty string "" is returned.</p>
     * @return string The innermost diagnostic context.
     * @static
     */
    function peek()
    {
        LoggerLog::debug("LoggerNDC::peek()");
    
        if (sizeof($GLOBALS['log4php.LoggerNDC.ht']) > 0) {
            return end($GLOBALS['log4php.LoggerNDC.ht']);
        } else {
            return '';
        }
    }
  
    /**
     * Push new diagnostic context information for the current thread.
     *
     * <p>The contents of the <var>message</var> parameter is
     * determined solely by the client.
     *  
     * @param string $message The new diagnostic context information.
     * @static  
     */
    function push($message)
    {
        LoggerLog::debug("LoggerNDC::push()");
    
        array_push($GLOBALS['log4php.LoggerNDC.ht'], (string)$message);
    }

    /**
     * Remove the diagnostic context for this thread.
     * @static
     */
    function remove()
    {
        LoggerLog::debug("LoggerNDC::remove()");
    
        LoggerNDC::clear();
    }

    /**
     * Set maximum depth of this diagnostic context. If the current
     * depth is smaller or equal to <var>maxDepth</var>, then no
     * action is taken.
     *
     * <p>This method is a convenient alternative to multiple 
     * {@link pop()} calls. Moreover, it is often the case that at 
     * the end of complex call sequences, the depth of the NDC is
     * unpredictable. The {@link setMaxDepth()} method circumvents
     * this problem.
     *
     * @param integer $maxDepth
     * @see getDepth()
     * @static
     */
    function setMaxDepth($maxDepth)
    {
        LoggerLog::debug("LoggerNDC::setMaxDepth() maxDepth='$maxDepth'");
    
        $maxDepth = (int)$maxDepth;
        if ($maxDepth <= LOGGER_NDC_HT_SIZE) {
            if (LoggerNDC::getDepth() > $maxDepth) {
                $GLOBALS['log4php.LoggerNDC.ht'] = array_slice($GLOBALS['log4php.LoggerNDC.ht'], $maxDepth);
            }
            $GLOBALS['log4php.LoggerNDC.maxDepth'] = $maxDepth;            
        }
    }
  
}
?>