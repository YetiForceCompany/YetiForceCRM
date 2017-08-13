<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace yii\db\mysql;

use yii\db\ColumnSchemaBuilder as AbstractColumnSchemaBuilder;

/**
 * ColumnSchemaBuilder is the schema builder for MySQL databases.
 *
 * @author Chris Harris <chris@buckshotsoftware.com>
 * @since 2.0.8
 */
class ColumnSchemaBuilder extends AbstractColumnSchemaBuilder
{

	/**
	 * @inheritdoc
	 */
	public $categoryMap = [
		Schema::TYPE_PK => self::CATEGORY_PK,
		Schema::TYPE_UPK => self::CATEGORY_PK,
		Schema::TYPE_BIGPK => self::CATEGORY_PK,
		Schema::TYPE_UBIGPK => self::CATEGORY_PK,
		Schema::TYPE_CHAR => self::CATEGORY_STRING,
		Schema::TYPE_STRING => self::CATEGORY_STRING,
		Schema::TYPE_TEXT => self::CATEGORY_STRING,
		Schema::TYPE_SMALLINT => self::CATEGORY_NUMERIC,
		Schema::TYPE_INTEGER => self::CATEGORY_NUMERIC,
		Schema::TYPE_BIGINT => self::CATEGORY_NUMERIC,
		Schema::TYPE_FLOAT => self::CATEGORY_NUMERIC,
		Schema::TYPE_DOUBLE => self::CATEGORY_NUMERIC,
		Schema::TYPE_DECIMAL => self::CATEGORY_NUMERIC,
		Schema::TYPE_DATETIME => self::CATEGORY_TIME,
		Schema::TYPE_TIMESTAMP => self::CATEGORY_TIME,
		Schema::TYPE_TIME => self::CATEGORY_TIME,
		Schema::TYPE_DATE => self::CATEGORY_TIME,
		Schema::TYPE_BINARY => self::CATEGORY_OTHER,
		Schema::TYPE_BOOLEAN => self::CATEGORY_NUMERIC,
		Schema::TYPE_MONEY => self::CATEGORY_NUMERIC,
		'tinyint' => self::CATEGORY_NUMERIC,
	];

	/**
	 * @inheritdoc
	 */
	protected function buildUnsignedString()
	{
		return $this->isUnsigned ? ' UNSIGNED' : '';
	}

	/**
	 * @inheritdoc
	 */
	protected function buildAfterString()
	{
		return $this->after !== null ?
			' AFTER ' . $this->db->quoteColumnName($this->after) :
			'';
	}

	/**
	 * @inheritdoc
	 */
	protected function buildFirstString()
	{
		return $this->isFirst ? ' FIRST' : '';
	}

	/**
	 * @inheritdoc
	 */
	protected function buildCommentString()
	{
		return $this->comment !== null ? " COMMENT " . $this->db->quoteValue($this->comment) : '';
	}

	protected function buildAutoIncrementString()
	{
		return $this->autoIncrement ? ' AUTO_INCREMENT' : '';
	}

	/**
	 * @inheritdoc
	 */
	public function __toString()
	{
		switch ($this->getTypeCategory()) {
			case self::CATEGORY_PK:
				$format = '{type}{length}{check}{comment}{pos}{append}';
				break;
			case self::CATEGORY_NUMERIC:
				$format = '{type}{length}{unsigned}{notnull}{unique}{default}{check}{autoIncrement}{comment}{pos}{append}';
				break;
			default:
				$format = '{type}{length}{notnull}{unique}{default}{check}{comment}{pos}{append}';
		}
		return $this->buildCompleteString($format);
	}
}
