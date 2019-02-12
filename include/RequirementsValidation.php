<?php
/**
 * Requirements validation.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
if (version_compare(PHP_VERSION, '7.1', '<')) {
	throw new \App\Exceptions\AppException('Wrong PHP version, recommended version >= 7.1');
}
