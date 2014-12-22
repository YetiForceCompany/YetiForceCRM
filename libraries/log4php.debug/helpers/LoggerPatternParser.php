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

if (!defined('LOG4PHP_LINE_SEP')) {
    if (substr(php_uname(), 0, 7) == "Windows") {
        /**
         * @ignore
         */
        define('LOG4PHP_LINE_SEP', "\r\n");
    } else {
        /**
         * @ignore
         */
        define('LOG4PHP_LINE_SEP', "\n");
    }
}
 
/**
 */
require_once(LOG4PHP_DIR . '/helpers/LoggerFormattingInfo.php');
require_once(LOG4PHP_DIR . '/helpers/LoggerPatternConverter.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');

define('LOG4PHP_LOGGER_PATTERN_PARSER_ESCAPE_CHAR',         '%');

define('LOG4PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE',       0);
define('LOG4PHP_LOGGER_PATTERN_PARSER_CONVERTER_STATE',     1);
define('LOG4PHP_LOGGER_PATTERN_PARSER_MINUS_STATE',         2);
define('LOG4PHP_LOGGER_PATTERN_PARSER_DOT_STATE',           3);
define('LOG4PHP_LOGGER_PATTERN_PARSER_MIN_STATE',           4);
define('LOG4PHP_LOGGER_PATTERN_PARSER_MAX_STATE',           5);

define('LOG4PHP_LOGGER_PATTERN_PARSER_FULL_LOCATION_CONVERTER',         1000);
define('LOG4PHP_LOGGER_PATTERN_PARSER_METHOD_LOCATION_CONVERTER',       1001);
define('LOG4PHP_LOGGER_PATTERN_PARSER_CLASS_LOCATION_CONVERTER',        1002);
define('LOG4PHP_LOGGER_PATTERN_PARSER_FILE_LOCATION_CONVERTER',         1003);
define('LOG4PHP_LOGGER_PATTERN_PARSER_LINE_LOCATION_CONVERTER',         1004);

define('LOG4PHP_LOGGER_PATTERN_PARSER_RELATIVE_TIME_CONVERTER',         2000);
define('LOG4PHP_LOGGER_PATTERN_PARSER_THREAD_CONVERTER',                2001);
define('LOG4PHP_LOGGER_PATTERN_PARSER_LEVEL_CONVERTER',                 2002);
define('LOG4PHP_LOGGER_PATTERN_PARSER_NDC_CONVERTER',                   2003);
define('LOG4PHP_LOGGER_PATTERN_PARSER_MESSAGE_CONVERTER',               2004);

define('LOG4PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_ISO8601',    'Y-m-d H:i:s,u'); 
define('LOG4PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_ABSOLUTE',   'H:i:s');
define('LOG4PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_DATE',       'd M Y H:i:s,u');

/**
 * Most of the work of the {@link LoggerPatternLayout} class 
 * is delegated to the {@link LoggerPatternParser} class.
 * 
 * <p>It is this class that parses conversion patterns and creates
 * a chained list of {@link LoggerPatternConverter} converters.</p>
 * 
 * @author VxR <vxr@vxr.it>
 * @version $Revision: 1.10 $ 
 * @package log4php
 * @subpackage helpers
 *
 * @since 0.3
 */
class LoggerPatternParser {

    var $state;
    var $currentLiteral;
    var $patternLength;
    var $i;
    
    /**
     * @var LoggerPatternConverter
     */
    var $head = null;
     
    /**
     * @var LoggerPatternConverter
     */
    var $tail = null;
    
    /**
     * @var LoggerFormattingInfo
     */
    var $formattingInfo;
    
    /**
     * @var string pattern to parse
     */
    var $pattern;

    /**
     * Constructor 
     *
     * @param string $pattern
     */
    function LoggerPatternParser($pattern)
    {
        LoggerLog::debug("LoggerPatternParser::LoggerPatternParser() pattern='$pattern'");
    
        $this->pattern = $pattern;
        $this->patternLength =  strlen($pattern);
        $this->formattingInfo = new LoggerFormattingInfo();
        $this->state = LOG4PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE;
    }

    /**
     * @param LoggerPatternConverter $pc
     */
    function addToList($pc)
    {
        // LoggerLog::debug("LoggerPatternParser::addToList()");
    
        if($this->head == null) {
            $this->head = $pc;
            $this->tail =& $this->head;
        } else {
            $this->tail->next = $pc;
            $this->tail =& $this->tail->next;
        }
    }

    /**
     * @return string
     */
    function extractOption()
    {
        if(($this->i < $this->patternLength) and ($this->pattern{$this->i} == '{')) {
            $end = strpos($this->pattern, '}' , $this->i);
            if ($end !== false) {
                $r = substr($this->pattern, ($this->i + 1), ($end - $this->i - 1));
	            $this->i= $end + 1;
        	    return $r;
            }
        }
        return null;
    }

    /**
     * The option is expected to be in decimal and positive. In case of
     * error, zero is returned.  
     */
    function extractPrecisionOption()
    {
        $opt = $this->extractOption();
        $r = 0;
        if ($opt !== null) {
            if (is_numeric($opt)) {
    	        $r = (int)$opt;
            	if($r <= 0) {
            	    LoggerLog::warn("Precision option ({$opt}) isn't a positive integer.");
            	    $r = 0;
            	}
            } else {
                LoggerLog::warn("Category option \"{$opt}\" not a decimal integer.");
            }
        }
        return $r;
    }

    function parse()
    {
        LoggerLog::debug("LoggerPatternParser::parse()");
    
        $c = '';
        $this->i = 0;
        $this->currentLiteral = '';
        while ($this->i < $this->patternLength) {
            $c = $this->pattern{$this->i++};
//            LoggerLog::debug("LoggerPatternParser::parse() char is now '$c' and currentLiteral is '{$this->currentLiteral}'");            
            switch($this->state) {
                case LOG4PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE:
                    // LoggerLog::debug("LoggerPatternParser::parse() state is 'LOG4PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE'");
                    // In literal state, the last char is always a literal.
                    if($this->i == $this->patternLength) {
                        $this->currentLiteral .= $c;
                        continue;
                    }
                    if($c == LOG4PHP_LOGGER_PATTERN_PARSER_ESCAPE_CHAR) {
                        // LoggerLog::debug("LoggerPatternParser::parse() char is an escape char");                    
                        // peek at the next char.
                        switch($this->pattern{$this->i}) {
                            case LOG4PHP_LOGGER_PATTERN_PARSER_ESCAPE_CHAR:
                                // LoggerLog::debug("LoggerPatternParser::parse() next char is an escape char");                    
                                $this->currentLiteral .= $c;
                                $this->i++; // move pointer
                                break;
                            case 'n':
                                // LoggerLog::debug("LoggerPatternParser::parse() next char is 'n'");                            
                                $this->currentLiteral .= LOG4PHP_LINE_SEP;
                                $this->i++; // move pointer
                                break;
                            default:
                                if(strlen($this->currentLiteral) != 0) {
                                    $this->addToList(new LoggerLiteralPatternConverter($this->currentLiteral));
                                    LoggerLog::debug("LoggerPatternParser::parse() Parsed LITERAL converter: \"{$this->currentLiteral}\".");
                                }
                                $this->currentLiteral = $c;
                                $this->state = LOG4PHP_LOGGER_PATTERN_PARSER_CONVERTER_STATE;
                                $this->formattingInfo->reset();
                        }
                    } else {
                        $this->currentLiteral .= $c;
                    }
                    break;
              case LOG4PHP_LOGGER_PATTERN_PARSER_CONVERTER_STATE:
                    // LoggerLog::debug("LoggerPatternParser::parse() state is 'LOG4PHP_LOGGER_PATTERN_PARSER_CONVERTER_STATE'");              
                	$this->currentLiteral .= $c;
                	switch($c) {
                    	case '-':
                            $this->formattingInfo->leftAlign = true;
                            break;
                    	case '.':
                            $this->state = LOG4PHP_LOGGER_PATTERN_PARSER_DOT_STATE;
	                        break;
                    	default:
                            if(ord($c) >= ord('0') and ord($c) <= ord('9')) {
                        	    $this->formattingInfo->min = ord($c) - ord('0');
                        	    $this->state = LOG4PHP_LOGGER_PATTERN_PARSER_MIN_STATE;
                            } else {
                                $this->finalizeConverter($c);
                            }
                  	} // switch
                    break;
              case LOG4PHP_LOGGER_PATTERN_PARSER_MIN_STATE:
                    // LoggerLog::debug("LoggerPatternParser::parse() state is 'LOG4PHP_LOGGER_PATTERN_PARSER_MIN_STATE'");              
	                $this->currentLiteral .= $c;
                    if(ord($c) >= ord('0') and ord($c) <= ord('9')) {
                        $this->formattingInfo->min = ($this->formattingInfo->min * 10) + (ord(c) - ord('0'));
                	} elseif ($c == '.') {
                        $this->state = LOG4PHP_LOGGER_PATTERN_PARSER_DOT_STATE;
                    } else {
                    	$this->finalizeConverter($c);
                	}
                	break;
              case LOG4PHP_LOGGER_PATTERN_PARSER_DOT_STATE:
                    // LoggerLog::debug("LoggerPatternParser::parse() state is 'LOG4PHP_LOGGER_PATTERN_PARSER_DOT_STATE'");              
                	$this->currentLiteral .= $c;
                    if(ord($c) >= ord('0') and ord($c) <= ord('9')) {
                        $this->formattingInfo->max = ord($c) - ord('0');
	                    $this->state = LOG4PHP_LOGGER_PATTERN_PARSER_MAX_STATE;
                    } else {
                	  LoggerLog::warn("LoggerPatternParser::parse() Error occured in position {$this->i}. Was expecting digit, instead got char \"{$c}\".");
	                  $this->state = LOG4PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE;
                    }
                	break;
              case LOG4PHP_LOGGER_PATTERN_PARSER_MAX_STATE:
                    // LoggerLog::debug("LoggerPatternParser::parse() state is 'LOG4PHP_LOGGER_PATTERN_PARSER_MAX_STATE'");              
                	$this->currentLiteral .= $c;
                    if(ord($c) >= ord('0') and ord($c) <= ord('9')) {
                        $this->formattingInfo->max = ($this->formattingInfo->max * 10) + (ord($c) - ord('0'));
	                } else {
                	  $this->finalizeConverter($c);
                      $this->state = LOG4PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE;
	                }
                	break;
            } // switch
        } // while
        if(strlen($this->currentLiteral) != 0) {
            $this->addToList(new LoggerLiteralPatternConverter($this->currentLiteral));
            // LoggerLog::debug("LoggerPatternParser::parse() Parsed LITERAL converter: \"{$this->currentLiteral}\".");
        }
        return $this->head;
    }

    function finalizeConverter($c)
    {
        LoggerLog::debug("LoggerPatternParser::finalizeConverter() with char '$c'");    

        $pc = null;
        switch($c) {
            case 'c':
                $pc = new LoggerCategoryPatternConverter($this->formattingInfo, $this->extractPrecisionOption());
                LoggerLog::debug("LoggerPatternParser::finalizeConverter() CATEGORY converter.");
                // $this->formattingInfo->dump();
                $this->currentLiteral = '';
                break;
            case 'C':
                $pc = new LoggerClassNamePatternConverter($this->formattingInfo, $this->extractPrecisionOption());
                LoggerLog::debug("LoggerPatternParser::finalizeConverter() CLASSNAME converter.");
                //$this->formattingInfo->dump();
                $this->currentLiteral = '';
                break;
            case 'd':
                $dateFormatStr = LOG4PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_ISO8601; // ISO8601_DATE_FORMAT;
                $dOpt = $this->extractOption();

                if($dOpt !== null)
	                $dateFormatStr = $dOpt;
                    
                if ($dateFormatStr == 'ISO8601') {
                    $df = LOG4PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_ISO8601;
                } elseif($dateFormatStr == 'ABSOLUTE') {
                    $df = LOG4PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_ABSOLUTE;
                } elseif($dateFormatStr == 'DATE') {
                    $df = LOG4PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_DATE;
                } else {
                    $df = $dateFormatStr;
                    if ($df == null) {
                        $df = LOG4PHP_LOGGER_PATTERN_PARSER_DATE_FORMAT_ISO8601;
                    }
	            }
                $pc = new LoggerDatePatternConverter($this->formattingInfo, $df);
                $this->currentLiteral = '';
                break;
            case 'F':
                $pc = new LoggerLocationPatternConverter($this->formattingInfo, LOG4PHP_LOGGER_PATTERN_PARSER_FILE_LOCATION_CONVERTER);
                LoggerLog::debug("LoggerPatternParser::finalizeConverter() File name converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'l':
                $pc = new LoggerLocationPatternConverter($this->formattingInfo, LOG4PHP_LOGGER_PATTERN_PARSER_FULL_LOCATION_CONVERTER);
                LoggerLog::debug("LoggerPatternParser::finalizeConverter() Location converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'L':
                $pc = new LoggerLocationPatternConverter($this->formattingInfo, LOG4PHP_LOGGER_PATTERN_PARSER_LINE_LOCATION_CONVERTER);
                LoggerLog::debug("LoggerPatternParser::finalizeConverter() LINE NUMBER converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'm':
                $pc = new LoggerBasicPatternConverter($this->formattingInfo, LOG4PHP_LOGGER_PATTERN_PARSER_MESSAGE_CONVERTER);
                LoggerLog::debug("LoggerPatternParser::finalizeConverter() MESSAGE converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'M':
                $pc = new LoggerLocationPatternConverter($this->formattingInfo, LOG4PHP_LOGGER_PATTERN_PARSER_METHOD_LOCATION_CONVERTER);
                //LogLog.debug("METHOD converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'p':
                $pc = new LoggerBasicPatternConverter($this->formattingInfo, LOG4PHP_LOGGER_PATTERN_PARSER_LEVEL_CONVERTER);
                //LogLog.debug("LEVEL converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'r':
                $pc = new LoggerBasicPatternConverter($this->formattingInfo, LOG4PHP_LOGGER_PATTERN_PARSER_RELATIVE_TIME_CONVERTER);
                LoggerLog::debug("LoggerPatternParser::finalizeConverter() RELATIVE TIME converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 't':
                $pc = new LoggerBasicPatternConverter($this->formattingInfo, LOG4PHP_LOGGER_PATTERN_PARSER_THREAD_CONVERTER);
                LoggerLog::debug("LoggerPatternParser::finalizeConverter() THREAD converter.");
                //formattingInfo.dump();
                $this->currentLiteral = '';
                break;
            case 'u':
                if($this->i < $this->patternLength) {
	                $cNext = $this->pattern{$this->i};
                    if(ord($cNext) >= ord('0') and ord($cNext) <= ord('9')) {
	                    $pc = new LoggerUserFieldPatternConverter($this->formattingInfo, (string)(ord($cNext) - ord('0')));
                        LoggerLog::debug("LoggerPatternParser::finalizeConverter() USER converter [{$cNext}].");
	                    // formattingInfo.dump();
                        $this->currentLiteral = '';
	                    $this->i++;
	                } else {
                        LoggerLog::warn("LoggerPatternParser::finalizeConverter() Unexpected char '{$cNext}' at position {$this->i}.");
                    }
                }
                break;
            case 'x':
                $pc = new LoggerBasicPatternConverter($this->formattingInfo, LOG4PHP_LOGGER_PATTERN_PARSER_NDC_CONVERTER);
                LoggerLog::debug("LoggerPatternParser::finalizeConverter() NDC converter.");
                $this->currentLiteral = '';
                break;

            case 'X':
                $xOpt = $this->extractOption();
                $pc = new LoggerMDCPatternConverter($this->formattingInfo, $xOpt);
                LoggerLog::debug("LoggerPatternParser::finalizeConverter() MDC converter.");
                $this->currentLiteral = '';
                break;
            default:
                LoggerLog::warn("LoggerPatternParser::finalizeConverter() Unexpected char [$c] at position {$this->i} in conversion pattern.");
                $pc = new LoggerLiteralPatternConverter($this->currentLiteral);
                $this->currentLiteral = '';
        }
        $this->addConverter($pc);
    }

    function addConverter($pc)
    {
        $this->currentLiteral = '';
        // Add the pattern converter to the list.
        $this->addToList($pc);
        // Next pattern is assumed to be a literal.
        $this->state = LOG4PHP_LOGGER_PATTERN_PARSER_LITERAL_STATE;
        // Reset formatting info
        $this->formattingInfo->reset();
    }
}

?>