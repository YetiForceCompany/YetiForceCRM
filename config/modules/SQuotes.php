<?php
/**
 * SQuotes module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
    // List of fields read from related module
    'INVENTORY_ON_SELECT_AUTO_COMPLETE' => [
        'description' => [
            'ref' => 'getInventoryListName',
        ],
        'price' => [
            'ref' => 'getInventoryPrice',
        ],
    ],
];
