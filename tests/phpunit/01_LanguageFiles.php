<?php
/**
 * Language Files test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class LanguageFiles extends TestCase
{

	public function test()
	{
		$templatepath = 'languages/';
		$flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($templatepath, $flags), RecursiveIteratorIterator::SELF_FIRST);
		foreach ($objects as $name => $object) {
			if (!is_dir($name)) {
				include_once $name;
			}
		}
	}
}
