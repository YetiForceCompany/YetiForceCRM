<?php
namespace App\QueryField;

/**
 * Phone Query Field Class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class PhoneField extends StringField
{

	/**
	 * {@inheritDoc}
	 */
	public function getListViewFields()
	{
		if ($fieldModel = $this->queryGenerator->getModuleField($this->fieldModel->getName() . '_extra')) {
			return $fieldModel;
		}
		return false;
	}
}
