<?php

namespace App\Db\Drivers\Pgsql;

/**
 * Command represents a SQL statement to be executed against a database.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Schema extends \yii\db\pgsql\Schema
{
	use \App\Db\Drivers\SchemaTrait;

	/**
	 * Create a column schema builder instance giving the type and value precision.
	 *
	 * This method may be overridden by child classes to create a DBMS-specific column schema builder.
	 *
	 * @param string           $type   type of the column. See [[ColumnSchemaBuilder::$type]].
	 * @param int|string|array $length length or precision of the column. See [[ColumnSchemaBuilder::$length]].
	 *
	 * @return ColumnSchemaBuilder column schema builder instance
	 *
	 * @since 2.0.6
	 */
	public function createColumnSchemaBuilder($type, $length = null)
	{
		return new ColumnSchemaBuilder($type, $length, $this->db);
	}
}
