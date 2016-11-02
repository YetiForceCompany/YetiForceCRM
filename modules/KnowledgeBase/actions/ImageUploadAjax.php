<?php

/**
 * Action to upload file
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztofgastolek@wars.pl>
 */
class KnowledgeBase_ImageUploadAjax_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleName);
		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$fileTypeSettings = AppConfig::module($moduleName, 'fileTypeSettings');
		$allowedFileTypes = AppConfig::module($moduleName, 'allowedFileTypes');
		$rename = AppConfig::module($moduleName, 'rename');
		$iConf = &$fileTypeSettings['img'];
		$aConf = &$fileTypeSettings['audio'];
		$vConf = &$fileTypeSettings['video'];
		$response = false;

		if (isset($_FILES['upload']) && strlen($_FILES['upload']['name']) > 1) {
			// Getting filename without extension
			$fileName = preg_replace('/\.(.+?)$/i', '', basename($_FILES['upload']['name']));
			// Getting protocol and host name to send the absolute image path to CKEditor
			$protocol = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
			$site = $protocol . $_SERVER['SERVER_NAME'] . '/';
			$sepext = explode('.', strtolower($_FILES['upload']['name']));
			// Getting extension
			$type = end($sepext);
			$fileType = false;
			// Checking if the file is allowed
			foreach ($fileTypeSettings as $fileTypeName => $settings) {
				if (in_array($fileTypeName, $allowedFileTypes)) {
					if (in_array($type, $settings['type'])) {
						$fileType = $fileTypeName;
						$uploadDir = trim($settings['dir'], '/') . '/';
						break;
					}
				}
			}
			$fileInstance = \App\Fields\File::loadFromRequest($_FILES['upload']);
			if (!$fileInstance->validate()) {
				return false;
			}
			switch ($fileType) {
				default:
					$response = vtranslate('ERR_NOT_ALLOWED', $moduleName);
					break;
				case 'img':
					// Image width and height
					$saveFile = 'true';
					if (!$fileInstance->validate('image')) {
						return false;
					} else {
						list($width, $height) = getimagesize($_FILES['upload']['tmp_name']);
						if (isset($width) && isset($height)) {
							if ($width > $iConf['maxwidth'] || $height > $iConf['maxheight']) {
								$response = '\\n ' . vtranslate('LBL_WIDTH_HEIGHT', $moduleName) . ' = ' . $width . ' x ' . $height . ' \\n '
									. vtranslate('LBL_ALLOWED_WIDTH_HEIGHT', $moduleName) . ': ' . $iConf['maxwidth'] . ' x ' . $iConf['maxheight'];
							}
							if ($width < $iConf['minwidth'] || $height < $iConf['minheight']) {
								$response = '\\n ' . vtranslate('LBL_WIDTH_HEIGHT', $moduleName) . ' = ' . $width . ' x ' . $height . '\\n '
									. vtranslate('LBL_ALLOWED_WIDTH_HEIGHT', $moduleName) . ': ' . $iConf['minwidth'] . ' x ' . $iConf['minheight'];
							}
							if ($_FILES['upload']['size'] > $iConf['maxsize'] * 1000) {
								$response = '\\n ' . vtranslate('LBL_MAX_FILE_SIZE', $moduleName) . ': ' . $iConf['maxsize'] . ' KB.';
							}
						}
					}
					break;
				case 'audio':
					if ($_FILES['upload']['size'] > $aConf['maxsize'] * 1000) {
						$response = '\\n ' . vtranslate('LBL_MAX_FILE_SIZE', $moduleName) . ': ' . $aConf['maxsize'] . ' KB.';
					}
					break;
				case 'video':
					if ($_FILES['upload']['size'] > $vConf['maxsize'] * 1000) {
						$response = '\\n ' . vtranslate('LBL_MAX_FILE_SIZE', $moduleName) . ': ' . $vConf['maxsize'] . ' KB.';
					}
					break;
			}

			$fullUploadDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $uploadDir;
			$newFileName = $this->setFileName($fullUploadDir, $fileName, ".$type", 0, $rename);
			// Full file path
			$newFileName = \App\Fields\File::sanitizeUploadFileName($newFileName);
			$uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $uploadDir . $newFileName;
			// If there is no errors no errors, upload the image, else, output the errors
			if (!$response) {
				if (move_uploaded_file($_FILES['upload']['tmp_name'], $uploadPath)) {
					$CKEditorFuncNum = $_GET['CKEditorFuncNum'];
					$url = $site . $uploadDir . $newFileName;
					$msg = $fileName . '.' . $type . ' ' . vtranslate('LBL_FILE_UPLOADED', $moduleName) . ': \\n-';
					$msg.= vtranslate('LBL_SIZE', $moduleName) . ': ' . number_format($_FILES['upload']['size'] / 1024, 2, '.', '') . ' KB';

					switch ($fileType) {
						case 'img':
							$response = "window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')";
							break;
						case 'audio':
							$response = 'var cke_ob = window.parent.CKEDITOR;'
								. 'for(var ckid in cke_ob.instances)'
								. '{if(cke_ob.instances[ckid].focusManager.hasFocus) break;}'
								. 'cke_ob.instances[ckid].insertHtml'
								. '(\'<div><audio src="' . $url . '" controls></audio></div><p></p>\', \'unfiltered_html\');'
								. 'alert("' . $msg . '");'
								. 'var dialog = cke_ob.dialog.getCurrent();'
								. 'dialog.hide()';
							break;
						case 'video':
							$response = 'var cke_ob = window.parent.CKEDITOR;'
								. 'for(var ckid in cke_ob.instances)'
								. '{if(cke_ob.instances[ckid].focusManager.hasFocus) break;}'
								. 'cke_ob.instances[ckid].insertHtml'
								. '(\'<div><video src="' . $url . '" class="' . $vConf['tagclass'] . '" controls></video></div><p></p>\', \'unfiltered_html\');'
								. 'alert("' . $msg . '");'
								. 'var dialog = cke_ob.dialog.getCurrent();'
								. 'dialog.hide()';
							break;
					}
				} else {
					$response = 'alert("' . vtranslate('ERR_UNABLE_TO_UPLOAD', $moduleName) . '")';
				}
			} else {
				$response = 'alert("' . $response . '")';
			}
		}
		$vtigerResponse = new Vtiger_Response();
		$vtigerResponse->setResult('<script type="text/javascript">' . $response . '</script>');
		$vtigerResponse->setEmitType(2);
		$vtigerResponse->emit();
	}

	/**
	 * Sets filename
	 * @param string $dirPath directory path
	 * @param string $fileName filename to check
	 * @param string $extension extension
	 * @param int $i index to rename
	 * @param string $fileName filename
	 * @param int $rename checks if file should be rename or overwrite 
	 * @return string filename with extension
	 */
	public function setFileName($dirPath, $fileName, $extension, $i, $rename)
	{
		if ($rename == 1 && file_exists($dirPath . $fileName . $extension)) {
			$ending = '_' . $i;
			while (file_exists($dirPath . $fileName . $ending . $extension)) {
				$ending = '_' . $i;
				$i++;
			}
			return $fileName . $ending . $extension;
		} else {
			return $fileName . $extension;
		}
	}
}
