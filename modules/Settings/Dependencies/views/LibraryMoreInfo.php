<?php

/**
 * Library More Info View Class.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Dependencies_LibraryMoreInfo_View extends Settings_Vtiger_BasicModal_View
{
	/**
	 * Public libraries package files.
	 *
	 * @var string[]
	 */
	public $packageFiles = ['package.json', 'composer.json', 'bower.json'];

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$result = false;
		$fileContent = '';
		if (!$request->isEmpty('type') && !$request->isEmpty('libraryName')) {
			$type = $request->getByType('type', \App\Purifier::STANDARD);
			if ('public' === $type) {
				$dir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR;
				$libraryName = $request->getByType('libraryName', \App\Purifier::PATH);
				foreach ($this->packageFiles as $file) {
					$packageFile = $dir . $libraryName . DIRECTORY_SEPARATOR . $file;
					if (file_exists($packageFile) && \App\Fields\File::isAllowedFileDirectory($packageFile)) {
						$fileContent = file_get_contents($packageFile);
						$result = true;
						break;
					}
				}
			} elseif ('vendor' === $type) {
				$filePath = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . $request->getByType('libraryName', \App\Purifier::PATH) . DIRECTORY_SEPARATOR . 'composer.json';
				if (file_exists($filePath) && \App\Fields\File::isAllowedFileDirectory($filePath)) {
					$fileContent = file_get_contents($filePath);
					$result = true;
				}
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
