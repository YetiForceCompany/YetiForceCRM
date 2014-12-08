<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('vtlib/thirdparty/dUnzip2.inc.php');

/**
 * Provides API to make working with zip file extractions easy
 * @package vtlib
 */
class Vtiger_Unzip extends dUnzip2 {

	/**
	 * Check existence of path in the given array
	 * @access private
	 */
	function __checkPathInArray($path, $pathArray) {
		foreach($pathArray as $checkPath) {
			if(strpos($path, $checkPath) === 0)
				return true;
		}
		return false;
	}

	/**
	 * Check if the file path is directory
	 * @param String Zip file path
	 */
	function isdir($filepath) {
		if(substr($filepath, -1, 1) == "/") return true;
		return false;
	}

	/**
	 * Extended unzipAll function (look at base class)
	 * Allows you to rename while unzipping and handle exclusions.
	 * @access private
	 */
	Function unzipAllEx($targetDir=false, $includeExclude=false, $renamePaths=false, $ignoreFiles=false,
		$baseDir="", $applyChmod=0755){

		// We want to always maintain the structure
		$maintainStructure = true;

		if($targetDir === false)
			$targetDir = dirname(__FILE__)."/";

		if($renamePaths === false) $renamePaths = Array();

		/*
		 * Setup includeExclude parameter
		 * FORMAT:
		 * Array(
		 * 'include'=> Array('zipfilepath1', 'zipfilepath2', ...),
		 * 'exclude'=> Array('zipfilepath3', ...)
		 * )
		 *
		 * DEFAULT: If include is specified only files under the specified path will be included.
		 * If exclude is specified folders or files will be excluded.
		 */
		if($includeExclude === false) $includeExclude = Array();

		$lista = $this->getList();
		if(sizeof($lista)) foreach($lista as $fileName=>$trash){
			// Should the file be ignored?
			if($includeExclude['include'] &&
				!$this->__checkPathInArray($fileName, $includeExclude['include'])) {
					// Do not include something not specified in include
					continue;
			}
			if($includeExclude['exclude'] &&
				$this->__checkPathInArray($fileName, $includeExclude['exclude'])) {
					// Do not include something not specified in include
					continue;
			}
			// END

			$dirname  = dirname($fileName);

			// Rename the path with the matching one (as specified)
			if(!empty($renamePaths)) {
				foreach($renamePaths as $lookup => $replace) {
					if(strpos($dirname, $lookup) === 0) {
						$dirname = substr_replace($dirname, $replace, 0, strlen($lookup));
						break;
					}
				}
			}
			// END

			$outDN    = "$targetDir/$dirname";

			if(substr($dirname, 0, strlen($baseDir)) != $baseDir)
				continue;

			if(!is_dir($outDN) && $maintainStructure){
				$str = "";
				$folders = explode("/", $dirname);
				foreach($folders as $folder){
					$str = $str?"$str/$folder":$folder;
					if(!is_dir("$targetDir/$str")){
						$this->debugMsg(1, "Creating folder: $targetDir/$str");
						mkdir("$targetDir/$str");
						if($applyChmod)
							@chmod("$targetDir/$str", $applyChmod);
					}
				}
			}
			if(substr($fileName, -1, 1) == "/")
				continue;

			if (substr($fileName, -3) == '.sh') $applyChmod = 0775; // Script executable.
			$this->unzip($fileName, "$targetDir/$dirname/".basename($fileName), $applyChmod);
		}
	}

	/**
	 * Function checks if the file exist in the zip
	 * @param type $fileName
	 * @return boolean
	 */
	function checkFileExistsInRootFolder($fileName) {
		$fileList = $this->getList();
		foreach($fileList as $file => $details) {
			if($fileName === $file)
				return true;
		}
		return false;
	}
}
?>
