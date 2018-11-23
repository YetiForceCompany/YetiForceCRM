<?php
/**
 * Test of protection against code injection.
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
	 * Testing protection against code injection in metadata.
	 *
	 * @dataProvider providerDataWrongMetadata
	 */
	public function testCodeInjectionInMetadata(string $fileImgIn)
	{
		$this->expectException(\App\Exceptions\AppException::class);
		$filePathSrc = 'tests' . \DIRECTORY_SEPARATOR . 'data' . \DIRECTORY_SEPARATOR . 'MultiImage' .
			\DIRECTORY_SEPARATOR . $fileImgIn;
		$this->assertFileExists($filePathSrc);
		$fileSrc = \App\Fields\File::loadFromPath($filePathSrc);
		$fileSrc->validateCodeInjectionInMetadata();
	}

	/**
	 * @throws \Exception
	 *
	 * @dataProvider providerData
	 */
	public function testValidateImageFile(string $fileName, bool $isGood, $message)
	{
		$filePathSrc = 'tests' . \DIRECTORY_SEPARATOR . 'data' . \DIRECTORY_SEPARATOR . 'MultiImage' .
			\DIRECTORY_SEPARATOR . $fileName;
		$this->assertFileExists($filePathSrc);
		$file = \App\Fields\File::loadFromPath($filePathSrc);
		$this->assertSame(
			$isGood,
			$file->validate('image'),
			"Problem with image validation: {$fileName}. Message: {$message} - " . ($isGood ? $file->validateError : 'A dangerous file was passed')
		);
	}

	/**
	 * Testing the protection against code injection.
	 *
	 * @param string $fileImgIn
	 *
	 * @dataProvider providerDataForRemoveForbiddenTags
	 */
	public function testCodeInjection(string $fileImgIn)
	{
		$this->expectException(\App\Exceptions\AppException::class);
		$filePathSrc = 'tests' . \DIRECTORY_SEPARATOR . 'data' . \DIRECTORY_SEPARATOR . 'MultiImage' .
			\DIRECTORY_SEPARATOR . $fileImgIn;
		$this->assertFileExists($filePathSrc);
		$fileSrc = \App\Fields\File::loadFromPath($filePathSrc);
		$fileSrc->validateCodeInjection();
	}

	/**
	 * Testing the removal of forbidden tags.
	 *
	 * @param string $fileImgIn
	 *
	 * @dataProvider providerDataForRemoveForbiddenTags
	 */
	public function testRemoveForbiddenTags(string $fileImgIn)
	{
		$filePathSrc = 'tests' . \DIRECTORY_SEPARATOR . 'data' . \DIRECTORY_SEPARATOR . 'MultiImage' .
			\DIRECTORY_SEPARATOR . $fileImgIn;
		$this->assertFileExists($filePathSrc);
		$fileImgOut = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'images' .
			\DIRECTORY_SEPARATOR . $fileImgIn;
		\App\Fields\File::removeForbiddenTags($filePathSrc, $fileImgOut);
		$this->assertFileExists($fileImgOut);
		$file = \App\Fields\File::loadFromPath($fileImgOut);
		$file->validateCodeInjection();
		$file->validateCodeInjectionInMetadata();
	}

	/**
	 * Data provider.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerData()
	{
		return [
			['validate_image_0.jpg', false, 'PHP file with changed extension'],
			['validate_image_1.jpg', false, 'Empty file'],
			['validate_image_2.jpg.php', false, 'PHP file with changed extension'],
			['validate_image_3.jpg.php', false, 'A good image file with PHP extensions'],
			['validate_image_4.jpg', true, 'A good image file with the tag "<?"'],
			['validate_image_5_exif.jpg', false, 'A file with PHP code in the metadata'],
		];
	}

	/**
	 * Data provider - forbidden tags.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerDataForRemoveForbiddenTags()
	{
		return [
			['validate_image_6.jpg'],
			['validate_image_5_exif.jpg']
		];
	}

	/**
	 * Data provider - bad metadata.
	 *
	 * @return []
	 * @codeCoverageIgnore
	 */
	public function providerDataWrongMetadata()
	{
		return [
			['validate_image_5_exif.jpg']
		];
	}
}
