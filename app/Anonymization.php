<?php
/**
 * Anonymization file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Anonymization class.
 */
class Anonymization
{
	/** @var int Anonymization logs */
	public const LOGS = 0;
	/** @var int Anonymization of change history entries on the displayed data layer. */
	public const MODTRACKER_DISPLAY = 1;
	/** @var int Anonymization of change history entries on the database layer. */
	public const MODTRACKER_DB = 2;

	/**
	 * Gets Anonymization types.
	 *
	 * @return array
	 */
	public static function getTypes(): array
	{
		return [
			self::LOGS => 'LBL_ANONYMIZATION_LOGS',
			self::MODTRACKER_DISPLAY => 'LBL_ANONYMIZATION_MODTRACKER_DISPLAY',
			self::MODTRACKER_DB => 'LBL_ANONYMIZATION_MODTRACKER_DB',
		];
	}

	/**
	 * @var array Word map for anonymization.
	 */
	const MAPS = [
		'password' => ['pass', 'password', 'oldPassword', 'retype_password', 'db_password'],
	];
	/**
	 * @var string Map name
	 */
	protected $map;
	/**
	 * @var string Module name
	 */
	protected $moduleName;
	/**
	 * @var bool Detect module name if not there
	 */
	public $detectModuleName = true;
	/**
	 * @var bool Value for anonymised data
	 */
	public $value = '****';
	/**
	 * @var array Data array
	 */
	protected $data;
	/**
	 * @var string[] Keys to ananimation
	 */
	protected $fields;

	/**
	 * Anonymization constructor.
	 *
	 * @param string $map
	 */
	public function __construct(string $map = 'all')
	{
		$this->map = $map;
	}

	/**
	 * Set module name.
	 *
	 * @param string $moduleName
	 *
	 * @return self
	 */
	public function setModuleName(string $moduleName): self
	{
		$this->moduleName = $moduleName;
		return $this;
	}

	/**
	 * Set data.
	 *
	 * @param array $data
	 *
	 * @return self
	 */
	public function setData(array $data): self
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * Get data.
	 *
	 * @return self
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * Data anonymization.
	 *
	 * @return void
	 */
	public function anonymize(): self
	{
		if (empty($this->data)) {
			return $this;
		}
		$mapFields = 'all' === $this->map ? self::MAPS : (isset(self::MAPS[$this->map]) ? [$this->map => self::MAPS[$this->map]] : []);
		if ($mapFields) {
			foreach ($mapFields as $fields) {
				$this->fields = $fields;
				$this->data = $this->anonymizeByFields($this->data);
			}
		}
		if (('all' === $this->map || 'fields' === $this->map) && (!empty($this->moduleName) || $this->detectModuleName)) {
			if (empty($this->moduleName)) {
				$this->detectModuleName();
			}
			if (!empty($this->moduleName) && ($fields = self::getFields(Module::getModuleId($this->moduleName)))) {
				$this->fields = $fields;
				$this->data = $this->anonymizeByFields($this->data);
			}
		}
		return $this;
	}

	/**
	 * Detect module name.
	 *
	 * @return void
	 */
	private function detectModuleName(): void
	{
		if (!empty($this->data['module'])) {
			$this->moduleName = $this->data['module'];
		}
	}

	/**
	 * Anonymize by fields.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function anonymizeByFields(array $data): array
	{
		foreach ($data as $key => &$value) {
			if (\in_array($key, $this->fields, true)) {
				$value = $this->value;
			} elseif (\is_array($value)) {
				$value = $this->anonymizeByFields($value);
			}
		}
		return $data;
	}

	/**
	 * Get list of fields for anonymized.
	 *
	 * @param int $moduleId Module id
	 *
	 * @return string[]
	 */
	public static function getFields(int $moduleId): array
	{
		if (Cache::has('getFieldsFromRelation', $moduleId)) {
			$fields = Cache::get('getFieldsFromRelation', $moduleId);
		} else {
			$fields = (new \App\Db\Query())->select(['vtiger_field.fieldname'])->from('s_#__fields_anonymization')
				->innerJoin('vtiger_field', 'vtiger_field.fieldid = s_#__fields_anonymization.field_id')
				->where(['tabid' => $moduleId])
				->column();
			Cache::save('getFieldsFromRelation', $moduleId, $fields, Cache::LONG);
		}
		return $fields;
	}
}
