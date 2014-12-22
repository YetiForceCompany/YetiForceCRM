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

/**
 */
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * Array for fast space padding
 * Used by {@link LoggerPatternConverter::spacePad()}.  
 */
$GLOBALS['log4php.LoggerPatternConverter.spaces'] = array(" ", "  ", "    ", "        ", //1,2,4,8 spaces
			    "                ", // 16 spaces
			    "                                " ); // 32 spaces

/**
 * LoggerPatternConverter is an abstract class that provides the formatting 
 * functionality that derived classes need.
 * 
 * <p>Conversion specifiers in a conversion patterns are parsed to
 * individual PatternConverters. Each of which is responsible for
 * converting a logging event in a converter specific manner.</p>
 * 
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.13 $
 * @package log4php
 * @subpackage helpers
 * @abstract
 * @since 0.3
 */
class LoggerPatternConverter {

    /**
     * @var LoggerPatternConverter next converter in converter chain
     */
    var $next = null;
    
    var $min = -1;
    var $max = 0x7FFFFFFF;
    var $leftAlign = false;

    /**
     * Constructor 
     *
     * @param LoggerFormattingInfo $fi
     */
    function LoggerPatternConverter($fi = null) 
    {  
        if ($fi !== null) {
            $this->min = $fi->min;
            $this->max = $fi->max;
            $this->leftAlign = $fi->leftAlign;
        }
    }
  
    /**
     * Derived pattern converters must override this method in order to
     * convert conversion specifiers in the correct way.
     *
     * @param LoggerLoggingEvent $event
     */
    function convert($event) {}

    /**
     * A template method for formatting in a converter specific way.
     *
     * @param string &$sbuf string buffer
     * @param LoggerLoggingEvent $e
     */
    function format(&$sbuf, $e)
    {
        LoggerLog::debug("LoggerPatternConverter::format() sbuf='$sbuf'");    
    
        $s = $this->convert($e);
        
        LoggerLog::debug("LoggerPatternConverter::format() converted event is '$s'");    
        
    
        if($s == null or empty($s)) {
            if(0 < $this->min)
                $this->spacePad($sbuf, $this->min);
            return;
        }
        
        $len = strlen($s);
    
        if($len > $this->max) {
            $sbuf .= substr($s , 0, ($len - $this->max));
        } elseif($len < $this->min) {
            if($this->leftAlign) {	
                $sbuf .= $s;
                $this->spacePad($sbuf, ($this->min - $len));
            } else {
                $this->spacePad($sbuf, ($this->min - $len));
                $sbuf .= $s;
            }
        } else {
            $sbuf .= $s;
        }
    }	


    /**
     * Fast space padding method.
     *
     * @param string    &$sbuf     string buffer
     * @param integer   $length    pad length
     *
     * @todo reimplement using PHP string functions
     */
    function spacePad(&$sbuf, $length)
    {
        LoggerLog::debug("LoggerPatternConverter::spacePad() sbuf='$sbuf' len='$length'");        
    
        while($length >= 32) {
          $sbuf .= $GLOBALS['log4php.LoggerPatternConverter.spaces'][5];
          $length -= 32;
        }
        
        for($i = 4; $i >= 0; $i--) {	
            if(($length & (1<<$i)) != 0) {
    	        $sbuf .= $GLOBALS['log4php.LoggerPatternConverter.spaces'][$i];
            }
        }

        // $sbuf = str_pad($sbuf, $length);
    }
}

// ---------------------------------------------------------------------
//                      PatternConverters
// ---------------------------------------------------------------------

/**
 * @author VxR <vxr@vxr.it>
 * @package log4php
 * @subpackage helpers
 */
class LoggerBasicPatternConverter extends LoggerPatternConverter {

    /**
     * @var integer
     */
    var $type;

    /**
     * Constructor
     *
     * @param string $formattingInfo
     * @param integer $type
     */
    function LoggerBasicPatternConverter($formattingInfo, $type)
    {
      LoggerLog::debug("LoggerBasicPatternConverter::LoggerBasicPatternConverter() type='$type'");    
    
      $this->LoggerPatternConverter($formattingInfo);
      $this->type = $type;
    }

    /**
     * @param LoggerLoggingEvent $event
     * @return string
     */
    function convert($event)
    {
        switch($this->type) {
            case LOG4PHP_LOGGER_PATTERN_PARSER_RELATIVE_TIME_CONVERTER:
                $timeStamp = $event->getTimeStamp();
                $startTime = LoggerLoggingEvent::getStartTime();
	            return (string)(int)($timeStamp * 1000 - $startTime * 1000);
                
            case LOG4PHP_LOGGER_PATTERN_PARSER_THREAD_CONVERTER:
	            return $event->getThreadName();

            case LOG4PHP_LOGGER_PATTERN_PARSER_LEVEL_CONVERTER:
                $level = $event->getLevel();
	            return $level->toString();

            case LOG4PHP_LOGGER_PATTERN_PARSER_NDC_CONVERTER:
	            return $event->getNDC();

            case LOG4PHP_LOGGER_PATTERN_PARSER_MESSAGE_CONVERTER:
	            return $event->getRenderedMessage();
                
            default: 
                return '';
        }
    }
}

/**
 * @author VxR <vxr@vxr.it>
 * @package log4php
 * @subpackage helpers
 */
class LoggerLiteralPatternConverter extends LoggerPatternConverter {
    
    /**
     * @var string
     */
    var $literal;

    /**
     * Constructor
     *
     * @param string $value
     */
    function LoggerLiteralPatternConverter($value)
    {
        LoggerLog::debug("LoggerLiteralPatternConverter::LoggerLiteralPatternConverter() value='$value'");    
    
        $this->literal = $value;
    }

    /**
     * @param string &$sbuf
     * @param LoggerLoggingEvent $event
     */
    function format(&$sbuf, $event)
    {
        $sbuf .= $this->literal;
    }

    /**
     * @param LoggerLoggingEvent $event
     * @return string
     */
    function convert($event)
    {
      return $this->literal;
    }
}

/**
 * @author VxR <vxr@vxr.it>
 * @package log4php
 * @subpackage helpers
 */
class LoggerDatePatternConverter extends LoggerPatternConverter {

    /**
     * @var string
     */
    var $df;
    
    /**
     * Constructor
     *
     * @param string $formattingInfo
     * @param string $df
     */
    function LoggerDatePatternConverter($formattingInfo, $df)
    {
        LoggerLog::debug("LoggerDatePatternConverter::LoggerDatePatternConverter() dateFormat='$df'");    
    
        $this->LoggerPatternConverter($formattingInfo);
        $this->df = $df;
    }

    /**
     * @param LoggerLoggingEvent $event
     * @return string
     */
    function convert($event)
    {
        $timeStamp = $event->getTimeStamp();
        $usecs = round(($timeStamp - (int)$timeStamp) * 1000);
        $this->df = str_replace("\u", "u", ereg_replace("[^\\]u", sprintf(',%03d', $usecs), $this->df));
         
        return date($this->df, $event->getTimeStamp());
        
    }
}

/**
 * @author VxR <vxr@vxr.it>
 * @package log4php
 * @subpackage helpers
 */
class LoggerMDCPatternConverter extends LoggerPatternConverter {

    /**
     * @var string
     */
    var $key;

    /**
     * Constructor
     *
     * @param string $formattingInfo
     * @param string $key
     */
    function LoggerMDCPatternConverter($formattingInfo, $key)
    {
      LoggerLog::debug("LoggerMDCPatternConverter::LoggerMDCPatternConverter() key='$key'");    

      $this->LoggerPatternConverter($formattingInfo);
      $this->key = $key;
    }

    /**
     * @param LoggerLoggingEvent $event
     * @return string
     */
    function convert($event)
    {
        return $event->getMDC($this->key);
    }
}

/**
 * @author VxR <vxr@vxr.it>
 * @package log4php
 * @subpackage helpers
 */
class LoggerLocationPatternConverter extends LoggerPatternConverter {
    
    /**
     * @var integer
     */
    var $type;

    /**
     * Constructor
     *
     * @param string $formattingInfo
     * @param integer $type
     */
    function LoggerLocationPatternConverter($formattingInfo, $type)
    {
      LoggerLog::debug("LoggerLocationPatternConverter::LoggerLocationPatternConverter() type='$type'");    
    
      $this->LoggerPatternConverter($formattingInfo);
      $this->type = $type;
    }

    /**
     * @param LoggerLoggingEvent $event
     * @return string
     */
    function convert($event)
    {
        $locationInfo = $event->getLocationInformation();
        switch($this->type) {
            case LOG4PHP_LOGGER_PATTERN_PARSER_FULL_LOCATION_CONVERTER:
	            return $locationInfo->fullInfo;
            case LOG4PHP_LOGGER_PATTERN_PARSER_METHOD_LOCATION_CONVERTER:
	            return $locationInfo->getMethodName();
            case LOG4PHP_LOGGER_PATTERN_PARSER_LINE_LOCATION_CONVERTER:
	            return $locationInfo->getLineNumber();
            case LOG4PHP_LOGGER_PATTERN_PARSER_FILE_LOCATION_CONVERTER:
	            return $locationInfo->getFileName();
            default: 
                return '';
        }
    }
}

/**
 * @author VxR <vxr@vxr.it>
 * @package log4php
 * @subpackage helpers
 * @abstract
 */
class LoggerNamedPatternConverter extends LoggerPatternConverter {

    /**
     * @var integer
     */
    var $precision;

    /**
     * Constructor
     *
     * @param string $formattingInfo
     * @param integer $precision
     */
    function LoggerNamedPatternConverter($formattingInfo, $precision)
    {
      LoggerLog::debug("LoggerNamedPatternConverter::LoggerNamedPatternConverter() precision='$precision'");    
    
      $this->LoggerPatternConverter($formattingInfo);
      $this->precision =  $precision;
    }

    /**
     * @param LoggerLoggingEvent $event
     * @return string
     * @abstract
     */
    function getFullyQualifiedName($event)
    { 
        // abstract
        return;
    }

    /**
     * @param LoggerLoggingEvent $event
     * @return string
     */
    function convert($event)
    {
        $n = $this->getFullyQualifiedName($event);
        if ($this->precision <= 0) {
	        return $n;
        } else {
	        $len = strlen($n);
            
        	// We substract 1 from 'len' when assigning to 'end' to avoid out of
        	// bounds exception in return r.substring(end+1, len). This can happen if
        	// precision is 1 and the category name ends with a dot.
        	$end = $len -1 ;
        	for($i = $this->precision; $i > 0; $i--) {
        	    $end = strrpos(substr($n, 0, ($end - 1)), '.');
        	    if ($end == false)
        	        return $n;
        	}
        	return substr($n, ($end + 1), $len);
        }
    }
}

/**
 * @author VxR <vxr@vxr.it>
 * @package log4php
 * @subpackage helpers
 */
class LoggerClassNamePatternConverter extends LoggerNamedPatternConverter {

    /**
     * Constructor
     *
     * @param string $formattingInfo
     * @param integer $precision
     */
    function LoggerClassNamePatternConverter($formattingInfo, $precision)
    {
        LoggerLog::debug("LoggerClassNamePatternConverter::LoggerClassNamePatternConverter() precision='$precision'");    
    
        $this->LoggerNamedPatternConverter($formattingInfo, $precision);
    }

    /**
     * @param LoggerLoggingEvent $event
     * @return string
     */
    function getFullyQualifiedName($event)
    {
        return $event->fqcn;
    }
}

/**
 * @author VxR <vxr@vxr.it>
 * @package log4php
 * @subpackage helpers
 */
class LoggerCategoryPatternConverter extends LoggerNamedPatternConverter {

    /**
     * Constructor
     *
     * @param string $formattingInfo
     * @param integer $precision
     */
    function LoggerCategoryPatternConverter($formattingInfo, $precision)
    {
        LoggerLog::debug("LoggerCategoryPatternConverter::LoggerCategoryPatternConverter() precision='$precision'");    
    
        $this->LoggerNamedPatternConverter($formattingInfo, $precision);
    }

    /**
     * @param LoggerLoggingEvent $event
     * @return string
     */
    function getFullyQualifiedName($event)
    {
      return $event->getLoggerName();
    }
}

?>