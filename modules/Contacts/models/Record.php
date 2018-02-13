<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Class contacts record model.
 */
class Contacts_Record_Model extends Vtiger_Record_Model
{
    /**
     * Function returns the url for create event.
     *
     * @return string
     */
    public function getCreateEventUrl()
    {
        $calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');

        return $calendarModuleModel->getCreateEventRecordUrl().'&link='.$this->getId();
    }

    /**
     * Function returns the url for create todo.
     *
     * @return string
     */
    public function getCreateTaskUrl()
    {
        $calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');

        return $calendarModuleModel->getCreateTaskRecordUrl().'&link='.$this->getId();
    }

    /**
     * Function to get List of Fields which are related from Contacts to Inventory Record.
     *
     * @return array
     */
    public function getInventoryMappingFields()
    {
        return [
            ['parentField' => 'parent_id', 'inventoryField' => 'account_id', 'defaultValue' => ''],
            ['parentField' => 'buildingnumbera', 'inventoryField' => 'buildingnumbera', 'defaultValue' => ''],
            ['parentField' => 'localnumbera', 'inventoryField' => 'localnumbera', 'defaultValue' => ''],
            ['parentField' => 'addresslevel1a', 'inventoryField' => 'addresslevel1a', 'defaultValue' => ''],
            ['parentField' => 'addresslevel2a', 'inventoryField' => 'addresslevel2a', 'defaultValue' => ''],
            ['parentField' => 'addresslevel3a', 'inventoryField' => 'addresslevel3a', 'defaultValue' => ''],
            ['parentField' => 'addresslevel4a', 'inventoryField' => 'addresslevel4a', 'defaultValue' => ''],
            ['parentField' => 'addresslevel5a', 'inventoryField' => 'addresslevel5a', 'defaultValue' => ''],
            ['parentField' => 'addresslevel6a', 'inventoryField' => 'addresslevel6a', 'defaultValue' => ''],
            ['parentField' => 'addresslevel7a', 'inventoryField' => 'addresslevel7a', 'defaultValue' => ''],
            ['parentField' => 'addresslevel8a', 'inventoryField' => 'addresslevel8a', 'defaultValue' => ''],
            ['parentField' => 'buildingnumberb', 'inventoryField' => 'buildingnumberb', 'defaultValue' => ''],
            ['parentField' => 'localnumberb', 'inventoryField' => 'localnumberb', 'defaultValue' => ''],
            ['parentField' => 'addresslevel1b', 'inventoryField' => 'addresslevel1b', 'defaultValue' => ''],
            ['parentField' => 'addresslevel2b', 'inventoryField' => 'addresslevel2b', 'defaultValue' => ''],
            ['parentField' => 'addresslevel3b', 'inventoryField' => 'addresslevel3b', 'defaultValue' => ''],
            ['parentField' => 'addresslevel4b', 'inventoryField' => 'addresslevel4b', 'defaultValue' => ''],
            ['parentField' => 'addresslevel5b', 'inventoryField' => 'addresslevel5b', 'defaultValue' => ''],
            ['parentField' => 'addresslevel6b', 'inventoryField' => 'addresslevel6b', 'defaultValue' => ''],
            ['parentField' => 'addresslevel7b', 'inventoryField' => 'addresslevel7b', 'defaultValue' => ''],
            ['parentField' => 'addresslevel8b', 'inventoryField' => 'addresslevel8b', 'defaultValue' => ''],
        ];
    }

    /**
     * Get image details.
     *
     * @return array image details List
     */
    public function getImageDetails()
    {
        $imageDetails = [];
        $recordId = $this->getId();

        if ($recordId) {
            $result = (new App\Db\Query())->select(['vtiger_attachments.*', 'vtiger_crmentity.setype'])->from('vtiger_attachments')->innerJoin('vtiger_seattachmentsrel', 'vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid')->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_attachments.attachmentsid')->where(['vtiger_crmentity.setype' => 'Contacts Image', 'vtiger_seattachmentsrel.crmid' => $recordId])->one();

            $imageId = $result['attachmentsid'];
            $imagePath = $result['path'];
            $imageName = $result['name'];

            //\App\Purifier::decodeHtml - added to handle UTF-8 characters in file names
            $imageOriginalName = App\Purifier::decodeHtml($imageName);

            if (!empty($imageName)) {
                $imageDetails[] = [
                    'id' => $imageId,
                    'orgname' => $imageOriginalName,
                    'path' => $imagePath.$imageId,
                    'name' => $imageName,
                ];
            }
        }

        return $imageDetails;
    }

    /**
     * The function decide about mandatory save record.
     *
     * @return bool
     */
    public function isMandatorySave()
    {
        return $_FILES ? true : false;
    }

    /**
     * Function to save data to database.
     */
    public function saveToDb()
    {
        parent::saveToDb();
        $this->insertAttachment();
    }

    /**
     * This function is used to add the vtiger_attachments. This will call the function uploadAndSaveFile which will upload the attachment into the server and save that attachment information in the database.
     */
    public function insertAttachment()
    {
        $module = \App\Request::_get('module');
        $id = $this->getId();
        $db = App\Db::getInstance();
        $fileSaved = false;
        //This is to added to store the existing attachment id of the contact where we should delete this when we give new image
        $oldAttachmentid = (new App\Db\Query())->select(['vtiger_crmentity.crmid'])->from('vtiger_seattachmentsrel')
                ->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_seattachmentsrel.attachmentsid')
                ->where(['vtiger_seattachmentsrel.crmid' => $id])->scalar();
        if ($_FILES) {
            foreach ($_FILES as $fileindex => $files) {
                if (empty($files['tmp_name'])) {
                    continue;
                }
                $fileInstance = \App\Fields\File::loadFromRequest($files);
                if ($fileInstance->validate('image')) {
                    $files['original_name'] = \App\Request::_get($fileindex.'_hidden');
                    $fileSaved = $this->uploadAndSaveFile($files);
                }
            }
        }
        //Inserting image information of record into base table
        $db->createCommand()->update('vtiger_contactdetails', ['imagename' => \App\Purifier::decodeHtml($this->ext['attachmentsName'])], ['contactid' => $id])
            ->execute();
        //This is to handle the delete image for contacts
        if ($module === 'Contacts' && $fileSaved) {
            if ($oldAttachmentid) {
                $setype = (new App\Db\Query())->select(['setype'])
                    ->from('vtiger_crmentity')
                    ->where(['crmid' => $oldAttachmentid])
                    ->scalar();
                if ($setype === 'Contacts Image') {
                    $db->createCommand()->delete('vtiger_attachments', ['attachmentsid' => $oldAttachmentid])->execute();
                    $db->createCommand()->delete('vtiger_seattachmentsrel', ['attachmentsid' => $oldAttachmentid])->execute();
                }
            }
        }

        \App\Log::trace("Exiting from insertIntoAttachment($id,$module) method.");
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        parent::delete();
        \App\Db::getInstance()->createCommand()->update('vtiger_customerdetails', [
            'portal' => 0,
            'support_start_date' => null,
            'support_end_date' => null,
            ], ['customerid' => $this->getId()])->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordRelatedListViewLinksLeftSide(Vtiger_RelationListView_Model $viewModel)
    {
        $links = parent::getRecordRelatedListViewLinksLeftSide($viewModel);
        if (AppConfig::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail')) {
            if (Users_Record_Model::getCurrentUserModel()->get('internal_mailer') == 1) {
                $links['LBL_SEND_EMAIL'] = Vtiger_Link_Model::getInstanceFromValues([
                        'linklabel' => 'LBL_SEND_EMAIL',
                        'linkhref' => true,
                        'linkurl' => OSSMail_Module_Model::getComposeUrl($this->getModuleName(), $this->getId(), 'Detail', 'new'),
                        'linkicon' => 'fas fa-envelope',
                        'linkclass' => 'btn-xs btn-default',
                        'linktarget' => '_blank',
                ]);
            } else {
                $urldata = OSSMail_Module_Model::getExternalUrl($this->getModuleName(), $this->getId(), 'Detail', 'new');
                if ($urldata && $urldata !== 'mailto:?') {
                    $links[] = Vtiger_Link_Model::getInstanceFromValues([
                            'linklabel' => 'LBL_CREATEMAIL',
                            'linkhref' => true,
                            'linkurl' => $urldata,
                            'linkicon' => 'fas fa-envelope',
                            'linkclass' => 'btn-xs btn-default',
                            'relatedModuleName' => 'OSSMailView',
                    ]);
                }
            }
        }

        return $links;
    }
}
