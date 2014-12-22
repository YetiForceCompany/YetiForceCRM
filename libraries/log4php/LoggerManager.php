<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/** Classes to avoid logging */

class LoggerManager {
	static function getlogger($name = 'ROOT') {
		$configinfo = LoggerPropertyConfigurator::getInstance()->getConfigInfo($name);
		return new Logger($name, $configinfo);
	}
}

/**
 * Core logging class.
 */
class Logger {
	private $name = false;
	private $appender = false;
	private $configinfo = false;
	
	/**
	 * Writing log file information could cost in-terms of performance.
	 * Enable logging based on the levels here explicitly
	 */
	private $enableLogLevel =  array(	
		'ERROR' => false,
		'FATAL' => false,
		'INFO'  => false,
		'WARN'  => false,
		'DEBUG' => false,
	);
	
	function __construct($name, $configinfo = false) {
		$this->name = $name;
		$this->configinfo = $configinfo;
		
		/** For migration log-level we need debug turned-on */
		if(strtoupper($name) == 'MIGRATION') {
			$this->enableLogLevel['DEBUG'] = true;
		}
		
	}
	
	function emit($level, $message) {
		if(!$this->appender) {
			$filename = 'logs/vtigercrm.log';			
			if($this->configinfo && isset($this->configinfo['appender']['File'])) {
				$filename = $this->configinfo['appender']['File'];
			}
			$this->appender = new LoggerAppenderFile($filename, 0777); 
		}
		$mypid = @getmypid();
		
		$this->appender->emit("$level [$mypid] $this->name - ", $message);
	}
	
	function info($message) {
		if($this->isLevelEnabled('INFO')) {
			$this->emit('INFO', $message);
		}
	}
	
	function debug($message) {
		if($this->isDebugEnabled()) {
			$this->emit('DEBUG', $message);
		}
	}
	
	function warn($message) {
		if($this->isLevelEnabled('WARN')) {
			$this->emit('WARN', $message);
		}
	}
	
	function fatal($message) {
		if($this->isLevelEnabled('FATAL')) {
			$this->emit('FATAL', $message);
		}		
	}
	
	function error($message) {
		if($this->isLevelEnabled('ERROR')) {
			$this->emit('ERROR', $message);
		}
	}
	
	function isLevelEnabled($level) {
		if($this->enableLogLevel[$level] && $this->configinfo) {
			return (strtoupper($this->configinfo['level']) == $level);
		}
		return false;
	}
	
	function isDebugEnabled() {
		return $this->isLevelEnabled('DEBUG');
	}
}

/**
 * Log message appender to file.
 */
class LoggerAppenderFile {
	
	private $filename;
	private $chmod;
	
	function __construct($filename, $chmod = 0222) {
		$this->filename = $filename;
		$this->chmod    = $chmod;
	}
	
	function emit($prefix, $message) {		
		if($this->chmod != 0777 && file_exists($this->filename)) {
			if(is_readable($this->filename)) {
				@chmod($this->filename, $this->chmod);
			}
		}
		$fh = @fopen($this->filename, 'a');
		if($fh) {
			@fwrite($fh, date('m/d/Y H:i:s') . " $prefix $message\n");
			@fclose($fh);
		}
	}	
	
}
?>