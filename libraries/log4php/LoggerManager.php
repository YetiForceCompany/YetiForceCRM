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
	protected static $loggerCache = false;
	static function getlogger($name = 'ROOT') {
		$configinfo = LoggerPropertyConfigurator::getInstance()->getConfigInfo($name);
		self::$loggerCache = new Logger($name, $configinfo);
		return self::$loggerCache;
	}
	
	static function getInstance() {
		if (self::$loggerCache) {
			return self::$loggerCache;
		}
		return LoggerManager::getLogger();
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
	
	public function __construct($name, $configinfo = false) {
		$this->name = $name;
		$this->configinfo = $configinfo;
		
		/** For migration log-level we need debug turned-on */
		if(strtoupper($name) == 'MIGRATION') {
			$this->enableLogLevel['DEBUG'] = true;
		}
		
	}
	
	public function emit($level, $message) {
		if(!$this->appender) {
			$filename = 'cache/logs/system.log';			
			if($this->configinfo && isset($this->configinfo['appender']['File'])) {
				$filename = $this->configinfo['appender']['File'];
			}
			$this->appender = new LoggerAppenderFile($filename, 0777); 
		}
		$mypid = @getmypid();
		
		$this->appender->emit("$level [$mypid] $this->name - ", $message);
	}
	
	public function info($message) {
		if($this->isLevelEnabled('INFO')) {
			$this->emit('INFO', $message);
		}
	}
	
	public function debug($message) {
		if($this->isDebugEnabled()) {
			$this->emit('DEBUG', $message);
		}
	}
	
	public function warn($message) {
		if($this->isLevelEnabled('WARN')) {
			$this->emit('WARN', $message);
		}
	}
	
	public function fatal($message) {
		if($this->isLevelEnabled('FATAL')) {
			$this->emit('FATAL', $message);
		}		
	}
	
	public function error($message) {
		if($this->isLevelEnabled('ERROR')) {
			$this->emit('ERROR', $message);
			$this->emit('ERROR', PHP_EOL.vtlib\Functions::getBacktrace(1));
		}
	}
	
	public function isLevelEnabled($level) {
		if($this->enableLogLevel[$level] && $this->configinfo) {
			return (strtoupper($this->configinfo['level']) == $level);
		}
		return false;
	}
	
	public function isDebugEnabled() {
		return $this->isLevelEnabled('DEBUG');
	}
}

/**
 * Log message appender to file.
 */
class LoggerAppenderFile {
	
	private $filename;
	private $chmod;
	
	public function __construct($filename, $chmod = 0222) {
		$this->filename = $filename;
		$this->chmod    = $chmod;
	}
	
	public function emit($prefix, $message) {
		/*
		if($this->chmod != 0777 && file_exists($this->filename)) {
			if(is_readable($this->filename)) {
				@chmod($this->filename, $this->chmod);
			}
		}
		*/
		$fh = @fopen($this->filename, 'a');
		if($fh) {
			@fwrite($fh, date('m/d/Y H:i:s') . " $prefix $message\n");
			@fclose($fh);
		}
	}	
	
}
?>
