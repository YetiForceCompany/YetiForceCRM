<?php

namespace App\SystemWarnings\SystemRequirements;

/**
 * Conf report system database warnings class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class Database extends \App\SystemWarnings\Template
{
	protected $title = 'LBL_CONFIG_DATABASE';
	protected $priority = 7;

	/**
	 * Checking whether all the configuration parameters are correct.
	 */
	public function process()
	{
		$this->status = 1;
		$errors = \App\Utils\ConfReport::getAllErrors()['database'] ?? [];
		if (!empty($errors)) {
			$this->status = 0;
		}
		if ($this->status === 0) {
			$reference = \App\Utils\ConfReport::get('database');
			$errorsText = '<br><pre>';
			foreach ($errors as $key => $value) {
				$errorsText .= "\n{$key} = " . \yii\helpers\VarDumper::dumpAsString($value) . ' (' . \App\Language::translate('LBL_RECOMMENDED_VALUE', 'Settings:SystemWarnings') . ': ' . $reference[$key]['recommended'] . ')';
			}
			$errorsText .= '</pre>';
			$this->link = 'index.php?parent=Settings&module=ConfReport&view=Index';
			$this->linkTitle = \App\Language::translate('LBL_CONFIG_REPORT_LINK', 'Settings:SystemWarnings');
			$this->description = \App\Language::translateArgs('LBL_CONFIG_PERFORMANCE_DESC', 'Settings:SystemWarnings', '<a target="_blank" rel="noreferrer" href="' . \App\Language::translate('LBL_CONFIG_DOC_URL', 'Settings:SystemWarnings') . '"><u>' . \App\Language::translate('LBL_CONFIG_DOC_URL_LABEL', 'Settings:SystemWarnings') . '</u></a>') . $errorsText;
		}
	}
}
