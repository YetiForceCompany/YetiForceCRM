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
	<div class="widget_header row">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
    <div class="editContainer">
        <div id="breadcrumb">
            <ul class="crumbs marginLeftZero">
                <li class="first step active" style="z-index:9" id="Step1">
                    <a>
                        <span class="stepNum">1</span>
                        <span class="stepText">{vtranslate('LBL_FILL_BASE_DATA',$MODULE_NAME)}</span>
                    </a>
                </li>
                <li style="z-index:8" class="step" id="Step2">
                    <a>
                        <span class="stepNum">2</span>
                        <span class="stepText">{vtranslate('ADD_CONDITIONS',$MODULE_NAME)}</span>
                    </a>
                </li>

            </ul>
        </div>
        <div class="clearfix"></div>
                    <div id="step">
                        {include file='Step1.tpl'|@vtemplate_path:$SETTINGS_MODULE_NAME}
                    </div>
                    <input type="hidden" name="next_step" value="Step2" />
        <div class="clearfix"></div>
    </div>
{/strip}
<script type="text/javascript" src="{Yeti_Layout::getLayoutFile('modules/Settings/'|cat:$MODULE_NAME|cat:'/resources/Edit.js')}"></script>
