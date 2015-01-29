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
<div class="container-fluid editViewContainer">
	<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
	<input type="hidden" name="module" value="TreesManager"/>
	<input type="hidden" name="parent" value="Settings"/>
	<input type="hidden" name="action" value="Save"/>
	<input type="hidden" name="record" value="{$RECORD_ID}" />
	<input type="hidden" id="treeLastID" value="{$LAST_ID}" />
	<input type="hidden" id="access" value="{$ACCESS}" />
	<input type="hidden" name="tree" id="treeValues" value='{Vtiger_Util_Helper::toSafeHTML($TREE)}' />
	<div class="widget_header row-fluid">
		{if $MODE eq 'edit'}
			<h3>{vtranslate('LBL_EDIT_TEMPLATE_TREES', $QUALIFIED_MODULE)}</h3>
		{else}
			<h3>{vtranslate('LBL_CREATING_TEMPLATE_TREES', $QUALIFIED_MODULE)}</h3>
		{/if}
	</div>
	<hr>
	<div class="row-fluid">
		<label class="span3"><strong>{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}<span class="redColor">*</span>: </strong></label>
		<div class="input-append span7">
			<input type="text" class="fieldValue span7 " name="name" id="treeename" value="{$RECORD_MODEL->get('name')}" data-validation-engine='validate[required]'  />
		</div>
	</div>
	<br>
	<div class="row-fluid">
		<label class="span3"><strong>{vtranslate('LBL_MODULE', $QUALIFIED_MODULE)}: </strong></label>
		<div class="span8 fieldValue">
			{assign var="SUPPORTED_MODULE_MODELS" value=Settings_Workflows_Module_Model::getSupportedModules()}
			<select class="select2" name="templatemodule" {if !$ACCESS} disabled {/if} style="width: 300px;">
				{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
					<option {if $SOURCE_MODULE eq $TAB_ID} selected="" {/if} value="{$TAB_ID}">
						{if $MODULE_MODEL->getName() eq 'Calendar'}
							{vtranslate('LBL_TASK', $MODULE_MODEL->getName())}
						{else}
							{vtranslate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
						{/if}
					</option>
				{/foreach}
			</select>
			{if !$ACCESS} 
				<input type="text" class="fieldValue hide" name="templatemodule" value="{$SOURCE_MODULE}"/>
			{/if}
		</div>
	</div>
	<hr>
	<div class="row-fluid">
		<label class="span3"><strong>{vtranslate('LBL_ADD_ITEM_TREE', $QUALIFIED_MODULE)}</strong></label>
		<div class="span5">
			<div class="input-append">
				<input type="text" class="fieldValue span4 addNewElement">
				<a class="btn addNewElementBtn"><strong>{vtranslate('LBL_ADD_TO_TREES', $QUALIFIED_MODULE)}</strong></a>
			</div>
		</div>
	</div>
	<hr>
	<div class="modal-header contentsBackground">
		<div id="treeContents"></div>
	</div>
	<br>
	<div class="pull-right">
		<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
		<a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
	</div>
	<div class="clearfix"></div>
</div>
{/strip}