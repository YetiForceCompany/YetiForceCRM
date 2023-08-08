<?php
/**
 * UIType record log  field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * UIType record log field class.
 */
class Vtiger_RecordLog_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		return empty($value) ? null : $value;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (!$value) {
			return '';
		}
		if (\is_string($value)) {
			$value = \App\Json::decode($value);
		}
		$text = '';
		$moduleModel = $this->getFieldModel()->getModule();
		$labels = $this->getLabels();
		foreach ($value as $id => $errors) {
			if (isset($labels[$id])) {
				$text .= '[' . $labels[$id] . "]\n";
			}
			foreach ($errors as $fieldName => $error) {
				$fieldLabel = $fieldName;
				if ($fieldModel = $moduleModel->getFieldByName($fieldName)) {
					$fieldLabel = $fieldModel->getFullLabelTranslation();
				}
				$text .= \App\Language::translate('LBL_FIELD_NAME') . ': ' . $fieldLabel . PHP_EOL;
				$text .= \App\Language::translate('LBL_VALUE_OF_FIELDS') . ': ';
				$text .= \App\Purifier::encodeHtml($error['field']) . PHP_EOL;
				$text .= \App\Language::translate('LBL_ERROR_MASAGE') . ': ';
				$text .= \App\Language::translate($error['message'], 'Other.Exceptions') . PHP_EOL . PHP_EOL;
			}
		}
		$text = trim($text);
		if (!$rawText) {
			if (1000 == $length) {
				$text = nl2br($text);
			} else {
				$text = \App\Layout::truncateText($text, $length ?: 200, true, true);
			}
		}
		return $text;
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['c', 'k', 'y', 'ny'];
	}

	/**
	 * Get labels for alert.
	 *
	 * @return array
	 */
	private function getLabels(): array
	{
		$params = $this->getFieldModel()->getFieldParams();
		$rows = [];
		if (!empty($params['type'])) {
			switch ($params['type']) {
				case 'ComarchIntegration':
					$rows = array_column(App\Integrations\Comarch\Config::getAllServers(), 'name', 'id');
					break;
				default:
					// code...
					break;
			}
		}
		return $rows;
	}
}
