<?php
/**
 * The file contains: SaveInventory class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\BaseModel;

/**
 * Class SaveInventory.
 */
class SaveInventory extends AbstractSaveInventory
{
	/**
	 * {@inheritdoc}
	 */
	protected function getValue(string $columnName, string $inventoryKey)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function ignore(string $columnName): bool
	{
		return false;
	}
}
