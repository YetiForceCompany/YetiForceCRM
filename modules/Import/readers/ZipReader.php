<?php

/**
 * ZipReader Class
 * @package YetiForce.Reader
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Import_ZipReader_Reader extends Import_FileReader_Reader
{

	protected $moduleName;
	protected $importFolderLocation;
	protected $filelist = [];

	public function __construct(Vtiger_Request $request, $user)
	{
		$instance = Vtiger_Cache::get('ZipReader', $request->get('module') . $user->id);
		if (!empty($instance)) {
			$this->setInstanceProperties($instance);
			$this->request = $request;
			return;
		}
		$this->moduleName = $request->get('module');
		$this->extension = $request->get('extension');
		parent::__construct($request, $user);
		$this->initialize($request, $user);
		Vtiger_Cache::set('ZipReader', $this->moduleName . $user->id, $this);
	}

	public function setInstanceProperties($instance)
	{
		$objectProperties = get_object_vars($instance);
		foreach ($objectProperties as $properName => $propertyValue) {
			$this->$properName = $propertyValue;
		}
	}

	public function initialize($request, $user)
	{
		$zipfile = Import_Utils_Helper::getImportFilePath($user);
		$this->importFolderLocation = $zipfile . '_' . $user->id;
		// clean old data
		if ($request->getMode() == 'uploadAndParse') {
			$this->deleteFolder();
		}
		if ($this->extension && file_exists($zipfile) && !file_exists($this->importFolderLocation)) {
			mkdir($this->importFolderLocation);
			$unzip = new vtlib\Unzip($zipfile);
			$unzip->unzipAllEx($this->importFolderLocation);
			foreach ($unzip->getList() as $name => $data) {
				$this->filelist[] = $name;
			}
			$unzip->__destroy();
			unlink($zipfile);
		} elseif (is_dir($this->importFolderLocation)) {
			foreach (new DirectoryIterator($this->importFolderLocation) as $file) {
				if (!$file->isDot()) {
					if (strpos($file->getFilename(), '.xml') !== false) {
						$this->filelist[] = $file->getFilename();
					}
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

	public function getFirstRowData($hasHeader)
	{
		$data = $this->request->getAll();
		$newRequest = new Vtiger_Request($data);
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
		$newRequest = new Vtiger_Request($data);
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
		if (!empty($this->importFolderLocation) && is_dir($this->importFolderLocation)) {
			$dirs[] = $this->importFolderLocation;
			foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->importFolderLocation, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
				if ($item->isDir()) {
					$dirs[] = $this->importFolderLocation . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
				} else {
					unlink($this->importFolderLocation . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
				}
			}
			arsort($dirs);
			foreach ($dirs as $dir) {
				rmdir($dir);
			}
		}
	}
}
