<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class CustomerPortalPassword
{

	public static function encryptPassword($userPassword, $userEmail)
	{

		$salt = substr($userEmail, 0, 2);

		$crypt_type = static::getCryptType();

		if ($crypt_type == 'MD5') {
			$salt = '$1$' . $salt . '$';
		} elseif ($crypt_type == 'BLOWFISH') {
			$salt = '$2$' . $salt . '$';
		} elseif ($crypt_type == 'PHP5.3MD5') {
			$salt = '$1$' . str_pad($salt, 9, '0');
		}

		$encryptedPassword = crypt($userPassword, $salt);
		return $encryptedPassword;
	}

	public static function getCryptType()
	{
		return (version_compare(PHP_VERSION, '5.3.0') >= 0) ? 'PHP5.3MD5' : 'MD5';
	}
}
