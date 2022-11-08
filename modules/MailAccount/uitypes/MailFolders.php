<?php

/**
 * UIType Mail folders field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * UIType Mail folders field class.
 */
class MailAccount_MailFolders_UIType extends Vtiger_Base_UIType
{
	/** @var bool Search allowed */
	protected $search = false;

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || (!\is_array($value) && \App\Json::isEmpty($value))) {
			return;
		}

		if (!isset($this->validate[$value])) {
			$maximumLength = $this->getFieldModel()->getMaxValue();
			if ($maximumLength && \strlen($value) > $maximumLength) {
				throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
			$this->validate[$value] = true;
		}
	}

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if (\is_array($value)) {
			$value = \App\Json::encode($value);
		}

		return $value ? \App\Purifier::decodeHtml($value) : '';
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}

		$names = [];
		$trees = \App\Json::decode($value);
		foreach ($trees as $treeId) {
			$name = explode('.', $treeId);
			$names[] = end($name);
		}
		$value = implode(', ', $names);
		if (\is_int($length)) {
			$value = \App\TextUtils::textTruncate($value, $length);
		}

		return $rawText ? $value : \App\Purifier::encodeHtml($value);
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/MailFolders.tpl';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny'];
	}
}
