<?php

namespace App\Fields;

use App\Log;

/**
 * File class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class File
{
	/**
	 * Allowed formats.
	 *
	 * @var string[]
	 */
	public static $allowedFormats = ['image' => ['jpeg', 'png', 'jpg', 'pjpeg', 'x-png', 'gif', 'bmp', 'x-ms-bmp']];

	/**
	 * Mime types.
	 *
	 * @var string[]
	 */
	private static $mimeTypes;

	/**
	 * What file types to validate by php injection.
	 *
	 * @var string[]
	 */
	private static $phpInjection = ['image'];

	/**
	 * Directory path used for temporary files.
	 *
	 * @var string
	 */
	private static $tmpPath;

	/**
	 * File path.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * File extension.
	 *
	 * @var string
	 */
	private $ext;

	/**
	 * File mime type.
	 *
	 * @var string
	 */
	private $mimeType;

	/**
	 * File short mime type.
	 *
	 * @var string
	 */
	private $mimeShortType;

	/**
	 * Size.
	 *
	 * @var int
	 */
	private $size;

	/**
	 * File content.
	 *
	 * @var string
	 */
	private $content;

	/**
	 * Error code.
	 *
	 * @var int|bool
	 */
	private $error = false;

	/**
	 * Last validate error.
	 *
	 * @var string
	 */
	public $validateError = '';

	/**
	 * Validate all files by code injection.
	 *
	 * @var bool
	 */
	private $validateAllCodeInjection = false;

	/**
	 * Load file instance from file info.
	 *
	 * @param array $fileInfo
	 *
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
	 * Load file instance from request.
	 *
	 * @param array $file
	 *
	 * @return \self
	 */
	public static function loadFromRequest($file)
	{
		$instance = new self();
		$instance->name = trim(\App\Purifier::purify($file['name']));
		$instance->path = $file['tmp_name'];
		$instance->size = $file['size'];
		$instance->error = $file['error'];
		return $instance;
	}

	/**
	 * Load file instance from file path.
	 *
	 * @param array $path
	 *
	 * @return \self
	 */
	public static function loadFromPath($path)
	{
		$instance = new self();
		$instance->name = basename($path);
		$instance->path = $path;
		return $instance;
	}

	/**
	 * Load file instance from content.
	 *
	 * @param string   $content
	 * @param string   $name
	 * @param string[] $param
	 *
	 * @return bool|\self
	 */
	public static function loadFromContent($content, $name = false, $param = [])
	{
		$ext = 'tmp';
		if (empty($name)) {
			static::initMimeTypes();
			if (!empty($param['mimeShortType']) && !($ext = array_search($param['mimeShortType'], static::$mimeTypes))) {
				list(, $ext) = explode('/', $param['mimeShortType']);
			}
			$name = uniqid() . '.' . $ext;
		}
		if ($ext === 'tmp' && ($fileExt = pathinfo($name, PATHINFO_EXTENSION))) {
			$ext = $fileExt;
		}
		$path = tempnam(static::getTmpPath(), 'YFF');
		$success = file_put_contents($path, $content);
		if (!$success) {
			Log::error('Error while saving the file: ' . $path, __CLASS__);
			return false;
		}
		$instance = new self();
		$instance->name = $name;
		$instance->path = $path;
		$instance->ext = $ext;
		if (isset($param['mimeShortType'])) {
			$instance->mimeType = $param['mimeShortType'];
		}
		foreach ($param as $key => $value) {
			$instance->$key = $value;
		}
		return $instance;
	}

	/**
	 * Load file instance from url.
	 *
	 * @param string   $url
	 * @param string[] $param
	 *
	 * @return bool
	 */
	public static function loadFromUrl($url, $param = [])
	{
		if (empty($url)) {
			Log::error('No url: ' . $url, __CLASS__);
			return false;
		}
		try {
			$responsse = \Requests::get($url, [], ['timeout' => 5, 'connect_timeout' => 5]);
			if ($responsse->status_code !== 200) {
				Log::error('Error when downloading content: ' . $url . ' | Status code: ' . $responsse->status_code, __CLASS__);
				return false;
			}
			$content = $responsse->body;
		} catch (\Exception $exc) {
			Log::error('Error when downloading content: ' . $url . ' | ' . $exc->getMessage(), __CLASS__);
			return false;
		}
		if (empty($content)) {
			Log::error('Url does not contain content: ' . $url, __CLASS__);
			return false;
		}
		return static::loadFromContent($content, basename($url), $param);
	}

	/**
	 * Get size.
	 *
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
	 * Function to sanitize the upload file name when the file name is detected to have bad extensions.
	 *
	 * @return string
	 */
	public function getSanitizeName()
	{
		return self::sanitizeUploadFileName($this->name);
	}

	/**
	 * Get file name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get mime type.
	 *
	 * @return string
	 */
	public function getMimeType()
	{
		if (empty($this->mimeType)) {
			static::initMimeTypes();
			$ext = $this->getExtension(true);
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
	 * Get short mime type.
	 *
	 * @param int $type 0 or 1
	 *
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
	 * Get file extension.
	 *
	 * @return string
	 */
	public function getExtension($fromName = false)
	{
		if (isset($this->ext)) {
			return $this->ext;
		}
		if ($fromName) {
			$ext = explode('.', $this->name);
			return $this->ext = strtolower(array_pop($ext));
		}
		return $this->ext = strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
	}

	/**
	 * Get file path.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Get directory path.
	 *
	 * @return string
	 */
	public function getDirectoryPath()
	{
		return pathinfo($this->getPath(), PATHINFO_DIRNAME);
	}

	/**
	 * Validate whether the file is safe.
	 *
	 * @param bool|string $type
	 *
	 * @throws \Exception
	 *
	 * @return bool
	 */
	public function validate($type = false)
	{
		$return = true;
		Log::trace('File validate - Start', __CLASS__);
		try {
			$this->checkFile();
			$this->validateFormat();
			$this->validateCodeInjection();
			if (($type && $type === 'image') || $this->getShortMimeType(0) === 'image') {
				$this->validateImage();
			}
			if ($type && $this->getShortMimeType(0) !== $type) {
				throw new \Exception('Wrong file type');
			}
		} catch (\Exception $e) {
			$return = false;
			$message = $e->getMessage();
			if (strpos($message, '||') === false) {
				$message = \App\Language::translateSingleMod($message, 'Other.Exceptions');
			} else {
				$params = explode('||', $message);
				$message = call_user_func_array('vsprintf', [\App\Language::translateSingleMod(array_shift($params), 'Other.Exceptions'), $params]);
			}
			$this->validateError = $message;
			Log::error('Error: ' . $message, __CLASS__);
		}
		Log::trace('File validate - End', __CLASS__);
		return $return;
	}

	/**
	 * Basic check file.
	 *
	 * @throws \Exception
	 */
	private function checkFile()
	{
		if ($this->error !== false && $this->error != UPLOAD_ERR_OK) {
			throw new \Exception('ERR_FILE_ERROR_REQUEST||' . $this->getErrorMessage($this->error));
		}
		if (empty($this->name)) {
			throw new \Exception('ERR_FILE_EMPTY_NAME');
		}
		if ($this->getSize() === 0) {
			throw new \Exception('ERR_FILE_WRONG_SIZE');
		}
	}

	/**
	 * Validate format.
	 *
	 * @throws \Exception
	 */
	private function validateFormat()
	{
		if (isset(self::$allowedFormats[$this->getShortMimeType(0)])) {
			if (!in_array($this->getShortMimeType(1), self::$allowedFormats[$this->getShortMimeType(0)])) {
				throw new \Exception('ERR_FILE_ILLEGAL_FORMAT');
			}
		}
	}

	/**
	 * Validate image.
	 *
	 * @throws \Exception
	 */
	private function validateImage()
	{
		if (!getimagesize($this->path)) {
			throw new \Exception('ERR_FILE_WRONG_IMAGE');
		}
		if (preg_match('[\x01-\x08\x0c-\x1f]', $this->getContents())) {
			throw new \Exception('ERR_FILE_WRONG_IMAGE');
		}
	}

	/**
	 * Validate code injection.
	 *
	 * @throws \Exception
	 */
	private function validateCodeInjection()
	{
		if ($this->validateAllCodeInjection || in_array($this->getShortMimeType(0), self::$phpInjection)) {
			// Check for php code injection
			$content = $this->getContents();
			if (preg_match('/(<\?php?(.*?))/s', $content) === 1 || preg_match('/(<?script(.*?)language(.*?)=(.*?)"(.*?)php(.*?)"(.*?))/s', $content) === 1 || stripos($content, '<?=') !== false || stripos($content, '<%=') !== false || stripos($content, '<? ') !== false || stripos($content, '<% ') !== false) {
				throw new \Exception('ERR_FILE_PHP_CODE_INJECTION');
			}
			if (function_exists('exif_read_data') && ($this->mimeType === 'image/jpeg' || $this->mimeType === 'image/tiff') && in_array(exif_imagetype($this->path), [IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM])) {
				$exifdata = exif_read_data($this->path);
				if ($exifdata && !$this->validateImageMetadata($exifdata)) {
					throw new \Exception('ERR_FILE_PHP_CODE_INJECTION');
				}
			}
			if (stripos('<?xpacket', $content) !== false) {
				throw new \Exception('ERR_FILE_XPACKET_CODE_INJECTION');
			}
		}
	}

	/**
	 * Validate image metadata.
	 *
	 * @param mixed $data
	 *
	 * @return bool
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
			if (preg_match('/(<\?php?(.*?))/i', $data) === 1 || preg_match('/(<?script(.*?)language(.*?)=(.*?)"(.*?)php(.*?)"(.*?))/i', $data) === 1 || stripos($data, '<?=') !== false || stripos($data, '<%=') !== false || stripos($data, '<? ') !== false || stripos($data, '<% ') !== false) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Get file ontent.
	 *
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
	 * Move file.
	 *
	 * @param string $target
	 *
	 * @return bool
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
	 * Delete file.
	 *
	 * @return bool
	 */
	public function delete()
	{
		if (file_exists($this->path)) {
			return unlink($this->path);
		}
		return false;
	}

	/**
	 * Generate file hash.
	 *
	 * @param bool   $checkInAttachments
	 * @param string $uploadFilePath
	 *
	 * @return string File hash sha256
	 */
	public function generateHash(bool $checkInAttachments = false, string $uploadFilePath = '')
	{
		if ($checkInAttachments) {
			$hash = hash('sha1', $this->getContents()) . \App\Encryption::generatePassword(10);
			if ((new \App\Db\Query())->from('u_#__file_upload_temp')->where(['key' => $hash])->exists() || ($uploadFilePath && file_exists($uploadFilePath . $hash))) {
				$hash = $this->generateHash($checkInAttachments);
			}
			return $hash;
		}
		return hash('sha256', $this->getContents() . \App\Encryption::generatePassword(10));
	}

	/**
	 * Function to sanitize the upload file name when the file name is detected to have bad extensions.
	 *
	 * @param string      $fileName          File name to be sanitized
	 * @param string|bool $badFileExtensions
	 *
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
	 * Get temporary directory path.
	 *
	 * @return string
	 */
	public static function getTmpPath()
	{
		if (isset(static::$tmpPath)) {
			return static::$tmpPath;
		}
		$hash = hash('crc32', ROOT_DIRECTORY);
		if (!empty(ini_get('upload_tmp_dir')) && is_writable(ini_get('upload_tmp_dir'))) {
			static::$tmpPath = ini_get('upload_tmp_dir') . DIRECTORY_SEPARATOR . 'YetiForceTemp' . $hash . DIRECTORY_SEPARATOR;
			if (!is_dir(static::$tmpPath)) {
				mkdir(static::$tmpPath, 0755);
			}
		} elseif (is_writable(sys_get_temp_dir())) {
			static::$tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'YetiForceTemp' . $hash . DIRECTORY_SEPARATOR;
			if (!is_dir(static::$tmpPath)) {
				mkdir(static::$tmpPath, 0755);
			}
		} elseif (is_writable(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'upload')) {
			static::$tmpPath = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR;
		}

		return static::$tmpPath;
	}

	/**
	 * Init mime types.
	 */
	public static function initMimeTypes()
	{
		if (empty(self::$mimeTypes)) {
			self::$mimeTypes = require \ROOT_DIRECTORY . '/config/mimetypes.php';
		}
	}

	/**
	 * Get mime content type ex. image/png.
	 *
	 * @param string $fileName
	 *
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
	 * Create document from string.
	 *
	 * @param string $content
	 * @param array  $params
	 *
	 * @return bool|array
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
			Log::error('Incorrect content value: ' . $content, __CLASS__);

			return false;
		}
		$fileInstance = static::loadFromContent($rawData, false, ['mimeShortType' => $contentType]);
		if ($fileInstance->validate() && ($id = static::saveFromContent($fileInstance, $params))) {
			return $id;
		}

		return false;
	}

	/**
	 * Create document from url.
	 *
	 * @param string $url    Url
	 * @param array  $params
	 *
	 * @return bool|array
	 */
	public static function saveFromUrl($url, $params = [])
	{
		$fileInstance = static::loadFromUrl($url);
		if (empty($url) || !$fileInstance) {
			Log::error('Invalid url: ' . $url, __CLASS__);

			return false;
		}
		if ($fileInstance->validate() && ($id = static::saveFromContent($fileInstance, $params))) {
			return $id;
		}

		return false;
	}

	/**
	 * Create document from content.
	 *
	 * @param \self $file
	 * @param array $params
	 *
	 * @return bool
	 */
	public static function saveFromContent(self $file, $params = [])
	{
		$fileName = \App\TextParser::textTruncate($file->getName(), 50, false);
		$record = \Vtiger_Record_Model::getCleanInstance('Documents');
		$record->setData($params);
		$record->set('notes_title', $fileName);
		$record->set('filename', $file->getName());
		$record->set('filestatus', 1);
		$record->set('filelocationtype', 'I');
		$record->set('folderid', 'T2');
		$record->file = [
			'name' => $fileName,
			'size' => $file->getSize(),
			'type' => $file->getMimeType(),
			'tmp_name' => $file->getPath(),
			'error' => 0,
		];
		$record->save();
		$file->delete();
		if (isset($record->ext['attachmentsId'])) {
			return array_merge(['crmid' => $record->getId()], $record->ext);
		}

		return false;
	}

	/**
	 * Init storage file directory.
	 *
	 * @param string $suffix
	 *
	 * @return string
	 */
	public static function initStorageFileDirectory($suffix = false)
	{
		$filepath = 'storage' . DIRECTORY_SEPARATOR;
		if ($suffix) {
			$filepath .= $suffix . DIRECTORY_SEPARATOR;
		}
		if (!is_dir($filepath)) { //create new folder
			mkdir($filepath, 0755, true);
		}
		$year = date('Y');
		$month = date('F');
		$day = date('j');
		$filepath .= $year;
		if (!is_dir($filepath)) { //create new folder
			mkdir($filepath, 0755, true);
		}
		$filepath .= DIRECTORY_SEPARATOR . $month;
		if (!is_dir($filepath)) { //create new folder
			mkdir($filepath, 0755, true);
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
			mkdir($filepath, 0755, true);
		}
		return $filepath . DIRECTORY_SEPARATOR;
	}

	/**
	 * Get error message by code.
	 *
	 * @param int $code
	 *
	 * @return string
	 */
	private function getErrorMessage($code)
	{
		switch ($code) {
			case UPLOAD_ERR_INI_SIZE:
				$message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = 'The uploaded file was only partially uploaded';
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = 'No file was uploaded';
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = 'Missing a temporary folder';
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = 'Failed to write file to disk';
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = 'File upload stopped by extension';
				break;
			default:
				$message = 'Unknown upload error';
				break;
		}
		return $message;
	}

	/**
	 * Get image base data.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function getImageBaseData($path)
	{
		if ($path) {
			$mime = static::getMimeContentType($path);
			$mimeParts = explode('/', $mime);
			if ($mime && file_exists($path) && isset(static::$allowedFormats[$mimeParts[0]]) && in_array($mimeParts[1], static::$allowedFormats[$mimeParts[0]])) {
				return "data:$mime;base64," . base64_encode(file_get_contents($path));
			}
		}
		return '';
	}

	/**
	 * Check if give path is writeable.
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function isWriteable($path)
	{
		$path = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $path;
		if (is_dir($path)) {
			return static::isDirWriteable($path);
		} else {
			return is_writable($path);
		}
	}

	/**
	 * Check if given directory is writeable.
	 * NOTE: The check is made by trying to create a random file in the directory.
	 *
	 * @param string $dirPath
	 *
	 * @return bool
	 */
	public static function isDirWriteable($dirPath)
	{
		if (is_dir($dirPath)) {
			do {
				$tmpFile = 'tmpfile' . time() . '-' . rand(1, 1000) . '.tmp';
				// Continue the loop unless we find a name that does not exists already.
				$useFilename = "$dirPath/$tmpFile";
				if (!file_exists($useFilename)) {
					break;
				}
			} while (true);
			$fh = fopen($useFilename, 'a');
			if ($fh) {
				fclose($fh);
				unlink($useFilename);

				return true;
			}
		}

		return false;
	}

	/**
	 * Check if give URL exists.
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public static function isExistsUrl($url)
	{
		try {
			$response = \Requests::get($url);
			if ($response->status_code === 200) {
				return true;
			} else {
				Log::error('Checked URL is not allowed: ' . $url . ' | Status code: ' . $response->status_code, __CLASS__);

				return false;
			}
		} catch (\Exception $exc) {
			Log::error('Checked URL is not allowed: ' . $url . ' | ' . $exc->getMessage(), __CLASS__);
			return false;
		}
	}

	/**
	 * Get crm pathname.
	 *
	 * @param string $path Absolute pathname
	 *
	 * @return string Local pathname
	 */
	public static function getLocalPath($path)
	{
		if (strpos($path, ROOT_DIRECTORY) === 0) {
			$index = strlen(ROOT_DIRECTORY) + 1;
			if (strrpos(ROOT_DIRECTORY, '/') === strlen(ROOT_DIRECTORY) - 1) {
				$index -= 1;
			}
			$path = substr($path, $index);
		}
		return $path;
	}

	/**
	 * Transform mulitiple uploaded file information into useful format.
	 *
	 * @param array $files $_FILES
	 * @param bool  $top
	 *
	 * @return array
	 */
	public static function transform(array $files, $top = true)
	{
		$rows = [];
		foreach ($files as $name => $file) {
			$subName = $top ? $file['name'] : $name;
			if (is_array($subName)) {
				foreach (array_keys($subName) as $key) {
					$rows[$name][$key] = [
						'name' => $file['name'][$key],
						'type' => $file['type'][$key],
						'tmp_name' => $file['tmp_name'][$key],
						'error' => $file['error'][$key],
						'size' => $file['size'][$key],
					];
					$rows[$name] = static::transform($rows[$name], false);
				}
			} else {
				$rows[$name] = $file;
			}
		}
		return $rows;
	}

	/**
	 * Upload and save attachment.
	 *
	 * @param \App\Request $request
	 * @param array        $files
	 * @param string       $type
	 * @param string       $storageName
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \Exception
	 * @throws \yii\db\Exception
	 *
	 * @return array
	 */
	public static function uploadAndSave(\App\Request $request, array $files, string $type, string $storageName)
	{
		$db = \App\Db::getInstance();
		$attach = [];
		foreach (static::transform($files, true) as $key => $transformFiles) {
			foreach ($transformFiles as $fileDetails) {
				$file = static::loadFromRequest($fileDetails);
				if (!$file->validate($type)) {
					$attach[] = ['name' => $file->getName(), 'error' => $file->validateError, 'hash' => $request->getByType('hash', 'Text')];
					continue;
				}
				$uploadFilePath = static::initStorageFileDirectory($storageName);
				$key = $file->generateHash(true, $uploadFilePath);
				$db->createCommand()->insert('u_#__file_upload_temp', [
					'name' => $file->getName(),
					'type' => $file->getMimeType(),
					'path' => $uploadFilePath,
					'createdtime' => date('Y-m-d H:i:s'),
					'fieldname' => $request->getByType('field', 'Alnum'),
					'key' => $key,
					'crmid' => $request->isEmpty('record') ? 0 : $request->getInteger('record'),
				])->execute();
				if (move_uploaded_file($file->getPath(), $uploadFilePath . $key)) {
					$attach[] = [
						'name' => $file->getName(),
						'size' => \vtlib\Functions::showBytes($file->getSize()),
						'key' => $key,
						'hash' => $request->getByType('hash', 'string')
					];
				} else {
					$db->createCommand()->delete('u_#__file_upload_temp', ['key' => $key])->execute();
					Log::error("Moves an uploaded file to a new location failed: {$uploadFilePath}");
					$attach[] = ['hash' => $request->getByType('hash', 'Text'), 'name' => $file->getName(), 'error' => ''];
				}
			}
		}
		return $attach;
	}

	/**
	 * Update upload files.
	 *
	 * @param array                $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @param \Vtiger_Field_Model  $fieldModel
	 *
	 * @return array
	 */
	public static function updateUploadFiles(array $value, \Vtiger_Record_Model $recordModel, \Vtiger_Field_Model $fieldModel)
	{
		$previousValue = $recordModel->getPreviousValue($fieldModel->getName());
		$previousValue = $previousValue ? static::parse($previousValue) : [];
		$value = static::parse($value);
		foreach ($value as $item) {
			if (!isset($previousValue[$item['key']]) && ($uploadFile = static::getUploadFile($item['key']))) {
				$value[$item['key']]['path'] = $uploadFile['path'] . $item['key'];
			}
		}
		foreach ($previousValue as $item) {
			if (!isset($value[$item['key']])) {
				static::removeUploadFile($item);
			}
		}
		return array_values($value);
	}

	/**
	 * Parse.
	 *
	 * @param array $value
	 *
	 * @return array
	 */
	public static function parse(array $value)
	{
		return array_reduce($value, function ($result, $item) {
			$result[$item['key']] = $item;
			return $result;
		}, []);
	}

	/**
	 * Get upload file details from db.
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public static function getUploadFile(string $key)
	{
		$row = (new \App\Db\Query())->from('u_#__file_upload_temp')->where(['key' => $key])->one();
		return $row ?: [];
	}

	/**
	 * Remove file.
	 *
	 * @param array $value
	 */
	public static function removeUploadFile(array $value)
	{
		\unlink($value['path']);
	}
}
