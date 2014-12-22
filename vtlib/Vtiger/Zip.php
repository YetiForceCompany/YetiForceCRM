<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('vtlib/thirdparty/dZip.inc.php');

/**
 * Wrapper class over dZip.
 * @package vtlib
 */
class Vtiger_Zip extends dZip {
	/**
	 * Push out the file content for download.
	 */
	function forceDownload($zipfileName) {
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: application/zip");
		header("Content-Disposition: attachment; filename=".basename($zipfileName).";" );
		//header("Content-Transfer-Encoding: binary");

		// For details on this workaround check here the ticket
		// http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/5298
		$disk_file_size = filesize($zipfileName);
		$zipfilesize = $disk_file_size + ($disk_file_size % 1024);
		header("Content-Length: ".$zipfilesize);
		$fileContent = fread(fopen($zipfileName, "rb"), $zipfilesize);
		echo $fileContent;	
	}

	/**
	 * Get relative path (w.r.t base)
	 */
	function __getRelativePath($basepath, $srcpath) {
		$base_realpath = $this->__normalizePath(realpath($basepath));
		$src_realpath  = $this->__normalizePath(realpath($srcpath));
		$search_index  = strpos($src_realpath, $base_realpath);
		if($search_index === 0) {
			$startindex = strlen($base_realpath)+1;
			// On windows $base_realpath ends with / and On Linux it will not have / at end!
			if(strrpos($base_realpath, '/') == strlen($base_realpath)-1) $startindex -= 1;
			$relpath = substr($src_realpath, $startindex);
		}
		return $relpath;
	}

	/**
	 * Check and add '/' directory separator
	 */
	function __fixDirSeparator($path) {
		if($path != '' && (strripos($path, '/') != strlen($path)-1)) $path .= '/';
		return $path;
	}

	/**
	 * Normalize the directory path separators.
	 */
	function __normalizePath($path) {
		if($path && strpos($path, '\\')!== false) $path = preg_replace("/\\\\/", "/", $path);
		return $path;
	}

	/**
	 * Copy the directory on the disk into zip file.
	 */
	function copyDirectoryFromDisk($dirname, $zipdirname=null, $excludeList=null, $basedirname=null) {
		$dir = opendir($dirname);
		if(strripos($dirname, '/') != strlen($dirname)-1)
			$dirname .= '/';

		if($basedirname == null) $basedirname = realpath($dirname);

		while(false !== ($file = readdir($dir))) {
			if($file != '.' && $file != '..' && 
				$file != '.svn' && $file != 'CVS') {
					// Exclude the file/directory 
					if(!empty($excludeList) && in_array("$dirname$file", $excludeList))
						continue;

					if(is_dir("$dirname$file")) {
						$this->copyDirectoryFromDisk("$dirname$file", $zipdirname, $excludeList, $basedirname);
					} else {
						$zippath = $dirname;
						if($zipdirname != null && $zipdirname != '') {
							$zipdirname = $this->__fixDirSeparator($zipdirname);
							$zippath = $zipdirname.$this->__getRelativePath($basedirname, $dirname);
						}
						$this->copyFileFromDisk($dirname, $zippath, $file);
					}
				}
		}
		closedir($dir);
	}

	/**
	 * Copy the disk file into the zip.
	 */
	function copyFileFromDisk($path, $zippath, $file) {
		$path = $this->__fixDirSeparator($path);
		$zippath = $this->__fixDirSeparator($zippath);
		$this->addFile("$path$file", "$zippath$file");
	}
}
?>
