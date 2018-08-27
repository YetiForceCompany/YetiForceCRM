<?php

namespace App\SystemWarnings\SystemRequirements;

/**
 * Conf report system stability warnings class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class Configuration extends \App\SystemWarnings\Template
{
	protected $title = 'LBL_CONFIG_SERVER';
	protected $priority = 7;

	/**
	 * Checking whether all the configuration parameters are correct.
	 */
	public function process()
	{
		$this->status = 1;
		$errors = \App\Utils\ConfReport::getAllErrors() ?? [];
		if (!empty($errors)) {
			$this->status = 0;
		}
		if ($this->status === 0) {
			$reference = \App\Utils\ConfReport::getAll();
			$errorsText = '<br><pre>';
			if (!empty($errors['stability'])) {
				$errorsText .= '<strong>' . \App\Language::translate('LBL_STABILITY', 'Settings:SystemWarnings') . ':</strong>';
				foreach ($errors['stability'] as $key => $value) {
					$errorsText .= "\n  {$key} = " . \yii\helpers\VarDumper::dumpAsString($value) . ' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') . ': \'' . $reference['stability'][$key]['recommended'] . '\')';
				}
				$errorsText .= "\n\n";
			}
			if (!empty($errors['database'])) {
				$errorsText .= '<strong>' . \App\Language::translate('LBL_DATABASE', 'Settings:SystemWarnings') . ':</strong>';
				foreach ($errors['database'] as $key => $value) {
					$errorsText .= "\n  {$key} = " . \yii\helpers\VarDumper::dumpAsString($value) . ' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') . ': \'' . $reference['database'][$key]['recommended'] . '\')';
				}
				$errorsText .= "\n\n";
			}
			if (!empty($errors['libraries'])) {
				$noMandatoryLib = false;
				foreach ($errors['libraries'] as $key => $value) {
					if ($reference['libraries'][$key]['mandatory']) {
						if (!$noMandatoryLib) {
							$errorsText .= '<strong>' . \App\Language::translate('LBL_PHPEXT', 'Settings:SystemWarnings') . ':</strong>';
							$noMandatoryLib = true;
						}
						$errorsText .= "\n{$key} (" . ($reference['libraries'][$key]['mandatory'] ? \App\Language::translate('LBL_LIB_REQUIRED', 'Settings:SystemWarnings') : \App\Language::translate('LBL_LIB_OPTIONAL', 'Settings:SystemWarnings')) . ')';
					}
				}
				if ($noMandatoryLib) {
					$errorsText .= "\n\n";
				}
			}
			if (!empty($errors['performance'])) {
				$errorsText .= '<strong>' . \App\Language::translate('LBL_PERFORMANCE', 'Settings:SystemWarnings') . ':</strong>';
				foreach ($errors['performance'] as $key => $value) {
					$errorsText .= "\n  {$key} = " . \yii\helpers\VarDumper::dumpAsString($value) . ' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') . ': \'' . $reference['performance'][$key]['recommended'] . '\')';
				}
				$errorsText .= "\n\n";
			}
			$errorsText .= '</pre>';
			$this->link = 'index.php?parent=Settings&module=ConfReport&view=Index';
			$this->linkTitle = \App\Language::translate('LBL_CONFIG_REPORT_LINK', 'Settings:SystemWarnings');
			$this->description = \App\Language::translateArgs('LBL_CONFIG_SERVER_DESC', 'Settings:SystemWarnings', '<a target="_blank" rel="noreferrer" href="' . \App\Language::translate('LBL_CONFIG_DOC_URL', 'Settings:SystemWarnings') . '"><u>' . \App\Language::translate('LBL_CONFIG_DOC_URL_LABEL', 'Settings:SystemWarnings') . '</u></a>') . $errorsText;
		}
	}
}
