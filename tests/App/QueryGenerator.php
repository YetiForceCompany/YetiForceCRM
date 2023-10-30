<?php
/**
 * QueryGenerator test file.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\App;

/**
 * QueryGenerator test class.
 */
class QueryGenerator extends \Tests\Base
{
	/**
	 * Advanced conditions test.
	 */
	public function testAdvancedConditions()
	{
		$moduleName = 'Accounts';
		$accountModel = \Tests\Base\C_RecordActions::createAccountRecord();
		$contactId = \Tests\Base\C_RecordActions::createContactRecord()->getId();
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->initForDefaultCustomView();
		$queryGenerator->setAdvancedConditions(['relationId' => '0', 'relationColumns' => ['1']]);
		$searchParams = \App\Condition::validSearchParams($moduleName, [[['relationColumn_1', 'a', $contactId]]]);
		$transformedSearchParams = $queryGenerator->parseBaseSearchParamsToCondition($searchParams);
		$this->assertEquals([
			'and' => [
				[
					'field_name' => 'relationColumn_1',
					'module_name' => false,
					'source_field_name' => false,
					'comparator' => 'a',
					'value' => $contactId,
				],
			],
		], $transformedSearchParams);
		$queryGenerator->parseAdvFilter($transformedSearchParams);
		$row = $queryGenerator->createQuery()->one() ?? [];
		$this->logs = [
			'accountId' => $accountModel->getId(),
			'contactId' => $contactId,
			'query' => $queryGenerator->createQuery()->createCommand()->getRawSql(),
			'Accounts' => (new \App\Db\Query())->select(['accountid', 'accountname', 'deleted'])->from('vtiger_account')->innerJoin('vtiger_crmentity', 'vtiger_account.accountid = vtiger_crmentity.crmid')->createCommand()->queryAllByGroup(1),
			'Contacts' => (new \App\Db\Query())->select(['contactid', 'lastname', 'deleted'])->from('vtiger_contactdetails')->innerJoin('vtiger_crmentity', 'vtiger_contactdetails.contactid = vtiger_crmentity.crmid')->createCommand()->queryAllByGroup(1),
		];
		$this->assertEquals($accountModel->get('accountname'), $row['accountname']);
		$this->assertEquals($accountModel->getId(), $row['id']);

		$relationId = 9;
		$documentModel = \Tests\Base\C_RecordActions::createDocumentsRecord();
		\Vtiger_Relation_Model::getInstanceById($relationId)->addRelation($row['id'], $documentModel->getId());

		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->setFields(['id', 'accountname']);
		$queryGenerator->setAdvancedConditions(['relationId' => '0', 'relationColumns' => [$relationId]]);
		$searchParams = \App\Condition::validSearchParams($moduleName, [[['relationColumn_' . $relationId, 'a', $documentModel->getId()]]]);
		$transformedSearchParams = $queryGenerator->parseBaseSearchParamsToCondition($searchParams);
		$this->assertEquals([
			'and' => [
				[
					'field_name' => 'relationColumn_' . $relationId,
					'module_name' => false,
					'source_field_name' => false,
					'comparator' => 'a',
					'value' => $documentModel->getId(),
				],
			],
		], $transformedSearchParams);
		$queryGenerator->parseAdvFilter($transformedSearchParams);
		$row = $queryGenerator->createQuery()->one() ?? [];
		$this->assertEquals($accountModel->get('accountname'), $row['accountname']);
		$this->assertEquals($accountModel->getId(), $row['id']);

		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->setFields(['id', 'accountname']);
		$queryGenerator->setAdvancedConditions([
			'relationId' => $relationId,
			'relationConditions' => ['condition' => 'AND', 'rules' => [['fieldname' => 'notes_title:Documents', 'operator' => 'e', 'value' => $documentModel->get('notes_title')]]],
		]);
		$this->logs['AccountsAdvancedConditions'] = $rows = $queryGenerator->createQuery()->createCommand()->queryAllByGroup(1);
		$this->logs['DocumentsId'] = $documentModel->getId();
		$this->logs['Documents'] = (new \App\Db\Query())->select(['vtiger_notes.notesid', 'title', 'deleted'])->from('vtiger_notes')->innerJoin('vtiger_crmentity', 'vtiger_notes.notesid = vtiger_crmentity.crmid')->createCommand()->queryAllByGroup(1);
		$this->logs['DocumentsRel'] = (new \App\Db\Query())->from('vtiger_senotesrel')->all();
		$this->assertArrayHasKey($accountModel->getId(), $rows);
	}
}
