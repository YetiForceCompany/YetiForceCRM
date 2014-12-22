{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
    <div class="editContainer" style="padding-left: 3%;padding-right: 3%">
        <h3>
            {if $RECORDID eq ''}
                {vtranslate('LBL_CREATING_WORKFLOW',$QUALIFIED_MODULE)}
            {else}
                {vtranslate('LBL_EDITING_WORKFLOW',$QUALIFIED_MODULE)} : {$WORKFLOW_MODEL->get('summary')}
            {/if}
        </h3>
        <hr>
        <div id="breadcrumb">
            <ul class="crumbs marginLeftZero">
                <li class="first step"  style="z-index:9" id="step1">
                    <a>
                        <span class="stepNum">1</span>
                        <span class="stepText">{vtranslate('SCHEDULE_WORKFLOW',$QUALIFIED_MODULE)}</span>
                    </a>
                </li>
                <li style="z-index:8" class="step" id="step2">
                    <a>
                        <span class="stepNum">2</span>
                        <span class="stepText">{vtranslate('ADD_CONDITIONS',$QUALIFIED_MODULE)}</span>
                    </a>
                </li>
                <li class="step last" style="z-index:7" id="step3">
                    <a>
                        <span class="stepNum">3</span>
                        <span class="stepText">{vtranslate('ADD_TASKS',$QUALIFIED_MODULE)}</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
{/strip}