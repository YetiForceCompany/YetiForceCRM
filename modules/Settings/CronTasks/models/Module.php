<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_CronTasks_Module_Model extends Settings_Vtiger_Module_Model
{
    public $baseTable = 'vtiger_cron_task';
    public $baseIndex = 'id';
    public $listFields = ['sequence' => 'Sequence', 'name' => 'Cron Job', 'frequency' => 'Frequency(H:M)', 'status' => 'Status', 'laststart' => 'Last Start', 'lastend' => 'Last End', 'duration' => 'Duration'];
    public $nameFields = [''];
    public $name = 'CronTasks';

    /**
     * Function to get editable fields from this module.
     *
     * @return array List of fieldNames
     */
    public function getEditableFieldsList()
    {
        return ['frequency', 'status'];
    }

    /**
     * Function to update sequence of several records.
     *
     * @param array $sequencesList
     */
    public function updateSequence($sequencesList)
    {
        $db = App\Db::getInstance();
        $caseSequence = 'CASE';
        foreach ($sequencesList as $sequence => $recordId) {
            $caseSequence .= ' WHEN '.$db->quoteColumnName('id').' = '.$db->quoteValue($recordId).' THEN '.$db->quoteValue($sequence);
        }
        $caseSequence .= ' END';
        $db->createCommand()->update('vtiger_cron_task', ['sequence' => new yii\db\Expression($caseSequence)])->execute();
    }

    public function hasCreatePermissions()
    {
        return false;
    }

    public function isPagingSupported()
    {
        return false;
    }

    /**
     * Get last executed Cron info formated by user settings.
     *
     * @return array ['duration'=>'0g 0m 0s','laststart'=>'2018-12-01 10:00:00','lastend'=>'...']
     */
    public function getLastCronInfo()
    {
        $result = [
            'duration' => '-',
            'laststart' => '-',
            'lastend' => '-',
        ];

        $lastDuration = '-';
        $moduleName = $this->getName();

        $query = (new App\Db\Query())->from($this->getBaseTable());
        $result = $query->select(['id'])->orderBy(['laststart' => SORT_DESC])->one();
        if ($result) {
            $recordId = $result['id'];
            $recordModel = Settings_CronTasks_Record_Model::getInstanceById((int) $recordId, $this->getName(true));
            $lastDuration = $recordModel->getDuration();

            $result['duration'] = $lastDuration;
            $lastStartDate = date('Y-m-d H:i:s', $recordModel->get('laststart'));
            $lastEndDate = date('Y-m-d H:i:s', $recordModel->get('lastend'));
            $result['laststart'] = \App\Fields\DateTime::formatToViewDate($lastStartDate);
            $result['lastend'] = \App\Fields\DateTime::formatToViewDate($lastEndDate);
        }

        return $result;
    }
}
