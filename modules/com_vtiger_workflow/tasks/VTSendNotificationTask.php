<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

/**
 * Class VTSendNotificationTask.
 */
class VTSendNotificationTask extends VTTask
{
    /**
     * Sending email takes more time, this should be handled via queue all the time.
     *
     * @var bool
     */
    public $executeImmediately = true;

    /**
     * Get field names.
     *
     * @return array
     */
    public function getFieldNames()
    {
        return ['template'];
    }

    /**
     * Execute task.
     *
     * @param Vtiger_Record_Model $recordModel
     */
    public function doTask($recordModel)
    {
        if (is_numeric($this->template) && $this->template) {
            $entityId = $recordModel->getId();
            $result = (new \App\Db\Query())
                    ->select(['vtiger_activity.*', 'vtiger_crmentity.description', 'vtiger_crmentity.smownerid as assigned_user_id', 'vtiger_crmentity.modifiedtime', 'vtiger_crmentity.createdtime', 'vtiger_activity_reminder.reminder_time'])
                    ->from('vtiger_activity')
                    ->innerJoin('vtiger_crmentity', 'vtiger_activity.activityid = vtiger_crmentity.crmid')
                    ->leftJoin('vtiger_activity_reminder', 'vtiger_activity_reminder.activity_id = vtiger_activity.activityid')
                    ->where(['vtiger_crmentity.deleted' => 0, 'vtiger_activity.activityid' => $entityId])->all();

            $moduleModel = $recordModel->getModule();
            $moduleModel->setEventFieldsForExport();
            $moduleModel->setTodoFieldsForExport();
            $exportData = new Calendar_Export_Model();
            $iCal = $exportData->output('', $result, $moduleModel, '', true);
            $resultInvitees = (new \App\Db\Query())->from('u_#__activity_invitation')->where(['activityid' => $entityId])->createCommand()->query();
            while ($recordinfo = $resultInvitees->read()) {
                $userModel = App\User::getUserModel($recordinfo['inviteeid']);
                if ($userModel->getDetail('status') === 'Active') {
                    \App\Mailer::sendFromTemplate([
                        'template' => $this->template,
                        'moduleName' => $recordModel->getModuleName(),
                        'recordId' => $entityId,
                        'to' => $userModel->getDetail('email1'),
                        'cc' => $this->copy_email,
                        'language' => $userModel->getDetail('language'),
                        'to_email_mod' => 'Users',
                        'params' => ['ics' => $iCal],
                    ]);
                }
            }
        }
    }
}
