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
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'languages', \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isFile()) {
				if (isset($languageStrings)) {
					unset($languageStrings);
				}
				if (isset($jsLanguageStrings)) {
					unset($jsLanguageStrings);
				}
				include $item->getPathname();
				$this->assertTrue(is_array($languageStrings) || is_array($jsLanguageStrings), 'File: ' . $item->getPathname() . ' | $languageStrings: ' . print_r(is_array($languageStrings), true) . ' | $jsLanguageStrings: ' . print_r(is_array($jsLanguageStrings), true));
			}
		}
	}
}
