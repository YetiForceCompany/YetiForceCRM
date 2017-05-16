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
	 * Function to extract files only for a given extension
	 * @param string $toDir Target directory
	 * @param string $ex File extension
	 * @throws Exceptions\AppException
	 */
	public function unzipByExtension($toDir, $ex)
	{
		if (!is_dir($toDir)) {
			throw new Exceptions\AppException('Directory not found, and unable to create it');
		}
		if (!is_writable($toDir)) {
			throw new Exceptions\AppException('No permissions to create files');
		}
		$filelist = [];
		for ($i = 0; $i < $this->numFiles; $i++) {
			$path = $this->getNameIndex($i);
			if ($this->checkFile($path)) {
				continue;
			}
			$filelist[] = $path;
			$this->extractTo($toDir, $path);
		}
		return $filelist;
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
		return false;
	}
}
