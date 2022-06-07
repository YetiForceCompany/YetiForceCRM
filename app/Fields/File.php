<?php
/**
 * Tool file for the field type `File`.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Fields;

use App\Log;

/**
 * Tool class for the field type `File`.
 */
class File
{
	/** @var string Temporary table name. */
	public const TABLE_NAME_TEMP = 'u_#__file_upload_temp';

	/**
	 * Allowed formats.
	 *
	 * @var array
	 */
	public static $allowedFormats = ['image' => ['jpeg', 'png', 'jpg', 'pjpeg', 'x-png', 'gif', 'bmp', 'x-ms-bmp', 'webp']];

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
	 * File name.
	 *
	 * @var string
	 */
	private $name;

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
	 * @var bool|int
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
			$instance->{$key} = $fileInfo[$key];
		}
		if (isset($instance->name)) {
			$instance->name = trim(\App\Purifier::purify($instance->name));
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
	 * @param string $path
	 *
	 * @return \self
	 */
	public static function loadFromPath(string $path)
	{
		$instance = new self();
		$instance->name = trim(\App\Purifier::purify(basename($path)));
		$instance->path = $path;
		return $instance;
	}

	/**
	 * Load file instance from base string.
	 *
	 * @param string $contents
	 * @param array  $param
	 *
	 * @return \self|null
	 */
	public static function loadFromBase(string $contents, array $param = []): ?self
	{
		$result = explode(',', $contents, 2);
		$contentType = $isBase64 = false;
		if (2 === \count($result)) {
			[$metadata, $data] = $result;
			foreach (explode(';', $metadata) as $cur) {
				if ('base64' === $cur) {
					$isBase64 = true;
				} elseif ('data:' === substr($cur, 0, 5)) {
					$contentType = str_replace('data:', '', $cur);
				}
			}
		} else {
			$data = $result[0];
		}
		$data = rawurldecode($data);
		$rawData = $isBase64 ? base64_decode($data) : $data;
		if (\strlen($rawData) < 12) {
			Log::error('Incorrect content value: ' . $contents, __CLASS__);
			return null;
		}
		return static::loadFromContent($rawData, false, array_merge($param, ['mimeType' => $contentType]));
	}

	/**
	 * Load file instance from content.
	 *
	 * @param string   $contents
	 * @param string   $name
	 * @param string[] $param
	 *
	 * @return bool|\self
	 */
	public static function loadFromContent(string $contents, $name = false, array $param = [])
	{
		if (empty($contents)) {
			Log::warning("Empty content, unable to create file: $name | Size: " . \strlen($contents), __CLASS__);
			return false;
		}
		static::initMimeTypes();
		$extension = 'tmp';
		if (empty($name)) {
			if (!empty($param['mimeType']) && !($extension = array_search($param['mimeType'], self::$mimeTypes))) {
				[, $extension] = explode('/', $param['mimeType']);
			}
			$name = uniqid() . '.' . $extension;
		} elseif ('tmp' === $extension) {
			if (($fileExt = pathinfo($name, PATHINFO_EXTENSION)) && isset(self::$mimeTypes[$fileExt])) {
				$extension = $fileExt;
				if (isset($param['mimeType']) && $param['mimeType'] !== self::$mimeTypes[$fileExt]) {
					Log::error("Invalid file content type File: $name  | {$param['mimeType']} <> " . self::$mimeTypes[$fileExt], __CLASS__);
					return false;
				}
			} elseif (!empty($param['mimeType']) && !($extension = array_search($param['mimeType'], self::$mimeTypes))) {
				[, $extension] = explode('/', $param['mimeType']);
			}
		}
		$path = tempnam(static::getTmpPath(), 'YFF');
		if (!file_put_contents($path, $contents)) {
			Log::error("Error while saving the file: $path | Size: " . \strlen($contents), __CLASS__);
			return false;
		}
		if (mb_strlen($name) > 180) {
			$name = \App\TextUtils::textTruncate($name, 180, false) . '_' . uniqid() . ".$extension";
		}
		$instance = new self();
		$instance->name = trim(\App\Purifier::purify($name));
		$instance->path = $path;
		$instance->ext = $extension;
		foreach ($param as $key => $value) {
			$instance->{$key} = $value;
		}
		return $instance;
	}

	/**
	 * Load file instance from url.
	 *
	 * @param string   $url
	 * @param string[] $param
	 *
	 * @return self|bool
	 */
	public static function loadFromUrl($url, $param = [])
	{
		if (empty($url)) {
			Log::warning('No url: ' . $url, __CLASS__);
			return false;
		}
		if (!\App\RequestUtil::isNetConnection()) {
			return false;
		}
		try {
			\App\Log::beginProfile("GET|File::loadFromUrl|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url, ['timeout' => 5, 'connect_timeout' => 1]);
			\App\Log::endProfile("GET|File::loadFromUrl|{$url}", __NAMESPACE__);
			if (200 !== $response->getStatusCode()) {
				Log::warning('Error when downloading content: ' . $url . ' | Status code: ' . $response->getStatusCode(), __CLASS__);
				return false;
			}
			$contents = $response->getBody()->getContents();
			$param['mimeType'] = explode(';', $response->getHeaderLine('Content-Type'))[0];
			$param['size'] = \strlen($contents);
		} catch (\Throwable $exc) {
			Log::warning('Error when downloading content: ' . $url . ' | ' . $exc->getMessage(), __CLASS__);
			return false;
		}
		if (empty($contents)) {
			Log::warning('Url does not contain content: ' . $url, __CLASS__);
			return false;
		}
		return static::loadFromContent($contents, static::sanitizeFileNameFromUrl($url), $param);
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
		return static::sanitizeUploadFileName($this->name);
	}

	/**
	 * Get file name.
	 *
	 * @param bool $decode
	 *
	 * @return string
	 */
	public function getName(bool $decode = false)
	{
		return $decode ? \App\Purifier::decodeHtml($this->name) : $this->name;
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
			$extension = $this->getExtension(true);
			if (isset(static::$mimeTypes[$extension])) {
				$this->mimeType = static::$mimeTypes[$extension];
			} elseif (\function_exists('mime_content_type')) {
				$this->mimeType = mime_content_type($this->path);
			} elseif (\function_exists('finfo_open')) {
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
	 * @param mixed $fromName
	 *
	 * @return string
	 */
	public function getExtension($fromName = false)
	{
		if (isset($this->ext)) {
			return $this->ext;
		}
		if ($fromName) {
			$extension = explode('.', $this->name);
			return $this->ext = strtolower(array_pop($extension));
		}
		return $this->ext = strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
	}

	/**
	 * Get file path.
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * Get file encoding.
	 *
	 * @param array|null $list
	 *
	 * @return string
	 */
	public function getEncoding(?array $list = null): string
	{
		return \strtoupper(mb_detect_encoding($this->getContents(), ($list ?? mb_list_encodings()), true));
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
	 * @param string|null $type
	 *
	 * @throws \Exception
	 *
	 * @return bool
	 */
	public function validate(?string $type = null): bool
	{
		$return = true;
		try {
			if ($type && $this->getShortMimeType(0) !== $type) {
				throw new \App\Exceptions\DangerousFile('ERR_FILE_ILLEGAL_FORMAT');
			}
			$this->checkFile();
			if (!empty($this->validateAllowedFormat)) {
				$this->validateFormat();
			}
			$this->validateCodeInjection();
			if (($type && 'image' === $type) || 'image' === $this->getShortMimeType(0)) {
				$this->validateImage();
			}
		} catch (\Exception $e) {
			$return = false;
			$message = $e->getMessage();
			if (false === strpos($message, '||')) {
				$message = \App\Language::translateSingleMod($message, 'Other.Exceptions');
			} else {
				$params = explode('||', $message);
				$message = \call_user_func_array('vsprintf', [\App\Language::translateSingleMod(array_shift($params), 'Other.Exceptions'), $params]);
			}
			$this->validateError = $message;
			Log::error("Error during file validation: {$this->getName()} | Size: {$this->getSize()}\n {$e->__toString()}", __CLASS__);
		}
		return $return;
	}

	/**
	 * Validate and secure the file.
	 *
	 * @param string|null $type
	 *
	 * @return bool
	 */
	public function validateAndSecure(?string $type = null): bool
	{
		if ($this->validate($type)) {
			return true;
		}
		$reValidate = false;
		if (static::secureFile($this)) {
			$this->size = filesize($this->path);
			$this->content = file_get_contents($this->path);
			$reValidate = true;
		}
		if ($reValidate && $this->validate($type)) {
			return true;
		}
		return false;
	}

	/**
	 * Validate image content.
	 *
	 * @throws \App\Exceptions\DangerousFile
	 *
	 * @return bool
	 */
	public function validateImageContent(): bool
	{
		$returnVal = false;
		if (\extension_loaded('imagick')) {
			try {
				$img = new \imagick($this->path);
				$returnVal = $img->valid();
				$img->clear();
				$img->destroy();
			} catch (\ImagickException $e) {
				$this->validateError = $e->getMessage();
				$returnVal = false;
			}
		} else {
			$img = \imagecreatefromstring($this->getContents());
			if (false !== $img) {
				$returnVal = true;
				\imagedestroy($img);
			}
		}
		return $returnVal;
	}

	/**
	 * Basic check file.
	 *
	 * @throws \Exception
	 */
	private function checkFile()
	{
		if (false !== $this->error && UPLOAD_ERR_OK != $this->error) {
			throw new \App\Exceptions\DangerousFile('ERR_FILE_ERROR_REQUEST||' . self::getErrorMessage($this->error));
		}
		if (empty($this->name)) {
			throw new \App\Exceptions\DangerousFile('ERR_FILE_EMPTY_NAME');
		}
		if (!$this->validateInjection($this->name)) {
			throw new \App\Exceptions\DangerousFile('ERR_FILE_ILLEGAL_NAME');
		}
		if (0 === $this->getSize()) {
			throw new \App\Exceptions\DangerousFile('ERR_FILE_WRONG_SIZE');
		}
	}

	/**
	 * Validate format.
	 *
	 * @throws \Exception
	 */
	private function validateFormat()
	{
		if ($this->validateAllowedFormat !== $this->getShortMimeType(0)) {
			throw new \App\Exceptions\DangerousFile('ERR_FILE_ILLEGAL_MIME_TYPE');
		}
		if (isset(self::$allowedFormats[$this->validateAllowedFormat]) && !\in_array($this->getShortMimeType(1), self::$allowedFormats[$this->validateAllowedFormat])) {
			throw new \App\Exceptions\DangerousFile('ERR_FILE_ILLEGAL_FORMAT');
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
			throw new \App\Exceptions\DangerousFile('ERR_FILE_WRONG_IMAGE');
		}
		if (preg_match('[\x01-\x08\x0c-\x1f]', $this->getContents())) {
			throw new \App\Exceptions\DangerousFile('ERR_FILE_WRONG_IMAGE');
		}
		$this->validateCodeInjectionInMetadata();
		if (!$this->validateImageContent()) {
			throw new \App\Exceptions\DangerousFile('ERR_FILE_WRONG_IMAGE ||' . $this->validateError);
		}
	}

	/**
	 * Validate code injection.
	 *
	 * @throws \Exception
	 */
	private function validateCodeInjection()
	{
		$shortMimeType = $this->getShortMimeType(0);
		if ($this->validateAllCodeInjection || \in_array($shortMimeType, static::$phpInjection)) {
			$contents = $this->getContents();
			if ((1 === preg_match('/(<\?php?(.*?))/si', $contents)
			|| false !== stripos($contents, '<?=')
			|| false !== stripos($contents, '<? ')) && $this->searchCodeInjection()
			) {
				throw new \App\Exceptions\DangerousFile('ERR_FILE_CODE_INJECTION');
			}
		}
	}

	/**
	 * Search code injection in content.
	 *
	 * @return bool
	 */
	private function searchCodeInjection(): bool
	{
		if (!\function_exists('token_get_all')) {
			return true;
		}
		try {
			$tokens = token_get_all($this->getContents(), TOKEN_PARSE);
			foreach ($tokens as $token) {
				switch (\is_array($token) ? $token[0] : $token) {
						case T_COMMENT:
						case T_DOC_COMMENT:
						case T_WHITESPACE:
						case T_CURLY_OPEN:
						case T_OPEN_TAG:
						case T_CLOSE_TAG:
						case T_INLINE_HTML:
						case T_DOLLAR_OPEN_CURLY_BRACES:
							continue 2;
						case T_DOUBLE_COLON:
						case T_ABSTRACT:
						case T_ARRAY:
						case T_AS:
						case T_BREAK:
						case T_CALLABLE:
						case T_CASE:
						case T_CATCH:
						case T_CLASS:
						case T_CLONE:
						case T_CONTINUE:
						case T_DEFAULT:
						case T_ECHO:
						case T_ELSE:
						case T_ELSEIF:
						case T_EMPTY:
						case T_ENDIF:
						case T_ENDSWITCH:
						case T_ENDWHILE:
						case T_EXIT:
						case T_EXTENDS:
						case T_FINAL:
						case T_FINALLY:
						case T_FOREACH:
						case T_FUNCTION:
						case T_GLOBAL:
						case T_IF:
						case T_IMPLEMENTS:
						case T_INCLUDE:
						case T_INCLUDE_ONCE:
						case T_INSTANCEOF:
						case T_INSTEADOF:
						case T_INTERFACE:
						case T_ISSET:
						case T_LOGICAL_AND:
						case T_LOGICAL_OR:
						case T_LOGICAL_XOR:
						case T_NAMESPACE:
						case T_NEW:
						case T_PRIVATE:
						case T_PROTECTED:
						case T_PUBLIC:
						case T_REQUIRE:
						case T_REQUIRE_ONCE:
						case T_RETURN:
						case T_STATIC:
						case T_THROW:
						case T_TRAIT:
						case T_TRY:
						case T_UNSET:
						case T_USE:
						case T_VAR:
						case T_WHILE:
						case T_YIELD:
							return true;
						default:
							$text = \is_array($token) ? $token[1] : $token;
							if (\function_exists($text) || \defined($text)) {
								return true;
							}
					}
			}
		} catch (\Throwable $e) {
			Log::warning($e->getMessage(), __METHOD__);
		}
		return false;
	}

	/**
	 * Validate code injection in metadata.
	 *
	 * @throws \App\Exceptions\DangerousFile
	 */
	private function validateCodeInjectionInMetadata()
	{
		if (\extension_loaded('imagick')) {
			try {
				$img = new \imagick($this->path);
				$this->validateInjection($img->getImageProperties());
			} catch (\Throwable $e) {
				throw new \App\Exceptions\DangerousFile('ERR_FILE_CODE_INJECTION', $e->getCode(), $e);
			}
		} elseif (
			\function_exists('exif_read_data')
			&& \in_array($this->getMimeType(), ['image/jpeg', 'image/tiff'])
			&& \in_array(exif_imagetype($this->path), [IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM])
		) {
			$imageSize = getimagesize($this->path, $imageInfo);
			try {
				if (
					$imageSize
					&& (empty($imageInfo['APP1']) || 0 === strpos($imageInfo['APP1'], 'Exif'))
					&& ($exifData = exif_read_data($this->path)) && !$this->validateInjection($exifData)
				) {
					throw new \App\Exceptions\DangerousFile('ERR_FILE_CODE_INJECTION');
				}
			} catch (\Throwable $e) {
				throw new \App\Exceptions\DangerousFile('ERR_FILE_CODE_INJECTION', $e->getCode(), $e);
			}
		}
	}

	/**
	 * Validate injection.
	 *
	 * @param string|array $data
	 *
	 * @return bool
	 */
	private function validateInjection($data): bool
	{
		$return = true;
		if (\is_array($data)) {
			foreach ($data as $value) {
				if (!$this->validateInjection($value)) {
					return false;
				}
			}
		} else {
			if (1 === preg_match('/(<\?php?(.*?))/i', $data) || false !== stripos($data, '<?=') || false !== stripos($data, '<? ')) {
				$return = false;
			} else {
				\App\Purifier::purifyHtmlEventAttributes($data);
			}
		}
		return $return;
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
		$this->path = $target;
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
			if ($uploadFilePath && file_exists($uploadFilePath . $hash)) {
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
	 * @param bool|string $badFileExtensions
	 *
	 * @return string
	 */
	public static function sanitizeUploadFileName($fileName, $badFileExtensions = false)
	{
		if (!$badFileExtensions) {
			$badFileExtensions = \App\Config::main('upload_badext');
		}
		$fileName = preg_replace('/\s+/', '_', \App\Utils::sanitizeSpecialChars($fileName));
		$fileName = rtrim($fileName, '\\/<>?*:"<>|');

		$fileNameParts = explode('.', $fileName);
		$badExtensionFound = false;
		foreach ($fileNameParts as $key => &$partOfFileName) {
			if (\in_array(strtolower($partOfFileName), $badFileExtensions)) {
				$badExtensionFound = true;
				$fileNameParts[$key] = $partOfFileName;
			}
		}
		$newFileName = implode('.', $fileNameParts);
		if ($badExtensionFound) {
			$newFileName .= '.txt';
		}
		return $newFileName;
	}

	/**
	 * Function to get base name of file.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function sanitizeFileNameFromUrl($url)
	{
		$partsUrl = parse_url($url);
		return static::sanitizeUploadFileName(basename($partsUrl['path']));
	}

	/**
	 * Get temporary directory path.
	 *
	 * @return string
	 */
	public static function getTmpPath()
	{
		if (isset(self::$tmpPath)) {
			return self::$tmpPath;
		}
		$hash = hash('crc32', ROOT_DIRECTORY);
		if (!empty(ini_get('upload_tmp_dir')) && is_writable(ini_get('upload_tmp_dir'))) {
			self::$tmpPath = ini_get('upload_tmp_dir') . \DIRECTORY_SEPARATOR . 'YetiForceTemp' . $hash . \DIRECTORY_SEPARATOR;
			if (!is_dir(self::$tmpPath)) {
				mkdir(self::$tmpPath, 0755);
			}
		} elseif (is_writable(sys_get_temp_dir())) {
			self::$tmpPath = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'YetiForceTemp' . $hash . \DIRECTORY_SEPARATOR;
			if (!is_dir(self::$tmpPath)) {
				mkdir(self::$tmpPath, 0755);
			}
		} elseif (is_writable(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'upload')) {
			self::$tmpPath = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'upload' . \DIRECTORY_SEPARATOR;
		}
		return self::$tmpPath;
	}

	/**
	 * Init mime types.
	 */
	public static function initMimeTypes()
	{
		if (empty(self::$mimeTypes)) {
			self::$mimeTypes = require ROOT_DIRECTORY . '/config/mimetypes.php';
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
		$extension = explode('.', $fileName);
		$extension = strtolower(array_pop($extension));
		if (isset(self::$mimeTypes[$extension])) {
			$mimeType = self::$mimeTypes[$extension];
		} elseif (\function_exists('mime_content_type')) {
			$mimeType = mime_content_type($fileName);
		} elseif (\function_exists('finfo_open')) {
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
	 * @param string $contents
	 * @param array  $param
	 *
	 * @return bool|self
	 */
	public static function saveFromString(string $contents, array $param = [])
	{
		$fileInstance = static::loadFromBase($contents, $param);
		if ($fileInstance->validateAndSecure()) {
			return $fileInstance;
		}
		$fileInstance->delete();
		return false;
	}

	/**
	 * Create document from url.
	 *
	 * @param string $url    Url
	 * @param array  $params
	 *
	 * @return array|bool
	 */
	public static function saveFromUrl($url, $params = [])
	{
		$fileInstance = static::loadFromUrl($url, $params['param'] ?? []);
		if (!$fileInstance) {
			return false;
		}
		if ($fileInstance->validateAndSecure() && ($id = static::saveFromContent($fileInstance, $params))) {
			return $id;
		}
		$fileInstance->delete();
		return false;
	}

	/**
	 * Create document from content.
	 *
	 * @param \self $file
	 * @param array $params
	 *
	 * @throws \Exception
	 *
	 * @return array|bool
	 */
	public static function saveFromContent(self $file, $params = [])
	{
		$fileName = $file->getName();
		$fileNameLength = \App\TextUtils::getTextLength($fileName);
		$record = \Vtiger_Record_Model::getCleanInstance('Documents');
		if ($fileNameLength > ($maxLength = $record->getField('filename')->getMaxValue())) {
			$extLength = 0;
			if ($ext = $file->getExtension()) {
				$ext .= ".{$ext}";
				$extLength = \App\TextUtils::getTextLength($ext);
				$fileName = substr($fileName, 0, $fileNameLength - $extLength);
			}
			$fileName = \App\TextUtils::textTruncate($fileName, $maxLength - $extLength, false) . $ext;
		}
		$fileName = \App\Purifier::decodeHtml(\App\Purifier::purify($fileName));
		$record->setData($params);
		$record->set('notes_title', ($params['titlePrefix'] ?? '') . $fileName);
		$record->set('filename', $fileName);
		$record->set('filestatus', 1);
		$record->set('filelocationtype', 'I');
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
	 * Init storage diractory.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function initStorage(string $path): string
	{
		if ('/' !== substr($path, '-1')) {
			$path .= '/';
		}
		$result = 0 === strpos($path, 'storage/') || 0 === strpos($path, 'public_html/storage/');
		if ($result && !is_dir($path)) {
			$result = mkdir($path, 0755, true);
		}

		return $result ? $path : '';
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
		if (!$filepath = \App\Config::module($suffix, 'storagePath')) {
			$filepath = 'storage' . \DIRECTORY_SEPARATOR;
		}
		if ($suffix) {
			$filepath .= $suffix . \DIRECTORY_SEPARATOR;
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
		$filepath .= \DIRECTORY_SEPARATOR . $month;
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
		$filepath .= \DIRECTORY_SEPARATOR . $week;
		if (!is_dir($filepath)) { //create new folder
			mkdir($filepath, 0755, true);
		}
		return str_replace('\\', '/', $filepath . \DIRECTORY_SEPARATOR);
	}

	/**
	 * Get error message by code.
	 *
	 * @param int $code
	 *
	 * @return string
	 */
	public static function getErrorMessage(int $code): string
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
				$message = 'Unknown upload error | Code: ' . $code;
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
			if ($mime && file_exists($path) && isset(static::$allowedFormats[$mimeParts[0]]) && \in_array($mimeParts[1], static::$allowedFormats[$mimeParts[0]])) {
				return "data:$mime;base64," . base64_encode(file_get_contents($path));
			}
		}
		return '';
	}

	/**
	 * Check if give path is writeable.
	 *
	 * @param string $path
	 * @param bool   $absolutePaths
	 *
	 * @return bool
	 */
	public static function isWriteable(string $path, bool $absolutePaths = false): bool
	{
		if (!$absolutePaths) {
			$path = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . $path;
		}
		if (is_dir($path)) {
			return static::isDirWriteable($path);
		}
		return is_writable($path);
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
				$tmpFile = 'tmpfile' . time() . '-' . random_int(1, 1000) . '.tmp';
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
		\App\Log::beginProfile("GET|File::isExistsUrl|{$url}", __NAMESPACE__);
		try {
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('HEAD', $url, ['timeout' => 1, 'connect_timeout' => 1, 'verify' => false, 'http_errors' => false, 'allow_redirects' => false]);
			$status = \in_array($response->getStatusCode(), [200, 302]);
		} catch (\Throwable $th) {
			$status = false;
		}
		\App\Log::endProfile("GET|File::isExistsUrl|{$url}", __NAMESPACE__);
		\App\Log::info("Checked URL: $url | Status: " . $status, __CLASS__);
		return $status;
	}

	/**
	 * Get crm pathname or relative path.
	 *
	 * @param string $path       Absolute pathname
	 * @param string $pathToTrim Path to trim
	 *
	 * @return string Local pathname
	 */
	public static function getLocalPath(string $path, string $pathToTrim = ROOT_DIRECTORY): string
	{
		if (0 === strpos($path, $pathToTrim)) {
			$index = \strlen($pathToTrim) + 1;
			if (strrpos($pathToTrim, '/') === \strlen($pathToTrim) - 1) {
				--$index;
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
			if (\is_array($subName)) {
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
	 * Delete data from the temporary table.
	 *
	 * @param string|string[] $keys
	 *
	 * @return int
	 */
	public static function cleanTemp($keys)
	{
		return \App\Db::getInstance()->createCommand()->delete(static::TABLE_NAME_TEMP, ['key' => $keys])->execute();
	}

	/**
	 * Add an entry to the temporary table of files.
	 *
	 * @param array $params
	 *
	 * @return int
	 */
	public function insertTempFile(array $params): int
	{
		$db = \App\Db::getInstance();
		$result = 0;
		$data = [
			'name' => $this->getName(true),
			'type' => $this->getMimeType(),
			'path' => null,
			'createdtime' => date('Y-m-d H:i:s'),
			'fieldname' => null,
			'key' => null,
			'crmid' => 0,
		];
		foreach ($data as $key => &$value) {
			if (isset($params[$key])) {
				$value = $params[$key];
			}
		}
		if ($db->createCommand()->insert(static::TABLE_NAME_TEMP, $data)->execute()) {
			$result = $db->getLastInsertID(static::TABLE_NAME_TEMP . '_id_seq');
		}
		return $result;
	}

	/**
	 * Add an entry to the media table of files.
	 *
	 * @param array $params
	 *
	 * @return int
	 */
	public function insertMediaFile(array $params): int
	{
		$db = \App\Db::getInstance();
		$result = 0;
		$data = [
			'name' => $this->getName(true),
			'type' => $this->getMimeType(),
			'path' => null,
			'ext' => $this->getExtension(),
			'createdtime' => date('Y-m-d H:i:s'),
			'fieldname' => '',
			'key' => null,
			'status' => 0,
			'user' => \App\User::getCurrentUserRealId()
		];
		foreach ($data as $key => &$value) {
			if (isset($params[$key])) {
				$value = $params[$key];
			}
		}
		if ($db->createCommand()->insert(\App\Layout\Media::TABLE_NAME_MEDIA, $data)->execute()) {
			$result = $db->getLastInsertID(\App\Layout\Media::TABLE_NAME_MEDIA . '_id_seq');
		}

		return $result;
	}

	/**
	 * Secure image file.
	 *
	 * @param \App\Fields\File $file
	 *
	 * @return bool
	 */
	public static function secureFile(self $file): bool
	{
		if ('image' !== $file->getShortMimeType(0)) {
			return false;
		}
		$result = false;
		if (\extension_loaded('imagick')) {
			try {
				$img = new \imagick($file->getPath());
				$img->stripImage();
				switch ($file->getExtension()) {
					case 'jpg':
					case 'jpeg':
						$img->setImageCompression(\Imagick::COMPRESSION_JPEG);
						$img->setImageCompressionQuality(99);
						break;
					default:
						break;
				}
				$img->writeImage($file->getPath());
				$img->clear();
				$img->destroy();
				$result = true;
			} catch (\ImagickException $e) {
				$result = false;
			}
		} else {
			if (\in_array($file->getExtension(), ['jpeg', 'png', 'gif', 'bmp', 'wbmp', 'gd2', 'webp'])) {
				$img = \imagecreatefromstring($file->getContents());
				if (false !== $img) {
					switch ($file->getExtension()) {
						case 'jpg':
						case 'jpeg':
							$result = \imagejpeg($img, $file->getPath());
							break;
						case 'png':
							$result = \imagepng($img, $file->getPath());
							break;
						case 'gif':
							$result = \imagegif($img, $file->getPath());
							break;
						case 'bmp':
							$result = \imagebmp($img, $file->getPath());
							break;
						default:
							break;
					}
					\imagedestroy($img);
				}
			}
		}
		return $result;
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
			if (isset($item['key'])) {
				$result[$item['key']] = $item;
			}
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
		$row = (new \App\Db\Query())->from(static::TABLE_NAME_TEMP)->where(['key' => $key])->one();
		return $row ?: [];
	}

	/**
	 * Check is it an allowed directory.
	 *
	 * @param string $fullPath
	 *
	 * @return bool
	 */
	public static function isAllowedDirectory(string $fullPath)
	{
		return !(!is_readable($fullPath) || !is_dir($fullPath) || is_file($fullPath));
	}

	/**
	 * Check is it an allowed file directory.
	 *
	 * @param string $fullPath
	 *
	 * @return bool
	 */
	public static function isAllowedFileDirectory(string $fullPath)
	{
		return !(!is_readable($fullPath) || is_dir($fullPath) || !is_file($fullPath));
	}

	/**
	 * Creates a temporary file.
	 *
	 * @param string $prefix The prefix of the generated temporary filename Note: Windows uses only the first three characters of prefix
	 * @param string $ext    File extension, default: .tmp
	 *
	 * @return string The new temporary filename (with path), or throw an exception on failure
	 */
	public static function createTempFile(string $prefix = '', string $ext = 'tmp'): string
	{
		return (new \Symfony\Component\Filesystem\Filesystem())->tempnam(self::getTmpPath(), $prefix, '.' . $ext);
	}

	/**
	 * Delete files from record.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function deleteForRecord(\Vtiger_Record_Model $recordModel)
	{
		foreach ($recordModel->getModule()->getFieldsByType(['multiAttachment', 'multiImage', 'image']) as $fieldModel) {
			if (!$recordModel->isEmpty($fieldModel->getName()) && !\App\Json::isEmpty($recordModel->get($fieldModel->getName()))) {
				foreach (\App\Json::decode($recordModel->get($fieldModel->getName())) as $file) {
					$path = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . $file['path'];
					if (file_exists($path)) {
						unlink($path);
					} else {
						\App\Log::warning('Deleted file does not exist: ' . print_r($file, true));
					}
				}
			}
		}
	}
}
