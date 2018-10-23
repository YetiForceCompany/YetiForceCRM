<?php
/**
 * Multi image class to handle files.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

/**
 * Image class to handle files.
 */
class Users_MultiImage_File extends Vtiger_MultiImage_File
{
	/**
	 * Checking permission in get method.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	public function getCheckPermission(\App\Request $request)
	{
		$fieldName = $request->getByType('field', 2);
		if (!$request->has('record') || ($fieldName !== 'imagename' && (!\App\Privilege::isPermitted('Users', 'DetailView', $request->getInteger('record')) || !\App\Field::getFieldPermission('Users', $fieldName)))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		return true;
	}
}
