{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}
    <div class="editContainer" style="padding-left: 3%;padding-right: 3%">
        <h3>
            {if $RECORDID eq ''}
                {vtranslate('LBL_CREATING_TPL',$MODULE_NAME)}
            {else}
                {vtranslate('LBL_EDITING_TPL',$MODULE_NAME)}
            {/if}
        </h3>

        <hr>
        <div id="breadcrumb">
            <ul class="crumbs marginLeftZero">
                <li class="first step active" style="z-index:9" id="Step1">
                    <a>
                        <span class="stepNum">1</span>
                        <span class="stepText">{vtranslate('ADD_MILESTONE',$MODULE_NAME)}</span>
                    </a>
                </li>
                <li style="z-index:8" class="step" id="Step2">
                    <a>
                        <span class="stepNum">2</span>
                        <span class="stepText">{vtranslate('ADD_PROJECT_TASKS',$MODULE_NAME)}</span>
                    </a>
                </li>

            </ul>
        </div>
                    <div id="step">
                        {include file='Step1.tpl'|@vtemplate_path:$SETTINGS_MODULE_NAME}
                    </div>
                    <div class="pull-right">
                        <a href="index.php?module=OSSProjectTemplates&parent=Settings&view=Edit2&tpl_id={$PARENT_TPL_ID}" class="btn btn-success">{vtranslate('NEXT', $MODULE_NAME)}</a>
                        <a href="index.php?module=OSSProjectTemplates&parent=Settings&view=Index" class="cancelLink btn btn-warning" type="reset">{vtranslate('CANCEL', $MODULE_NAME)}</a>
                    </div>
                    <input type="hidden" name="next_step" value="Step2" />
    </div>
{/strip}
<script type="text/javascript" src="{Yeti_Layout::getLayoutFile('modules/Settings/OSSProjectTemplates/resources/Edit.js')}"></script>
