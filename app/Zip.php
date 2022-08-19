<?php
/**
 * A file archive, compressed with Zip.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	 * @return bool|Zip
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
			$zip->{$key} = $value;
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
		if (true !== $zip->open($fileName, self::CREATE | self::OVERWRITE)) {
			throw new \App\Exceptions\AppException('Unable to create the zip file');
		}
		return $zip;
	}

	/**
	 * Function to extract files.
	 *
	 * @param      $toDir Target directory
	 * @param bool $close
	 *
	 * @return string|string[] Unpacked files
	 */
	public function unzip($toDir, bool $close = true)
	{
		if (\is_string($toDir)) {
			$toDir = [$toDir];
		}
		$files = $created = [];
		foreach ($toDir as $dir => $target) {
			for ($i = 0; $i < $this->numFiles; ++$i) {
				$zipPath = $this->getNameIndex($i);
				$path = \str_replace('\\', '/', $zipPath);
				if ((!\is_numeric($dir) && 0 !== strpos($path, $dir . '/')) || $this->validateFile($zipPath)) {
					continue;
				}
				$files[] = $zipPath;
				$file = $target . '/' . (\is_numeric($dir) ? $path : substr($path, \strlen($dir) + 1));
				$fileDir = \dirname($file);
				if (!isset($created[$fileDir])) {
					if (!is_dir($fileDir)) {
						mkdir($fileDir, 0755, true);
					}
					$created[$fileDir] = true;
				}
				if (!$this->isDir($path)) {
					// Read from Zip and write to disk
					$fpr = $this->getStream($zipPath);
					$fpw = fopen($file, 'w');
					while ($data = fread($fpr, 1024)) {
						fwrite($fpw, $data);
					}
					fclose($fpr);
					fclose($fpw);
				}
			}
		}
		if ($close) {
			$this->close();
		}
		return $files;
	}

	/**
	 * Simple extract the archive contents.
	 *
	 * @param string $toDir
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function extract(string $toDir)
	{
		if (!is_dir($toDir) && !mkdir($toDir, 0755, true) && !is_dir($toDir)) {
			throw new \App\Exceptions\AppException('Directory unable to create it');
		}
		$fileList = [];
		for ($i = 0; $i < $this->numFiles; ++$i) {
			$path = $this->getNameIndex($i);
			if ($this->validateFile(\str_replace('\\', '/', $path))) {
				continue;
			}
			$fileList[] = $path;
		}
		$this->extractTo($toDir, $fileList);
		return $fileList;
	}

	/**
	 * Check illegal characters.
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function validateFile(string $path)
	{
		if (!Validator::path($path)) {
			return true;
		}
		$validate = false;
		if ($this->checkFiles && !$this->isDir($path)) {
			$extension = pathinfo($path, PATHINFO_EXTENSION);
			if (isset($this->onlyExtensions) && !\in_array($extension, $this->onlyExtensions)) {
				$validate = true;
			}
			if (isset($this->illegalExtensions) && \in_array($extension, $this->illegalExtensions)) {
				$validate = true;
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
				$validate = true;
			}
		}
		return $validate;
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
		if ('/' === substr($filePath, -1, 1)) {
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
		$df = disk_free_space(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR);
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
	 * @param bool   $relativePath
	 */
	public function addDirectory(string $dir, string $localName = '', bool $relativePath = false)
	{
		if ($localName) {
			$localName .= '/';
		}
		$path = realpath($dir);
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::LEAVES_ONLY);
		$pathToTrim = $relativePath ? $path : ROOT_DIRECTORY;
		foreach ($files as $file) {
			if (!$file->isDir()) {
				$filePath = $file->getRealPath();
				$zipPath = str_replace(\DIRECTORY_SEPARATOR, '/', Fields\File::getLocalPath($filePath, $pathToTrim));
				$this->addFile($filePath, $localName . $zipPath);
			}
		}
	}

	/**
	 * Push out the file content for download.
	 *
	 * @param string $name
	 */
	public function download(string $name)
	{
		$fileName = $this->filename;
		$this->close();
		header('cache-control: private, max-age=120, must-revalidate');
		header('pragma: no-cache');
		header('expires: 0');
		header('content-type: application/zip');
		header('content-disposition: attachment; filename="' . $name . '.zip";');
		header('accept-ranges: bytes');
		header('content-length: ' . filesize($fileName));
		readfile($fileName);
		unlink($fileName);
	}
}
