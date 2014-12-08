<?php
/**
 * qCal_Loader
 * Loads files from the file system. Looks through the entire include path
 */
class qCal_Loader {

	/**
	 * Load a class
	 */
	static public function loadClass($name) {
	
		$path = str_replace("_", DIRECTORY_SEPARATOR, $name) . ".php";
		self::loadFile($path);
	
	}
	/**
	 * Loads a file or throws an exception
	 */
	static public function loadFile($filename) {
	
		if (!self::fileExists($filename)) {
			throw new qCal_Exception_InvalidFile("$filename does not exist.");
		}
		require_once $filename;
	
	}
	/**
	 * Looks through the include path for file name
	 */
	static public function fileExists($filename) {
	
		$includepath = get_include_path();
		$includepath = explode(PATH_SEPARATOR, $includepath);
		foreach ($includepath as $path) {
			$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			if (file_exists($path . $filename)) return true;
		}
		return false;
	
	}

}