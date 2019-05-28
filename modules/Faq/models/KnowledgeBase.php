<?php
/**
 * Model of KnowledgeBase.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$queryGenerator->addNativeCondition(['faqstatus' => 'Published']);
		$queryGenerator->addNativeCondition(['category' => $categories]);
		$queryGenerator->addNativeCondition(['featured' => 1]);
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
		$queryGenerator->addNativeCondition(['faqstatus' => 'Published']);
		if ($this->has('parentCategory')) {
			$queryGenerator->addNativeCondition(['category' => $this->get('parentCategory')]);
		}
		$queryGenerator->setLimit(Config\Modules\Faq::$knowledgeBaseArticleLimit);
		return $queryGenerator->createQuery();
	}
}
