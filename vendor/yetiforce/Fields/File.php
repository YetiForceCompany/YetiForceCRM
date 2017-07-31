<?php
namespace App\Fields;

/**
 * File class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class File
{

	/**
	 * Allowed formats
	 * @var string[] 
	 */
	private static $allowedFormats = ['image' => ['jpeg', 'png', 'jpg', 'pjpeg', 'x-png', 'gif', 'bmp', 'x-ms-bmp']];

	/**
	 * Mime types
	 * @var string[] 
	 */
	private static $mimeTypes;

	/**
	 * What file types to validate by php injection
	 * @var string[]  
	 */
	private static $phpInjection = ['image'];

	/**
	 * File path
	 * @var string 
	 */
	private $path;

	/**
	 * File mime type
	 * @var string 
	 */
	private $mimeType;

	/**
	 * File short mime type
	 * @var string 
	 */
	private $mimeShortType;

	/**
	 * Size
	 * @var int 
	 */
	private $size;

	/**
	 * File content
	 * @var string 
	 */
	private $content;

	/**
	 * Error code
	 * @var int|bool 
	 */
	private $error = false;

	/**
	 * Validate all files by code injection
	 * @var bool 
	 */
	private $validateAllCodeInjection = false;

	/**
	 * Load file instance from request
	 * @param array $file
	 * @return \self
	 */
	public static function loadFromRequest($file)
	{
		$instance = new self();
		$instance->name = $file['name'];
		$instance->path = $file['tmp_name'];
		$instance->size = $file['size'];
		$instance->error = $file['error'];
		return $instance;
	}

	/**
	 * Load file instance from file path
	 * @param array $path
	 * @param string $separator
	 * @return \self
	 */
	public static function loadFromPath($path, $separator = DIRECTORY_SEPARATOR)
	{
		$info = pathinfo($path);
		$instance = new self();
		$instance->name = $info['basename'];
		$instance->path = $path;
		return $instance;
	}

	/**
	 * Load file instance from file info
	 * @param array $fileInfo
	 * @return \self
	 */
	public static function loadFromInfo($fileInfo)
	{
		$instance = new self();
		foreach ($fileInfo as $key => $value) {
			$instance->$key = $fileInfo[$key];
		}
		return $instance;
	}

	/**
	 * Get size
	 * @return int
	 */
	public function getSize()
	{
		if (empty($this->size)) {
			$this->size = filesize($this->path);
		}
		return $this->size;
	}

	/**
	 * Function to sanitize the upload file name when the file name is detected to have bad extensions
	 * @return string
	 */
	public function getSanitizeName()
	{
		return self::sanitizeUploadFileName($this->name);
	}

	/**
	 * Get mime type
	 * @return string 
	 */
	public function getMimeType()
	{
		if (empty($this->mimeType)) {
			static::initMimeTypes();
			$ext = explode('.', $this->name);
			$ext = strtolower(array_pop($ext));
			if (isset(self::$mimeTypes[$ext])) {
				$this->mimeType = self::$mimeTypes[$ext];
			} elseif (function_exists('mime_content_type')) {
				$this->mimeType = mime_content_type($this->path);
			} elseif (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME);
				$this->mimeType = finfo_file($finfo, $this->path);
				finfo_close($finfo);
			} else {
				$this->mimeType = 'application/octet-stream';
			}
		}
		return $this->mimeType;
	}

	/**
	 * Get short mime type
	 * @param int $type 0 or 1
	 * @return string
	 */
	public function getShortMimeType($type = 1)
	{
		if (empty($this->mimeShortType)) {
			$this->mimeShortType = explode('/', $this->getMimeType());
		}
		return $this->mimeShortType[$type];
	}

	/**
	 * Get extension
	 * @return string
	 */
	public function getExtension()
	{
		return pathinfo($this->path, PATHINFO_EXTENSION);
	}

	/**
	 * Validate whether the file is safe 
	 * @param boolean|string $type
	 * @return boolean
	 * @throws \Exception
	 */
	public function validate($type = false)
	{
		$return = true;
		\App\Log::trace('File validate - Start', __CLASS__);
		try {
			$this->checkFile();
			$this->validateFormat();
			$this->validateCodeInjection();
			if (($type && $type === 'image') || $this->getShortMimeType(0) === 'image') {
				$this->validateImage();
			}
			if ($type && $this->getShortMimeType(0) != $type) {
				throw new \Exception('Wrong file type');
			}
		} catch (\Exception $e) {
			$return = false;
			\App\Log::error('Error: ' . $e->getMessage(), __CLASS__);
		}
		\App\Log::trace('File validate - End', __CLASS__);
		return $return;
	}

	/**
	 * Basic check file
	 * @throws \Exception
	 */
	private function checkFile()
	{
		if ($this->error !== false && $this->error != 0) {
			throw new \Exception('Error request: ' . $this->error);
		}
		if (empty($this->name)) {
			throw new \Exception('Empty name');
		}
		if ($this->getSize() === 0) {
			throw new \Exception('Wrong size');
		}
	}

	/**
	 * Validate format
	 * @throws \Exception
	 */
	private function validateFormat()
	{
		if (isset(self::$allowedFormats[$this->getShortMimeType(0)])) {
			if (!in_array($this->getShortMimeType(1), self::$allowedFormats[$this->getShortMimeType(0)])) {
				throw new \Exception('Illegal format');
			}
		}
	}

	/**
	 * Validate image
	 * @throws \Exception
	 */
	private function validateImage()
	{
		if (!getimagesize($this->path)) {
			throw new \Exception('Wrong image');
		}
		if (preg_match('[\x01-\x08\x0c-\x1f]', $this->getContents())) {
			throw new \Exception('Wrong image');
		}
	}

	/**
	 * Validate code injection
	 * @throws \Exception
	 */
	private function validateCodeInjection()
	{
		if ($this->validateAllCodeInjection || in_array($this->getShortMimeType(0), self::$phpInjection)) {
			// Check for php code injection
			$content = $this->getContents();
			if (preg_match('/(<\?php?(.*?))/i', $content) === 1 || preg_match('/(<?script(.*?)language(.*?)=(.*?)"(.*?)php(.*?)"(.*?))/i', $content) === 1 || stripos($content, '<?=') !== false || stripos($content, '<? ') !== false || stripos($content, '<% ') !== false) {
				throw new \Exception('Error php code injection');
			}
			if (function_exists('exif_read_data') && ($this->mimeType === 'image/jpeg' || $this->mimeType === 'image/tiff') && in_array(exif_imagetype($this->path), [IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM])) {
				$exifdata = exif_read_data($this->path);
				if ($exifdata && !$this->validateImageMetadata($exifdata)) {
					throw new \Exception('Error php code injection');
				}
			}
			if (stripos('<?xpacket', $content) !== false) {
				throw new \Exception('Error xpacket code injection');
			}
		}
	}

	/**
	 * Validate image metadata
	 * @param mixed $data
	 * @return boolean
	 */
	private function validateImageMetadata($data)
	{
		if (is_array($data)) {
			foreach ($data as $value) {
				if (!$this->validateImageMetadata($value)) {
					return false;
				}
			}
		} else {
			if (preg_match('/(<\?php?(.*?))/i', $data) === 1 || preg_match('/(<?script(.*?)language(.*?)=(.*?)"(.*?)php(.*?)"(.*?))/i', $data) === 1 || stripos($data, '<?=') !== false || stripos($data, '<? ') !== false || stripos($data, '<% ') !== false) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Get file ontent
	 * @return string
	 */
	public function getContents()
	{
		if (empty($this->content)) {
			$this->content = file_get_contents($this->path);
		}
		return $this->content;
	}

	/**
	 * Move file
	 * @param string $target
	 * @return boolean
	 */
	public function moveFile($target)
	{
		if (is_uploaded_file($this->path)) {
			$uploadStatus = move_uploaded_file($this->path, $target);
		} else {
			$uploadStatus = rename($this->path, $target);
		}
		return $uploadStatus;
	}

	/**
	 * Function to sanitize the upload file name when the file name is detected to have bad extensions
	 * @param string $fileName File name to be sanitized
	 * @param string|boolean $badFileExtensions
	 * @return string
	 */
	public static function sanitizeUploadFileName($fileName, $badFileExtensions = false)
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

	/**
	 * Init mime types
	 */
	public static function initMimeTypes()
	{
		if (empty(self::$mimeTypes)) {
			self::$mimeTypes = require 'config/mimetypes.php';
		}
	}

	/**
	 * Get mime content type
	 * @param string $fileName
	 * @return string
	 */
	public static function getMimeContentType($fileName)
	{
		static::initMimeTypes();
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

	/**
	 * Create document from string
	 * @param string $content
	 * @param array $params
	 * @return boolean|array
	 */
	public static function saveFromString($content, $params = [])
	{
		$result = explode(',', $content, 2);
		$contentType = $isBase64 = false;
		if (count($result) === 2) {
			list($metadata, $data) = $result;
			foreach (explode(';', $metadata) as $cur) {
				if ($cur === 'base64') {
					$isBase64 = true;
				} elseif (substr($cur, 0, 5) === 'data:') {
					$contentType = str_replace('data:', '', $cur);
				}
			}
		} else {
			$data = $result[0];
		}
		$data = rawurldecode($data);
		$rawData = $isBase64 ? base64_decode($data) : $data;
		if (strlen($rawData) < 12) {
			return false;
		}
		static::initMimeTypes();
		if (!$ext = array_search($contentType, self::$mimeTypes)) {
			list($type, $ext) = explode('/', $contentType);
		}
		$fileName = uniqid() . '.' . $ext;
		return static::saveFromContent($rawData, $fileName, $contentType, $params);
	}

	/**
	 * Create document from url
	 * @param string $url Url
	 * @param array $params
	 * @return boolean|array
	 */
	public static function saveFromUrl($url, $params = [])
	{
		if (empty($url)) {
			return false;
		}
		$arrHeader = @get_headers($url, 1);
		if (strpos($arrHeader[0], '200') === false) {
			return false;
		}
		$content = file_get_contents($url);
		if (empty($content)) {
			return false;
		}
		return static::saveFromContent($content, basename($url), false, $params);
	}

	/**
	 * Create document from content
	 * @param string $content
	 * @param string $fileName
	 * @param string|boolean $contentType
	 * @param array $params
	 * @return boolean|array
	 */
	public static function saveFromContent($content, $fileName, $contentType = false, $params = [])
	{
		$name = \vtlib\Functions::textLength($fileName, 50, false);
		$filePath = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $name;
		$success = file_put_contents($filePath, $content);
		if (!$success) {
			return false;
		}
		$record = \Vtiger_Record_Model::getCleanInstance('Documents');
		$record->setData($params);
		$record->set('notes_title', $fileName);
		$record->set('filename', $name);
		$record->set('filestatus', 1);
		$record->set('filelocationtype', 'I');
		$record->set('folderid', 'T2');
		$record->file = [
			'name' => $fileName,
			'size' => filesize($filePath),
			'type' => $contentType ? $contentType : static::getMimeContentType($filePath),
			'tmp_name' => $filePath,
			'error' => 0
		];
		$record->save();
		if (isset($record->ext['attachmentsId'])) {
			return array_merge(['crmid' => $record->getId()], $record->ext);
		}
		return false;
	}

	/**
	 * Init storage file directory
	 * @param string $module
	 * @return string
	 */
	public static function initStorageFileDirectory($module = false)
	{
		$filepath = 'storage' . DIRECTORY_SEPARATOR;
		if ($module && in_array($module, ['Users', 'Contacts', 'Products', 'OSSMailView', 'MultiImage'])) {
			$filepath .= $module . DIRECTORY_SEPARATOR;
		}
		if (!is_dir($filepath)) { //create new folder
			mkdir($filepath);
		}
		$year = date('Y');
		$month = date('F');
		$day = date('j');
		$week = '';
		$filepath .= $year;
		if (!is_dir($filepath)) { //create new folder
			mkdir($filepath);
		}
		$filepath .= DIRECTORY_SEPARATOR . $month;
		if (!is_dir($filepath)) { //create new folder
			mkdir($filepath);
		}
		if ($day > 0 && $day <= 7) {
			$week = 'week1';
		} elseif ($day > 7 && $day <= 14) {
			$week = 'week2';
		} elseif ($day > 14 && $day <= 21) {
			$week = 'week3';
		} elseif ($day > 21 && $day <= 28) {
			$week = 'week4';
		} else {
			$week = 'week5';
		}
		$filepath .= DIRECTORY_SEPARATOR . $week;
		if (!is_dir($filepath)) { //create new folder
			mkdir($filepath);
		}
		return $filepath . DIRECTORY_SEPARATOR;
	}
}
