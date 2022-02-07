<?php
/**
 * Model of KnowledgeBase.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Class KnowledgeBase model for module faq.
 */
class Faq_KnowledgeBase_Model extends KnowledgeBase_KnowledgeBase_Model
{
	/**
	 * Get featured records.
	 *
	 * @param string[] $categories
	 *
	 * @return array
	 */
	public function getFeaturedRecords(array $categories): array
	{
		$queryGenerator = new App\QueryGenerator('Faq');
		$queryGenerator->setFields(['id', 'category', 'subject']);
		$queryGenerator->addNativeCondition(['vtiger_faq.status' => 'Published']);
		$queryGenerator->addNativeCondition(['category' => $categories]);
		$queryGenerator->addNativeCondition(['featured' => 1]);
		if ($this->has('filterField') && $this->has('filterValue')) {
			$queryGenerator->addNativeCondition([$this->get('filterField') => $this->get('filterValue')]);
		}
		$queryGenerator->setLimit(50);
		return $queryGenerator->createQuery()->all();
	}

	/**
	 * Get record list query.
	 *
	 * @return App\Db\Query
	 */
	public function getListQuery(): App\Db\Query
	{
		$queryGenerator = new App\QueryGenerator('Faq');
		$queryGenerator->setFields(['id', 'assigned_user_id', 'subject', 'introduction', 'modifiedtime', 'category']);
		$queryGenerator->addNativeCondition(['vtiger_faq.status' => 'Published']);
		if ($this->has('parentCategory')) {
			$queryGenerator->addNativeCondition(['category' => $this->get('parentCategory')]);
		}
		if ($this->has('filterField') && $this->has('filterValue')) {
			$queryGenerator->addNativeCondition([$this->get('filterField') => $this->get('filterValue')]);
		}
		$queryGenerator->setLimit(Config\Modules\Faq::$knowledgeBaseArticleLimit);
		return $queryGenerator->createQuery();
	}

	/**
	 * Get accounts.
	 *
	 * @return string[]
	 */
	public function getAccounts(): array
	{
		$queryGenerator = new App\QueryGenerator('Faq');
		$queryGenerator->setFields(['accountid']);
		$queryGenerator->setDistinct('accountid');
		$queryGenerator->setCustomColumn('vtiger_account.accountname');
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_account', 'vtiger_account.accountid = vtiger_faq.accountid']);
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$rows = [];
		while ($row = $dataReader->read()) {
			$rows[$row['accountid']] = $row['accountname'];
		}
		$dataReader->close();
		return $rows;
	}
}
