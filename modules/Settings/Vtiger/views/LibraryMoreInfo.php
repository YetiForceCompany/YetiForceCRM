<?php

/**
 * Library More Info View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Vtiger_LibraryMoreInfo_View extends Vtiger_BasicModal_View
{
	/**
	 * Public libraries package files.
	 *
	 * @var string[]
	 */
	public $packageFiles = ['package.json', 'composer.json', 'bower.json'];

	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$result = false;
		$fileContent = '';
		if ($request->isEmpty('type') || $request->isEmpty('libraryName')) {
			$result = false;
		} else {
			if ($request->getByType('type', 1) === 'public') {
				$dir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR;
				$libraryName = $request->getByType('libraryName', 'Text');
				foreach ($this->packageFiles as $file) {
					$packageFile = $dir . $libraryName . DIRECTORY_SEPARATOR . $file;
					if ($fileContent) {
						continue;
					}
					if (file_exists($packageFile)) {
						$fileContent = file_get_contents($packageFile);
						$result = true;
					} else {
						$result = false;
					}
				}
			} elseif ($request->getByType('type', 1) === 'vendor') {
				$filePath = 'vendor' . DIRECTORY_SEPARATOR . $request->getByType('libraryName', 'Text') . DIRECTORY_SEPARATOR . 'composer.json';
				if (file_exists($filePath)) {
					$fileContent = file_get_contents($filePath);
					$result = true;
				} else {
					$result = false;
				}
			} else {
				$result = false;
			}
		}
		$this->preProcess($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('RESULT', $result);
		$viewer->assign('FILE_CONTENT', $fileContent);
		$viewer->view('LibraryMoreInfo.tpl', $qualifiedModuleName);
		$this->postProcess($request);
	}
}
