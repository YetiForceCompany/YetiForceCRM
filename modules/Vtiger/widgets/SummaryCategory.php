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
		$path = "modules/{$this->Module}/summary_blocks/";
		if (\App\Config::performance('LOAD_CUSTOM_FILES')) {
			$customPath = "custom/$path";
			$path = is_dir($customPath) ? $customPath : $path;
		}
		return is_dir($path) ?? $path;
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
