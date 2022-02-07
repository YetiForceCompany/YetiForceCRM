<?php
/**
 * Command file represents a SQL statement to be executed against a database.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Db\Drivers\Mysql;

/**
 * Command class represents a SQL statement to be executed against a database.
 */
class Schema extends \yii\db\mysql\Schema
{
	use \App\Db\Drivers\SchemaTrait;

	/**
	 * Create a column schema builder instance giving the type and value precision.
	 *
	 * This method may be overridden by child classes to create a DBMS-specific column schema builder.
	 *
	 * @param string           $type   type of the column. See [[ColumnSchemaBuilder::$type]].
	 * @param array|int|string $length length or precision of the column. See [[ColumnSchemaBuilder::$length]].
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
