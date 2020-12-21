<?php

namespace App\TextParser;

/**
 * Display bar code class.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon  <a.kon@yetiforce.com>
 */
class Barcode extends Base
{
	/** @var string */
	public $name = 'LBL_DISPLAY_BARCODE';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Default barcode type.
	 *
	 * @var string
	 */
	public $defaultBarcodeType = 'EAN13';

	/**
	 * Default barcode class.
	 *
	 * @var string
	 */
	public $defaultBarcodeClass = 'DNS1D';

	/**
	 * Height of a single bar element in pixels.
	 *
	 * @var string
	 */
	public $height = '2';

	/**
	 * Width of a single bar element in pixels.
	 *
	 * @var string
	 */
	public $width = '30';

	/**
	 * Show value of barcode.
	 *
	 * @var bool
	 */
	public $showText = true;

	/**
	 * Color of barcode.
	 *
	 * @var array
	 */
	public $color = [0, 0, 0];

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process(): string
	{
		$barcode = '';
		$valueForEncode = $this->getValueForBarcode();
		if ($valueForEncode) {
			$barcode = $this->createBarcode($valueForEncode);
			$barcode = $this->wrapInImageContainer($barcode);
		}
		return $barcode;
	}

	/**
	 * Return encode value for barcode.
	 *
	 * @return string
	 */
	public function getValueForBarcode(): string
	{
		$value = '';
		if (isset($this->params['value'])) {
			$value = $this->params['value'];
		}
		if (isset($this->params['fieldName'])) {
			$value = $this->textParser->recordModel->get($this->params['fieldName']);
		}
		return $value;
	}

	/**
	 * Function return a PNG image representation of barcode.
	 *
	 * @param string $valueToEncode
	 */
	public function createBarcode(string $valueToEncode): string
	{
		$qrCodeGenerator = $this->getBarcodeClass();
		$qrCodeGenerator->setStorPath(__DIR__ . \App\Config::main('tmp_dir'));
		$barcodeHeight = $this->params['height'] ?? $this->height;
		$barcodeWidth = $this->params['width'] ?? $this->width;
		$barcodeType = $this->params['type'] ?? $this->defaultBarcodeType;
		$showText = $this->params['showText'] ?? $this->showText;
		return $qrCodeGenerator->getBarcodePNG($valueToEncode, $barcodeType, $barcodeHeight, $barcodeWidth, $this->color, $showText);
	}

	/**
	 * Function get class for a specific barcode type.
	 *
	 * @return object
	 */
	public function getBarcodeClass(): object
	{
		$barcodeClass = $this->params['class'] ?? $this->defaultBarcodeClass;
		$className = '\Milon\Barcode\\' . $barcodeClass;
		if (!class_exists($className)) {
			throw new \App\Exceptions\AppException('ERR_CLASS_NOT_FOUND||' . $className);
		}
		return new $className();
	}

	/**
	 * Function return barcode in image.
	 *
	 * @param string $barcode
	 *
	 * @return string
	 */
	public function wrapInImageContainer(string $barcode): string
	{
		return '<img src="data:image/png;base64,' . $barcode . '"/>';
	}
}
