<?php
namespace App\Fields;

/**
 * File class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class File
{

	private static $allowedFormats = ['image' => ['jpeg', 'png', 'jpg', 'pjpeg', 'x-png', 'gif', 'bmp', 'x-ms-bmp']];
	private static $mimeTypes = [];
	private static $phpInjection = ['image'];
	private $path;
	private $mimeType;
	private $mimeShortType;
	private $size;
	private $content;
	private $error = false;

	public static function loadFromRequest($file)
	{
		$instance = new self();
		$instance->name = $file['name'];
		$instance->path = $file['tmp_name'];
		$instance->size = $file['size'];
		$instance->error = $file['error'];
		return $instance;
	}

	public static function loadFromPath($path, $separator = DIRECTORY_SEPARATOR)
	{
		$info = pathinfo($path);
		$instance = new self();
		$instance->name = $info['basename'];
		$instance->path = $path;
		return $instance;
	}

	public function getSize()
	{
		if (empty($this->size)) {
			$this->size = filesize($this->path);
		}
		return $this->size;
	}

	public function getSanitizeName()
	{
		return self::sanitizeUploadFileName($this->name);
	}

	public function getMimeType()
	{
		if (empty($this->mimeType)) {
			$this->mimeType = self::getMimeContentType($this->path);
		}
		return $this->mimeType;
	}

	public function getShortMimeType($type = 1)
	{
		if (empty($this->mimeShortType)) {
			$this->mimeShortType = explode('/', $this->getMimeType());
		}
		return $this->mimeShortType[$type];
	}

	public function getExtension()
	{
		return pathinfo($this->path, PATHINFO_EXTENSION);
	}

	public function validate($type = false)
	{
		$return = true;
		\App\Log::trace('File validate - Start');
		try {
			$this->checkFile();
			$this->validateFormat();
			$this->validateCodeInjection();
			if (($type && $type == 'image') || $this->getShortMimeType(0) == 'image') {
				$this->validateImage();
			}
			if ($type && $this->getShortMimeType(0) != $type) {
				throw new \Exception('Wrong file type');
			}
		} catch (\Exception $e) {
			$return = false;
			\App\Log::error('Error: ' . $e->getMessage());
		}
		\App\Log::trace('File validate - End');
		return $return;
	}

	public function checkFile()
	{
		if ($this->error !== false && $this->error != 0) {
			throw new \Exception('Error request: ' . $this->error);
		}
		if (empty($this->name)) {
			throw new \Exception('Empty name');
		}
		if ($this->getSize() == 0) {
			throw new \Exception('Wrong size');
		}
	}

	public function validateFormat()
	{
		if (isset(self::$allowedFormats[$this->getShortMimeType(0)])) {
			if (!in_array($this->getShortMimeType(1), self::$allowedFormats[$this->getShortMimeType(0)])) {
				throw new \Exception('Illegal format');
			}
		}
	}

	public function validateImage()
	{
		if (!getimagesize($this->path)) {
			throw new \Exception('Wrong image');
		}
		if (preg_match('[\x01-\x08\x0c-\x1f]', $this->getContents())) {
			throw new \Exception('Wrong image');
		}
	}

	public function validateCodeInjection()
	{
		if (in_array($this->getShortMimeType(0), self::$phpInjection)) {
			// Check for php code injection
			$shortTagSupported = ini_get('short_open_tag') ? true : false;
			if (stripos($shortTagSupported ? '<?' : '<?php', $this->getContents()) !== false) {
				throw new \Exception('Error php code injection');
			}
			if ($this->mimeType === 'image/jpeg' || $this->mimeType === 'image/tiff') {
				$exifdata = exif_read_data($this->path);
				if ($exifdata && !$this->validateImageMetadata($exifdata, $shortTagSupported)) {
					throw new \Exception('Error php code injection');
				}
			}
		}
	}

	public function validateImageMetadata($data, $short = true)
	{
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$ok = $this->validateImageMetadata($value);
				if (!$ok) {
					return false;
				}
			}
		} else {
			if (stripos($data, $short ? '<?' : '<?php') !== false) {
				return false;
			}
		}
		return true;
	}

	public function getContents()
	{
		if (empty($this->content)) {
			$this->content = file_get_contents($this->path);
		}
		return $this->content;
	}

	public function moveFile($target)
	{
		return move_uploaded_file($this->path, $target);
	}

	/** Function to sanitize the upload file name when the file name is detected to have bad extensions
	 * @param String -- $fileName - File name to be sanitized
	 * @return String - Sanitized file name
	 */
	static public function sanitizeUploadFileName($fileName, $badFileExtensions = false)
	{
		if (!$badFileExtensions) {
			$badFileExtensions = \AppConfig::main('upload_badext');
		}
		$fileName = preg_replace('/\s+/', '_', \vtlib\Functions::slug($fileName)); //replace space with _ in filename
		$fileName = rtrim($fileName, '\\/<>?*:"<>|');

		$fileNameParts = explode('.', $fileName);
		$badExtensionFound = false;

		foreach ($fileNameParts as $key => &$partOfFileName) {
			if (in_array(strtolower($partOfFileName), $badFileExtensions)) {
				$badExtensionFound = true;
				$fileNameParts[$i] = $partOfFileName;
			}
		}
		$newFileName = implode('.', $fileNameParts);
		if ($badExtensionFound) {
			$newFileName .= '.txt';
		}
		return $newFileName;
	}

	static public function getMimeContentType($fileName)
	{
		if (empty(self::$mimeTypes)) {
			require 'config/mimetypes.php';
			self::$mimeTypes = $mimeTypes;
		}
		$ext = explode('.', $fileName);
		$ext = strtolower(array_pop($ext));
		if (isset(self::$mimeTypes[$ext])) {
			$mimeType = self::$mimeTypes[$ext];
		} elseif (function_exists('mime_content_type')) {
			$mimeType = mime_content_type($fileName);
		} elseif (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimeType = finfo_file($finfo, $fileName);
			finfo_close($finfo);
		} else {
			$mimeType = 'application/octet-stream';
		}
		return $mimeType;
	}
}
