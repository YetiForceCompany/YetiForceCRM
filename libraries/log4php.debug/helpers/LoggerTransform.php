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
 * @subpackage helpers
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');
 
define('LOG4PHP_LOGGER_TRANSFORM_CDATA_START',          '<![CDATA[');
define('LOG4PHP_LOGGER_TRANSFORM_CDATA_END',            ']]>');
define('LOG4PHP_LOGGER_TRANSFORM_CDATA_PSEUDO_END',     ']]&gt;');
define('LOG4PHP_LOGGER_TRANSFORM_CDATA_EMBEDDED_END',   
    LOG4PHP_LOGGER_TRANSFORM_CDATA_END .
    LOG4PHP_LOGGER_TRANSFORM_CDATA_PSEUDO_END .
    LOG4PHP_LOGGER_TRANSFORM_CDATA_START 
);

/**
 * Utility class for transforming strings.
 *
 * @log4j-class org.apache.log4j.helpers.Transform
 *
 * @author VxR <vxr@vxr.it>
 * @package log4php
 * @subpackage helpers
 * @since 0.7
 */
class LoggerTransform {

    /**
    * This method takes a string which may contain HTML tags (ie,
    * &lt;b&gt;, &lt;table&gt;, etc) and replaces any '&lt;' and '&gt;'
    * characters with respective predefined entity references.
    *
    * @param string $input The text to be converted.
    * @return string The input string with the characters '&lt;' and '&gt;' replaced with
    *                &amp;lt; and &amp;gt; respectively.
    * @static  
    */
    function escapeTags($input)
    {
        //Check if the string is null or zero length -- if so, return
        //what was sent in.

        if(empty($input))
            return $input;

        //Use a StringBuffer in lieu of String concatenation -- it is
        //much more efficient this way.

        return htmlspecialchars($input, ENT_NOQUOTES);
    }

    /**
    * Ensures that embeded CDEnd strings (]]&gt;) are handled properly
    * within message, NDC and throwable tag text.
    *
    * @param string $buf    String holding the XML data to this point.  The
    *                       initial CDStart (<![CDATA[) and final CDEnd (]]>) 
    *                       of the CDATA section are the responsibility of 
    *                       the calling method.
    * @param string &str    The String that is inserted into an existing 
    *                       CDATA Section within buf.
    * @static  
    */
    function appendEscapingCDATA(&$buf, $str)
    {
        if(empty($str))
            return;
    
        $rStr = str_replace(
            LOG4PHP_LOGGER_TRANSFORM_CDATA_END,
            LOG4PHP_LOGGER_TRANSFORM_CDATA_EMBEDDED_END,
            $str
        );
        $buf .= $rStr;
    }
}
?>