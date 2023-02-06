<?php
/**
 * UIType WooCommerce server field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * UIType WooCommerce server field class.
 */
class Vtiger_WoocommerceServer_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (empty(\App\Integrations\WooCommerce\Config::getServer($value))) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ($value) {
			return parent::getDisplayValue(
						\App\Integrations\WooCommerce\Config::getServer($value)['name'] ?? '',
						$record,
						$recordModel,
						$rawText,
						$length
					);
		}
		return '';
	}

	/** {@inheritdoc} */
	public function getPicklistValues()
	{
		return App\Integrations\WooCommerce\Config::getAllServers();
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/SimplePicklist.tpl';
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/SimplePicklist.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['integer'];
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/SimplePicklist.tpl';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny', 'ef', 'nf'];
	}
}
