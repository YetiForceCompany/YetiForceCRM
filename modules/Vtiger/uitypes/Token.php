<?php

/**
 * UIType Token Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Vtiger_Token_UIType class.
 */
class Vtiger_Token_UIType extends Vtiger_Base_UIType
{
	/**
	 * Maximum token length.
	 */
	public const MAX_LENGTH = 64;

	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_TO_FIELD', 406);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (!empty($value) && !isset($this->validate[$value]) && !preg_match('/^[A-Fa-f0-9]{64}$/', $value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		return empty($value) ? '' : substr($value, 0, 2) . '****' . substr($value, -2);
	}

	/** {@inheritdoc} */
	public function getTextParserDisplayValue($value, Vtiger_Record_Model $recordModel, $params)
	{
		return $value;
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['string'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['y', 'ny'];
	}

	/** {@inheritdoc} */
	public function isActiveSearchView()
	{
		return false;
	}

	/**
	 * Gets unique token.
	 *
	 * @param bool $repeated
	 *
	 * @return string
	 */
	public function generateToken(bool $repeated = false)
	{
		$fieldModel = $this->getFieldModel();
		$token = \App\Fields\Token::generateToken();
		$queryGenerator = (new \App\QueryGenerator($fieldModel->getModuleName()));
		$queryGenerator->permissions = false;
		$queryGenerator->setStateCondition('All');
		$queryGenerator->addCondition($fieldModel->getName(), $token, 'e');
		$isExists = $queryGenerator->createQuery()->exists();
		if ($isExists && $repeated) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		return $isExists ? $this->generateToken(true) : $token;
	}
}
