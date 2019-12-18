<?php
/**
 * User variable.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\TextParser;

/**
 * UserVariable class.
 */
class UserVariable extends Base
{
	/** @var string */
	public $name = 'LBL_TEXT_PARSER_USER_VARIABLE';

	/** @var string Parser type */
	public $type = 'pdf';

	/** @var string Default template */
	public $default = '$(userVariable : name=__FIELD_NAME__|label=__LABEL__|default=__DEFAULT_VALUE__)$';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$data = $this->textParser->getUserVariables($this->params, false);
		$key = (string) key($data);
		return $this->textParser->getParam($key) ?? ($data[$key]['default'] ?? '');
	}
}
