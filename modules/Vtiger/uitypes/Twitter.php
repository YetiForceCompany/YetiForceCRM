<?php

/**
 * UIType Twitter field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * UIType Twitter field class.
 */
class Vtiger_Twitter_UIType extends Vtiger_Base_UIType
{
	/**
	 * Maximum length of Twitter account name.
	 */
	public const MAX_LENGTH = 15;

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (isset($this->validate[$value]) || empty($value)) {
			return;
		}
		if (!preg_match('/^[a-zA-Z0-9_]{1,' . static::MAX_LENGTH . '}$/', $value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		parent::validate($value, $isUserFormat);
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		$twitter = parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
		if ($rawText) {
			return $twitter;
		}
		return "<a href=\"https://twitter.com/{$twitter}\" target='_blank' rel=\"noreferrer noopener\" >@{$twitter}</a>";
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Twitter.tpl';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'c', 'k', 'y', 'ny', 'ef', 'nf'];
	}
}
