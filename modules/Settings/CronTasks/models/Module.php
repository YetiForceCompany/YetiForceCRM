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
    public $listFields = ['sequence' => 'Sequence', 'name' => 'Cron Job', 'frequency' => 'Frequency(H:M)', 'status' => 'Status', 'laststart' => 'Last Start', 'lastend' => 'Last End', 'duration' => 'LBL_DURATION'];
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
     * Get last executed Cron iteration info formated by user settings.
     *
     * @return array ['duration'=>'0g 0m 0s','laststart'=>'3 hours ago','lastend'=>'4 hours ago']
     */
    public function getLastCronIteration()
    {
        $result = [];
        $totalDiff = 0;
        $finalLastStart = 0;
        $finalLastEnd = 0;

        $tasks = (new \App\Db\Query())
            ->from('vtiger_cron_task')
            ->where(['>', 'status', Settings_CronTasks_Record_Model::$STATUS_DISABLED])
            ->createCommand()
            ->query()
            ->readAll();

        $running = false;
        $timedout = false;
        $record = new Settings_CronTasks_Record_Model();
        foreach ($tasks as $task) {
            $record = new Settings_CronTasks_Record_Model($task);
            $lastStart = $record->get('laststart');
            $lastEnd = $record->get('lastend');
            if (!$record->isRunning() && !$record->hadTimedout()) {
                $totalDiff += (int) $record->getTimeDiff();
                if ($lastEnd > $finalLastEnd) {
                    $finalLastEnd = $lastEnd;
                }
                if ($lastStart > $finalLastStart) {
                    $finalLastStart = $lastStart;
                }
            } elseif ($record->hadTimedout()) {
                $timedout = $record;
            } elseif ($record->isRunning()) {
                $running = $record;
            }
        }

        if ($running && !$timedout) {
            $result['duration'] = $running->getDuration();
        } elseif ($timedout) {
            $result['duration'] = $timedout->getDuration();
        } else {
            $result['duration'] = \App\Fields\Time::formatToHourText(\App\Fields\Time::secondsToDecimal($totalDiff));
        }
        $result['laststart'] = \App\Fields\DateTime::formatToViewDate(date('Y-m-d H:i:s', $finalLastStart));
        $result['lastend'] = \App\Fields\DateTime::formatToViewDate(date('Y-m-d H:i:s', $finalLastEnd));

        return $result;
    }
}
