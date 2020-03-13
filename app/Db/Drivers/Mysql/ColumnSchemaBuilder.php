<?php
/**
 * Column schema builder file is the schema builder for MySQL databases.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Db\Drivers\Mysql;

/**
 * Column schema builder class is the schema builder for MySQL databases.
 */
class ColumnSchemaBuilder extends \yii\db\mysql\ColumnSchemaBuilder
{
	use \App\Db\Drivers\ColumnSchemaBuilderTrait;

	/**
	 * Builds the autoincrement string for column.
	 *
	 * @return string
	 */
	protected function buildAutoIncrementString()
	{
		return $this->autoIncrement ? ' AUTO_INCREMENT' : '';
	}
}
