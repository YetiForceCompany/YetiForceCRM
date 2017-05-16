<?php
/**
 * A file archive, compressed with Zip.
 * @package YetiForce.App
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @copyright YetiForce Sp. z o.o.
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
namespace App;

/**
 * Zip class
 */
class Zip extends \ZipArchive
{

	/**
	 * Files extension for extract
	 * @var type 
	 */
	protected $extensionOnly;

	/**
	 * Construct
	 */
	public function __construct($fileName = false)
	{
		if ($fileName) {
			if (!$this->open($fileName)) {
				throw new Exceptions\AppException('Unable to open the zip file');
			}
		}
	}

	/**
	 * Extract files only for a given extension
	 * @param string $ex File extension
	 * @return $this 
	 */
	public function onlyExtension($ex)
	{
		$this->extensionOnly = $ex;
		return $this;
	}

	/**
	 * Function to extract files
	 * @param string $toDir Target directory
	 * @return string[] Unpacked files
	 * @throws Exceptions\AppException
	 */
	public function unzip($toDir)
	{
		if (!is_dir($toDir)) {
			throw new Exceptions\AppException('Directory not found, and unable to create it');
		}
		if (!is_writable($toDir)) {
			throw new Exceptions\AppException('No permissions to create files');
		}
		$fileList = [];
		if (is_array($toDir)) {
			foreach ($toDir as $dirname => $target) {
				for ($i = 0; $i < $this->numFiles; $i++) {
					$path = $this->getNameIndex($i);
					if (strpos($path, "{$dirname}/") !== 0 || $this->checkFile($path)) {
						continue;
					}
					// Determine output filename (removing the $source prefix)
					$file = $target . '/' . substr($path, strlen($dirname) + 1);
					// Create the directories if necessary
					$dir = dirname($file);
					if (!is_dir($dir)) {
						mkdir($dir, 0777, true);
					}
					$fileList[] = $path;
					if (substr($path, -1) !== '/') {
						// Read from Zip and write to disk
						$fpr = $this->getStream($path);
						$fpw = fopen($file, 'w');
						while ($data = fread($fpr, 1024)) {
							fwrite($fpw, $data);
						}
						fclose($fpr);
						fclose($fpw);
					}
				}
			}
		} else {
			for ($i = 0; $i < $this->numFiles; $i++) {
				$path = $this->getNameIndex($i);
				if ($this->checkFile($path)) {
					continue;
				}
				$fileList[] = $path;
			}
			$this->extractTo($toDir, $fileList);
		}
		$this->close();
		return $fileList;
	}

	/**
	 * Check illegal characters
	 * @param string $fileName
	 * @return boolean
	 */
	public function checkFile($fileName)
	{
		preg_match("[^\w\s\d\.\-_~,;:\[\]\(\]]", $fileName, $matches);
		if ($matches) {
			return true;
		}
		if (stripos($fileName, '..') === 0 || stripos($fileName, '/') === 0) {
			return true;
		}
		if (isset($this->extensionOnly) && $this->extensionOnly !== pathinfo($fileName, PATHINFO_EXTENSION)) {
			return true;
		}
		return false;
	}
}
