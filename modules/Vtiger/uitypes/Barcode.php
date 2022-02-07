<?php
/**
 * Barcode uitype file.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * UIType Barcode Field Class.
 */
class Vtiger_Barcode_UIType extends Vtiger_Base_UIType
{
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
	 * @var int
	 */
	public $height = 2;

	/**
	 * Width of a single bar element in pixels.
	 *
	 * @var int
	 */
	public $width = 30;

	/**
	 * Show value of barcode.
	 *
	 * @var bool
	 */
	public $showCode = true;

	/**
	 * Color of barcode.
	 *
	 * @var array
	 */
	public $color = [0, 0, 0];

	/**
	 * Default barcode display type.
	 *
	 * @var string
	 */
	public $barcodeDisplayType = 'text';

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ($value) {
			if ($rawText) {
				return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
			}
			$this->params = $params = $this->getFieldModel()->getFieldParams();
			switch ($params['barcodeDisplayType'] ?? $this->barcodeDisplayType) {
				case 'barcode':
					$this->showCode = false;
					$barcode = $this->createBarcode($value);
					$value = $this->wrapInImageContainer($barcode, $value);
					break;
				case 'barcodeAndValue':
					$this->showCode = true;
					$barcode = $this->createBarcode($value);
					$value = $this->wrapInImageContainer($barcode, $value);
					break;
				case 'text':
				default:
					$value = parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
					break;
			}
		}
		return $value;
	}

	/**
	 * Function return a PNG image representation of barcode.
	 *
	 * @param string $valueToEncode
	 *
	 * @return string
	 */
	public function createBarcode(string $valueToEncode): string
	{
		$qrCodeGenerator = $this->getBarcodeClass();
		$qrCodeGenerator->setStorPath(__DIR__ . App\Config::main('tmp_dir'));
		$barcodeHeight = $this->params['height'] ?? $this->height;
		$barcodeWidth = $this->params['width'] ?? $this->width;
		$barcodeType = $this->params['barcodeType'] ?? $this->defaultBarcodeType;
		return $qrCodeGenerator->getBarcodePNG($valueToEncode, $barcodeType, $barcodeHeight, $barcodeWidth, $this->color, $this->showCode);
	}

	/**
	 *  Function get class for a specific barcode type.
	 *
	 * @return object
	 */
	public function getBarcodeClass(): object
	{
		$barcodeClass = $this->params['barcodeClass'] ?? $this->defaultBarcodeClass;
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
	 * @param string $value
	 *
	 * @return string
	 */
	public function wrapInImageContainer(string $barcode, string $value): string
	{
		return '<img src="data:image/png;base64,' . $barcode . '" alt="' . $value . '"  title="' . $value . '"/>';
	}
}
