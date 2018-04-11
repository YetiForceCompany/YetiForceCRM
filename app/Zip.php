<?php
/**
 * A file archive, compressed with Zip.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Zip class.
 */
class Zip extends \ZipArchive
{
	/**
	 * Files extension for extract.
	 *
	 * @var array
	 */
	protected $onlyExtensions;

	/**
	 * Illegal extensions for extract.
	 *
	 * @var array
	 */
	protected $illegalExtensions;

	/**
	 * Check files before unpacking.
	 *
	 * @var bool
	 */
	protected $checkFiles = true;

	/**
	 * Open file and initialization unpack.
	 *
	 * @param bool  $fileName
	 * @param array $options
	 *
	 * @throws Exceptions\AppException
	 *
	 * @return Zip|bool
	 */
	public static function openFile($fileName = false, $options = [])
	{
		if (!$fileName) {
			throw new \App\Exceptions\AppException('No file name');
		}
		$zip = new self($fileName, $options);
		if (!file_exists($fileName) || !$zip->open($fileName)) {
			throw new \App\Exceptions\AppException('Unable to open the zip file');
		}
		if (!$zip->checkFreeSpace()) {
			throw new \App\Exceptions\AppException('The content of the zip file is too large');
		}
		foreach ($options as $key => $value) {
			$zip->$key = $value;
		}
		return $zip;
	}

	/**
	 * Open file for create zip file.
	 *
	 * @param string $fileName
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\Zip
	 */
	public static function createFile($fileName)
	{
		$zip = new self();
		if ($zip->open($fileName, self::CREATE | self::OVERWRITE) !== true) {
			throw new \App\Exceptions\AppException('Unable to create the zip file');
		}
		return $zip;
	}

	/**
	 * Function to extract files.
	 *
	 * @param string $toDir Target directory
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string[] Unpacked files
	 */
	public function unzip($toDir)
	{
		$fileList = [];
		if (is_array($toDir)) {
			foreach ($toDir as $dirname => $target) {
				for ($i = 0; $i < $this->numFiles; ++$i) {
					$path = $this->getNameIndex($i);
					if (strpos($path, $dirname . '/') !== 0 || ($this->checkFiles && $this->checkFile($path))) {
						continue;
					}
					// Determine output filename (removing the $source prefix)
					$file = $target . DIRECTORY_SEPARATOR . substr($path, strlen($dirname) + 1);
					// Create the directories if necessary
					$dir = dirname($file);
					if (!is_dir($dir)) {
						mkdir($dir, 0755, true);
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
				throw new \App\Exceptions\AppException('Directory not found, and unable to create it');
			}
			if (!is_writable($toDir)) {
				throw new \App\Exceptions\AppException('No permissions to create files');
			}
			for ($i = 0; $i < $this->numFiles; ++$i) {
				$path = $this->getNameIndex($i);
				if ($this->checkFiles && $this->checkFile($path)) {
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
	 * Check illegal characters.
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function checkFile($path)
	{
		preg_match("[^\w\s\d\.\-_~,;:\[\]\(\]]", $path, $matches);
		if ($matches) {
			return true;
		}
		if (stripos($path, '../') === 0 || stripos($path, '..\\') === 0) {
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
			$stat = $this->statName($path);
			$fileInstance = \App\Fields\File::loadFromInfo([
					'content' => $this->getFromName($path),
					'path' => $this->getLocalPath($path),
					'name' => basename($path),
					'size' => $stat['size'],
					'validateAllCodeInjection' => true,
			]);
			if (!$fileInstance->validate()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if the file path is directory.
	 *
	 * @param string $filePath
	 *
	 * @return bool
	 */
	public function isDir($filePath)
	{
		if (substr($filePath, -1, 1) === '/') {
			return true;
		}
		return false;
	}

	/**
	 * Function to extract single file.
	 *
	 * @param string $compressedFileName
	 * @param string $targetFileName
	 *
	 * @return bool
	 */
	public function unzipFile($compressedFileName, $targetFileName)
	{
		return copy($this->getLocalPath($compressedFileName), $targetFileName);
	}

	/**
	 * Get compressed file path.
	 *
	 * @param string $compressedFileName
	 *
	 * @return string
	 */
	public function getLocalPath($compressedFileName)
	{
		return "zip://{$this->filename}#{$compressedFileName}";
	}

	/**
	 * Check free disk space.
	 *
	 * @return bool
	 */
	public function checkFreeSpace()
	{
		$df = disk_free_space(ROOT_DIRECTORY . DIRECTORY_SEPARATOR);
		$size = 0;
		for ($i = 0; $i < $this->numFiles; ++$i) {
			$stat = $this->statIndex($i);
			$size += $stat['size'];
		}
		return $df > $size;
	}

	/**
	 * Copy the directory on the disk into zip file.
	 *
	 * @param string $dir
	 * @param string $localName
	 */
	public function addDirectory($dir, $localName = '')
	{
		if ($localName) {
			$localName .= \DIRECTORY_SEPARATOR;
		}
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(realpath($dir)), \RecursiveIteratorIterator::LEAVES_ONLY);
		foreach ($files as $name => $file) {
			if (!$file->isDir()) {
				$filePath = $file->getRealPath();
				$this->addFile($filePath, $localName . Fields\File::getLocalPath($filePath));
			}
		}
	}

	/**
	 * Push out the file content for download.
	 */
	public function download()
	{
		$fileName = $this->filename;
		$this->close();
		header('Cache-Control: private, max-age=120, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="' . $fileName . '";');
		header('Accept-Ranges: bytes');
		header('Content-Length: ' . filesize($fileName));
		echo readfile($fileName);
		unlink($fileName);
	}
}
