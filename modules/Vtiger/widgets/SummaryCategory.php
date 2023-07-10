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
	/** @var int Number of rows visible per line */
	const SUMMARY_ROW_COUNT = 4;

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
		$existsFiles = false;
		if (\App\Config::performance('LOAD_CUSTOM_FILES')) {
			$dirs[] = "custom/modules/{$this->Module}/summary_blocks/";
		}
		$dirs[] = "modules/{$this->Module}/summary_blocks/";
		foreach ($dirs as $path) {
			if (!is_dir($path)) {
				continue;
			}
			foreach ((new DirectoryIterator($path)) as $fileInfo) {
				if (!$fileInfo->isDot() && 'php' === $fileInfo->getExtension()) {
					$existsFiles = true;
					break 2;
				}
			}
		}
		return $existsFiles;
	}

	/**
	 * Function fetches data about the data summary widget.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return array
	 */
	public function getSummaryInfo(Vtiger_Record_Model $recordModel): array
	{
		$moduleName = $recordModel->getModuleName();
		$existsFiles = [];
		if (\App\Config::performance('LOAD_CUSTOM_FILES')) {
			$dirs[] = "custom/modules/$moduleName/summary_blocks/";
		}
		$dirs[] = "modules/$moduleName/summary_blocks/";
		$tempSummaryBlocks = [];
		foreach ($dirs as $path) {
			if (!is_dir($path)) {
				continue;
			}
			foreach ((new DirectoryIterator($path)) as $fileInfo) {
				$fileName = $fileInfo->getBasename('.php');
				if (!\in_array($fileName, $existsFiles) && !$fileInfo->isDot() && 'php' === $fileInfo->getExtension()) {
					$existsFiles[] = $fileName;
					$fullPath = $path . DIRECTORY_SEPARATOR . $fileInfo->getFilename();
					if (file_exists($fullPath)) {
						require_once $fullPath;
						$block = new $fileName();
						if (isset($block->reference) && !\App\Module::isModuleActive($block->reference)) {
							continue;
						}
						$tempSummaryBlocks[$block->sequence] = [
							'name' => $block->name,
							'data' => $block->process($recordModel),
							'reference' => $block->reference,
							'type' => $block->type ?? false,
							'icon' => $block->icon ?? false,
						];
					}
				}
			}
		}
		ksort($tempSummaryBlocks);
		$blockCount = 0;
		$summaryBlocks = [];
		foreach ($tempSummaryBlocks as $key => $block) {
			$summaryBlocks[(int) ($blockCount / self::SUMMARY_ROW_COUNT)][$key] = $tempSummaryBlocks[$key];
			++$blockCount;
		}
		return $summaryBlocks;
	}

	public function getWidget()
	{
		$this->Config = parent::getWidget();
		$this->Config['tpl'] = 'SummaryCategory.tpl';
		return $this->Config;
	}

	public function getConfigTplName()
	{
		return 'SummaryCategoryConfig';
	}
}
