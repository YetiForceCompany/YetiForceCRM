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
	 * @var array 
	 */
	protected $onlyExtensions;

	/**
	 * Illegal extensions for extract
	 * @var array 
	 */
	protected $illegalExtensions;

	/**
	 * Construct
	 */
	public function __construct($fileName = false)
	{
		if ($fileName) {
			if (!file_exists($fileName) || !$this->open($fileName)) {
				throw new Exceptions\AppException('Unable to open the zip file');
			}
		}
	}

	/**
	 * Extract files only for a given extension
	 * @param array|string $ex File extension
	 * @return $this 
	 */
	public function onlyExtension($ex)
	{
		$this->onlyExtensions = is_array($ex) ? $ex : [$ex];
		return $this;
	}

	/**
	 * Extract files outside of illegal extensions
	 * @param array|string $ex
	 * @return $this
	 */
	public function illegalExtensions($ex)
	{
		$this->illegalExtensions = is_array($ex) ? $ex : [$ex];
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
					if (!$this->isDir($path)) {
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
			if (!is_dir($toDir)) {
				throw new Exceptions\AppException('Directory not found, and unable to create it');
			}
			if (!is_writable($toDir)) {
				throw new Exceptions\AppException('No permissions to create files');
			}
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
	 * @param string $path
	 * @return boolean
	 */
	public function checkFile($path)
	{
		preg_match("[^\w\s\d\.\-_~,;:\[\]\(\]]", $path, $matches);
		if ($matches) {
			return true;
		}
		if (stripos($path, '..') === 0 || stripos($path, '/') === 0) {
			return true;
		}
		if (!$this->isDir($path)) {
			$extension = pathinfo($path, PATHINFO_EXTENSION);
			if (isset($this->onlyExtensions) && !in_array($extension, $this->onlyExtensions)) {
				return true;
			}
			if (isset($this->illegalExtensions) && in_array($extension, $this->illegalExtensions)) {
				return true;
			}
			$info = pathinfo($path);
			$stat = $this->statName($path);
			$fileInstance = \App\Fields\File::loadFromInfo([
					'path' => $this->getLocalPath($path),
					'name' => $info['basename'],
					'size' => $stat['size'],
					'validateAllCodeInjection' => true
			]);
			if (!$fileInstance->validate()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if the file path is directory
	 * @param string $filePath
	 * @return boolean
	 */
	public function isDir($filePath)
	{
		if (substr($filePath, -1, 1) === '/') {
			return true;
		}
		return false;
	}

	/**
	 * Function to extract single file
	 * @param string $compressedFileName
	 * @param string $targetFileName
	 * @return boolean
	 */
	public function unzipFile($compressedFileName, $targetFileName)
	{
		return copy($this->getLocalPath($compressedFileName), $targetFileName);
	}

	/**
	 * Get compressed file path
	 * @param string $compressedFileName
	 * @return string
	 */
	public function getLocalPath($compressedFileName)
	{
		return "zip://{$this->filename}#{$compressedFileName}";
	}
}
