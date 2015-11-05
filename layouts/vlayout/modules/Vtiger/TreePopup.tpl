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
<div id="treePopupContainer" class="contentsDiv paddingLeftRight10px">
	<input type="hidden" class="triggerEventName" value="{$TRIGGER_EVENT_NAME}"/>
	<input type="hidden" name="src_record" value="{$SRC_RECORD}" />
	<input type="hidden" name="src_field" value="{$SRC_FIELD}" />
	<input type="hidden" name="template" value="{$TEMPLATE}" />
	<input type="hidden" id="treeLastID" value="{$LAST_ID}" />
	<input type="hidden" name="tree" id="treePopupValues" value='{Vtiger_Util_Helper::toSafeHTML($TREE)}' />
	{assign var="MODULE_INSTANCE" value=Vtiger_Module_Model::getInstance($MODULE)}
	{assign var="FIELD_INSTANCE" value=Vtiger_Field_Model::getInstance($SRC_FIELD,$MODULE_INSTANCE)}
	{assign var="COMPANY_DETAILS" value=Vtiger_CompanyDetails_Model::getInstanceById()}
	{assign var="COMPANY_LOGO" value=$COMPANY_DETAILS->getLogo()}
	<div class="paddingLeftRight10px">
	    <div class="popupContainer row">
	        <div class="logo col-md-6"><img src="{$COMPANY_LOGO->get('imagepath')}" title="{$COMPANY_LOGO->get('title')}" alt="{$COMPANY_LOGO->get('alt')}" width="160px;"/></div>
	    </div>
		<div class="widget_header row">
			<h3>{vtranslate('LBL_SELECT_TREE_ITEM', $MODULE)} {vtranslate($FIELD_INSTANCE->get('label'), $MODULE)}</h3>
		</div>
		<div class="contentsBackground">
			<div id="treePopupContents"></div>
		</div>
	</div>
</div>
{* javascript files *}
{include file='JSResources.tpl'|@vtemplate_path}
</body>
</html>
{/strip}
