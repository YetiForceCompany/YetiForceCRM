<?php

/**
 * WooCommerce product tags synchronization file.
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

namespace App\Integrations\WooCommerce\Synchronizer;

/**
 * WooCommerce product tags synchronization class.
 */
class ProductTags extends Base
{
	/** @var int Records limit per page */
	const RECORDS_LIMIT_PER_PAGE = 100;
	/** @var array Cache for data from the API */
	private $cache;
	/** @var array ID by name cache from the API */
	private $cacheList;
	/** @var bool Flag whether to refresh the data in the cache after synchronization */
	private $refreshCache = false;
	/** @var \Settings_Picklist_Field_Model */
	private $fieldModel;

	/** {@inheritdoc} */
	public function process(): void
	{
		$this->fieldModel = \Settings_Picklist_Field_Model::getInstance(
			'tags',
			\Vtiger_Module_Model::getInstance('Products')
		);
		if ($this->fieldModel->isActiveField()) {
			$this->getAllFromApi();
			$direction = (int) $this->config->get('direction_tags');
			if ($this->config->get('master')) {
				if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_YF_TO_API === $direction) {
					$this->export();
				}
				if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_API_TO_YF === $direction) {
					$this->import();
				}
			} else {
				if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_API_TO_YF === $direction) {
					$this->import();
				}
				if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_YF_TO_API === $direction) {
					$this->export();
				}
			}
			if ($this->refreshCache) {
				$this->cache = null;
				$this->cacheList = null;
				$this->getAllFromApi(false);
			}
		}
	}

	/**
	 * Import tags from API.
	 *
	 * @return void
	 */
	public function import(): void
	{
		if ($this->config->get('logAll')) {
			$this->controller->log('Start import tags', []);
		}
		$picklistValues = \App\Fields\Picklist::getValues('tags');
		$keys = array_flip(array_map('mb_strtolower', array_column($picklistValues, 'tags', 'tagsid')));
		$i = 0;
		foreach ($this->cache as $tag) {
			$name = mb_strtolower($tag['name']);
			if (empty($keys[$name]) || !$this->config->get('master')) {
				try {
					$itemModel = $this->fieldModel->getItemModel(empty($keys[$name]) ? null : $keys[$name]);
					$save = false;
					foreach (['name' => 'name', 'description' => 'description', 'prefix' => 'slug'] as $property => $key) {
						if (isset($tag[$key]) && $tag[$key] != $itemModel->get($property)) {
							$itemModel->validateValue($property, $tag[$key]);
							$itemModel->set($property, $tag[$key]);
							$save = true;
						}
					}
					if ($save) {
						$itemModel->save();
						++$i;
					}
				} catch (\Throwable $th) {
					$this->controller->log('Import tag', $tag, $th);
					\App\Log::error('Error during import tag: ' . PHP_EOL . $th->__toString(), self::LOG_CATEGORY);
				}
			}
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('End import tags', ['imported' => $i]);
		}
	}

	/**
	 * Export tags to API.
	 *
	 * @return void
	 */
	public function export(): void
	{
		if ($this->config->get('logAll')) {
			$this->controller->log('Start export tags', []);
		}
		$tags = array_flip(array_map('mb_strtolower', array_column($this->cache, 'slug', 'id')));
		$i = 0;
		foreach (\App\Fields\Picklist::getValues('tags') as $value) {
			$prefix = mb_strtolower($value['prefix'] ?: $value['tags']);
			try {
				if (empty($tags[$prefix])) {
					$this->connector->request('POST', 'products/tags', [
						'name' => $value['tags'],
						'slug' => $value['prefix'],
						'description' => $value['description'],
					]);
					$this->refreshCache = true;
				} elseif ($this->config->get('master')) {
					$tag = $this->cache[$tags[$prefix]];
					$save = false;
					foreach (['tags' => 'name', 'description' => 'description', 'prefix' => 'slug'] as $property => $key) {
						if ($tag[$key] != $value[$property]) {
							$save = true;
						}
					}
					if ($save) {
						$this->connector->request(
							'PUT',
							'products/tags/' . $tag['id'],
							['name' => $value['tags'], 'slug' => $value['prefix'], 'description' => $value['description']]
						);
						$this->refreshCache = true;
						++$i;
					}
				}
			} catch (\Throwable $th) {
				$this->controller->log('Export tag', $value, $th);
				\App\Log::error('Error during export tag: ' . PHP_EOL . $th->__toString(), self::LOG_CATEGORY);
			}
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('End export tags', ['exported' => $i]);
		}
	}

	/**
	 * Get tags ids from API.
	 *
	 * @return array
	 */
	public function getTagsIds(): array
	{
		if (null === $this->cache) {
			$this->cacheList = array_column($this->getAllFromApi(), 'id', 'name');
		}
		return $this->cacheList;
	}

	/**
	 * Get all tags from API.
	 *
	 * @param bool $cache
	 *
	 * @return array
	 */
	private function getAllFromApi(bool $cache = true): array
	{
		if (null === $this->cache || !$cache) {
			$this->cache = [];
			try {
				$page = 1;
				$load = true;
				while ($load) {
					if ($rows = $this->getFromApi(
						'products/tags?&page=' . $page . '&per_page=' . self::RECORDS_LIMIT_PER_PAGE,
						$cache
					)
					) {
						foreach ($rows as $row) {
							$this->cache[$row['id']] = $row;
						}
						++$page;
					}
					if (self::RECORDS_LIMIT_PER_PAGE !== \count($rows)) {
						$load = false;
					}
				}
			} catch (\Throwable $ex) {
				$this->controller->log('Get tags', null, $ex);
				\App\Log::error('Error during get tags: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
			}
		}
		return $this->cache;
	}
}
