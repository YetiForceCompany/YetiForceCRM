<?php

/**
 * ZipReader class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Import_ZipReader_Reader extends Import_FileReader_Reader
{
	protected $moduleName;
	protected $importFolderLocation;
	protected $filelist = [];

	/**
	 * Construct.
	 *
	 * @param \App\Request $request
	 * @param \App\User    $user
	 */
	public function __construct(App\Request $request, App\User $user)
	{
		$instance = Vtiger_Cache::get('ZipReader', $request->getModule() . $user->getId());
		if (!empty($instance)) {
			$this->setInstanceProperties($instance);
			$this->request = $request;
			return;
		}
		$this->moduleName = $request->getModule();
		$this->extension = $request->getByType('extension');
		$allowedExtension = static::getAllowedExtension();
		if (!isset($allowedExtension[$this->extension])) {
			\App\Log::error('purifyByType: ' . $this->extension, 'IllegalValue');
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $this->extension, 406);
		}
		parent::__construct($request, $user);
		$this->initialize($request, $user);
		Vtiger_Cache::set('ZipReader', $this->moduleName . $user->getId(), $this);
	}

	/**
	 * Returns allowed extension files in zip package.
	 *
	 * @return string[]
	 */
	public static function getAllowedExtension()
	{
		return ['xml' => 'XML'];
	}

	public function setInstanceProperties($instance)
	{
		$objectProperties = get_object_vars($instance);
		foreach ($objectProperties as $properName => $propertyValue) {
			$this->{$properName} = $propertyValue;
		}
	}

	/**
	 * Initialize zip file.
	 *
	 * @param \App\Request $request
	 * @param \App\User    $user
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function initialize(App\Request $request, App\User $user)
	{
		$zipfile = Import_Utils_Helper::getImportFilePath($user);
		$this->importFolderLocation = "{$zipfile}_{$this->extension}";
		// clean old data
		if ('uploadAndParse' === $request->getMode()) {
			$this->deleteFolder();
		}
		if ($this->extension && file_exists($zipfile) && !file_exists($this->importFolderLocation)) {
			mkdir($this->importFolderLocation);
			$zip = \App\Zip::openFile($zipfile, ['onlyExtensions' => [$this->extension]]);
			$this->filelist = $zip->unzip($this->importFolderLocation);
			unlink($zipfile);
		} elseif (is_dir($this->importFolderLocation)) {
			foreach (new DirectoryIterator($this->importFolderLocation) as $file) {
				if (!$file->isDot() && false !== strpos($file->getFilename(), '.' . $this->extension)) {
					$this->filelist[] = $file->getFilename();
				}
			}
		}
	}

	public function hasHeader()
	{
		return true;
	}

	public function checkExtension($filelist)
	{
		$return = true;
		foreach ($filelist as $name) {
			$nameArray = explode('.', $name);
			if (strtolower(array_pop($nameArray)) != strtolower($this->extension)) {
				$return = false;
				break;
			}
		}
		return $return;
	}

	public function getFirstRowData($hasHeader = true)
	{
		$data = $this->request->getAll();
		$newRequest = new \App\Request($data, false);
		$newRequest->set('type', $this->extension);
		$fileReader = Import_Module_Model::getFileReader($newRequest, $this->user);
		if (!$fileReader) {
			return false;
		}
		$filePath = $this->getNextFile(false);
		if (!$filePath) {
			$this->deleteFolder();

			return false;
		}
		$fileReader->filePath = $filePath;

		return $fileReader->getFirstRowData($hasHeader);
	}

	public function getNextFile($del = true)
	{
		$return = false;
		foreach ($this->filelist as $name) {
			$filePatch = $this->importFolderLocation . DIRECTORY_SEPARATOR . $name;
			if (file_exists($filePatch) && $this->checkExtension([$name])) {
				$return = $filePatch;
				if ($del) {
					unset($this->filelist[$name]);
				}
				break;
			}
			unset($this->filelist[$name]);
		}
		return $return;
	}

	public function read()
	{
		$data = $this->request->getAll();
		$newRequest = new \App\Request($data, false);
		$newRequest->set('type', $this->extension);
		$fileReader = Import_Module_Model::getFileReader($newRequest, $this->user);
		if (!$fileReader) {
			return false;
		}
		while ($filePath = $this->getNextFile()) {
			$fileReader->filePath = $filePath;
			$fileReader->read();
			$fileReader->deleteFile();
		}
		$this->deleteFolder();
	}

	public function deleteFolder()
	{
		if (!empty($this->importFolderLocation)) {
			\vtlib\Functions::recurseDelete($this->importFolderLocation, true);
		}
	}
}
