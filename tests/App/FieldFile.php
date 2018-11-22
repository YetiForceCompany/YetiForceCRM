<?php
/**
 * FieldModel test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\App;

/**
 * Class FieldModel for test.
 */
class FieldFile extends \Tests\Base
{
	/**
	 * @throws \Exception
	 *
	 * @dataProvider providerData
	 */
	public function testValidateImageFile(string $fileName, bool $isGood)
	{
		$filePathSrc = 'tests' . \DIRECTORY_SEPARATOR . 'data' . \DIRECTORY_SEPARATOR . 'MultiImage' .
			\DIRECTORY_SEPARATOR . $fileName;
		$this->assertTrue(\file_exists($filePathSrc), 'file does not exist');
		$file = \App\Fields\File::loadFromPath($filePathSrc);
		$this->assertSame($isGood, $file->validate('image'), "Problem with image validation: {$fileName}. Message: {$file->validateError}");
	}

	/**
	 * Data provider for the test of wrong data. For the "validate" method test.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerData()
	{
		return [
			['validate_image_0.jpg', false],
			['validate_image_1.jpg', false],
			['validate_image_2.jpg.php', false],
			['validate_image_3.jpg.php', false],
			['0.jpg', true],
			['img1.jpg', true],
			['img2.jpg', false],
			['1.png', true],
		];
	}
}
