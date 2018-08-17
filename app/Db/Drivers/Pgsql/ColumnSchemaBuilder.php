<?php

namespace App\Db\Drivers\Pgsql;

/**
 * ColumnSchemaBuilder is the schema builder for PgSQL databases.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class ColumnSchemaBuilder extends \yii\db\ColumnSchemaBuilder
{
	use \App\Db\Drivers\ColumnSchemaBuilderTrait;
}
