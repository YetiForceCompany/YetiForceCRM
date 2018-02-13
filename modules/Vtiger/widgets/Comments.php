<?php

/**
 * Vtiger comments widget class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Comments_Widget extends Vtiger_Basic_Widget
{
    public $dbParams = ['relatedmodule' => 'ModComments'];

    public function getUrl()
    {
        return 'module='.$this->Module.'&view=Detail&record='.$this->Record.'&mode=showRecentComments&page=1&limit='.$this->Data['limit'];
    }

    public function getConfigTplName()
    {
        return 'CommentsConfig';
    }

    public function getWidget()
    {
        $widget = [];
        $modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
        if ($this->moduleModel->isCommentEnabled() && $modCommentsModel->isPermitted('EditView')) {
            $this->Config['url'] = $this->getUrl();
            $widget = $this->Config;
        }

        return $widget;
    }
}
