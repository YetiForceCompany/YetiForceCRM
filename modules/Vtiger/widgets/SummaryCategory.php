<?php

/**
 * Vtiger SummaryCategory widget class.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_SummaryCategory_Widget extends Vtiger_Basic_Widget
{
	/** {@inheritdoc} */
	public function isPermitted(): bool
	{
		return parent::isPermitted() && $this->isExistsSummaryBlocks();
	}

	/**
	 * Verification if there is a directory with summary data and if it contains files.
	 *
	 * @return bool
	 */
	public function isExistsSummaryBlocks(): bool
	{
		$fileExists = [];
		$dirs[] = "modules/{$this->Module}/summary_blocks/";
		if (\App\Config::performance('LOAD_CUSTOM_FILES')) {
			$dirs[] = "custom/modules/{$this->Module}/summary_blocks/";
		}
		foreach ($dirs as $path) {
			if (!is_dir($path)) {
				continue;
			}
			foreach ((new DirectoryIterator($path)) as $fileinfo) {
				if ($fileinfo->getBasename('.php') && !$fileinfo->isDot()) {
					$fileExists[] = $fileinfo->getFilename();
				}
			}
		}

		return !empty($fileExists);
	}

	public function getWidget()
	{
		$this->Config['tpl'] = 'SummaryCategory.tpl';

		return $this->Config;
	}

	public function getConfigTplName()
	{
		return 'SummaryCategoryConfig';
	}
}
