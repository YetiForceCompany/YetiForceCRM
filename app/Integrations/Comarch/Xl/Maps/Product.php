<?php

/**
 * Comarch product synchronization map file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription.
 * File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Comarch\Xl\Maps;

/**
 * Comarch product synchronization map class.
 */
class Product extends \App\Integrations\Comarch\Map
{
	/** {@inheritdoc} */
	const API_NAME_ID = 'twr_GIDNumer';
	/** {@inheritdoc} */
	protected $moduleName = 'Products';
	/** {@inheritdoc} */
	protected $fieldMap = [
		'productname' => ['names' => ['get' => 'twr_Nazwa', 'create' => 'Nazwa', 'update' => 'Nazwa']],
		'serial_no' => ['names' => ['get' => 'twr_Kod', 'create' => 'Kod', 'update' => 'Kod']],
		'ean' => ['names' => ['get' => 'twr_Ean', 'create' => 'Ean', 'update' => 'Ean']],
		'pscategory' => [
			'names' => ['get' => 'twr_KategoriaId'],
			'fn' => 'findBySynchronizer', 'synchronizer' => 'ProductGroup'
		],
		'usageunit' => [
			'names' => ['get' => 'twr_Jm'],
			'fn' => 'findBySynchronizer', 'synchronizer' => 'ProductUnit'
		],
	];
	/** {@inheritdoc} */
	protected $defaultDataYf = [
		'fieldMap' => [
			'discontinued' => 1
		]
	];

	/** {@inheritdoc} */
	public function saveInApi(): void
	{
		if (empty($this->dataApi[self::API_NAME_ID])) {
			$response = $this->synchronizer->controller->getConnector()
				->request('POST', 'Product/Create', $this->dataApi);
			$response = \App\Json::decode($response);
			$this->recordModel->set(self::FIELD_NAME_ID, $response['id']);
			$this->recordModel->save();
			$this->dataYf[self::FIELD_NAME_ID] = $this->dataApi[self::API_NAME_ID] = $response['id'];
		} else {
			$id = $this->dataApi[self::API_NAME_ID];
			unset($this->dataApi[self::API_NAME_ID]);
			$this->synchronizer->controller->getConnector()
				->request('PUT', 'Product/Update/' . $id, $this->dataApi);
			$this->dataApi[self::API_NAME_ID] = $id;
		}
		$this->synchronizer->updateMapIdCache(
			$this->recordModel->getModuleName(),
			$this->dataApi[self::API_NAME_ID],
			$this->recordModel->getId()
		);
	}

	/** {@inheritdoc} */
	public function findRecordInYf(): ?int
	{
		$yfId = $this->synchronizer->getYfId($this->dataApi[self::API_NAME_ID], $this->moduleName);
		if (!$yfId && (!empty($this->dataApi['twr_Ean']) || !empty($this->dataApi['twr_Kod']))) {
			$queryGenerator = new \App\QueryGenerator($this->moduleName);
			$queryGenerator->setStateCondition('All');
			$queryGenerator->setFields(['id'])->permissions = false;
			if (!empty($this->dataApi['twr_Ean'])) {
				$queryGenerator->addCondition('ean', $this->dataApi['twr_Ean'], 'e');
			} else {
				$queryGenerator->addCondition('serial_no', $this->dataApi['twr_Kod'], 'e');
			}
			$yfId = $queryGenerator->createQuery()->scalar() ?: null;
			if (null !== $yfId) {
				$this->synchronizer->updateMapIdCache($this->moduleName, $this->dataApi[self::API_NAME_ID], $yfId);
			}
		}
		return $yfId;
	}
}
