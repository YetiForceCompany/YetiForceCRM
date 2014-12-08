{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
    <form name="EditWorkflow" action="index.php" method="post" id="workflow_step3" class="form-horizontal">
        <input type="hidden" name="module" value="Workflows" />
        <input type="hidden" name="record" value="{$RECORD}" />
        <input type="hidden" class="step" value="3" />
        <div class="btn-group">
            <a class="btn dropdown-toggle addButton" data-toggle="dropdown" href="#">
                <strong>{vtranslate('LBL_ADD_TASK',$QUALIFIED_MODULE)}</strong>&nbsp;
                <span><img class="imageElement" src="{vimage_path('downArrowWhite.png')}" /></span>
            </a>
            <ul class="dropdown-menu">
                {foreach from=$TASK_TYPES item=TASK_TYPE}
                    <li><a class="cursorPointer" data-url="{$TASK_TYPE->getEditViewUrl()}&for_workflow={$RECORD}">{vtranslate($TASK_TYPE->get('label'),$QUALIFIED_MODULE)}</a></li>
                {/foreach}
            </ul>
        </div>
        <div id="taskListContainer">
            {include file='TasksList.tpl'|@vtemplate_path:$QUALIFIED_MODULE}	
        </div>
        <br>
        <div class="pull-right">
            <button class="btn btn-danger backStep" type="button"><strong>{vtranslate('LBL_BACK', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
            <button class="btn btn-success" type="button" onclick="javascript:window.history.back();"><strong>{vtranslate('LBL_FINISH', $QUALIFIED_MODULE)}</strong></button>
        </div>
        <div class="clearfix"></div>
    </form>
{/strip}