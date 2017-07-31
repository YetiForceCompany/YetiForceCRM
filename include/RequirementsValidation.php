<?php
/**
 * Requirements validation
 * @package YetiForce.Include
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
	throw new \Exception\AppException('Wrong PHP version, recommended version >= 5.4.0');
}
