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
	<form class="form-horizontal" method="post" action="index.php">
		<input type="hidden" name="view" value="Edit2" />
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" name="parent" value="Settings" />
		{if $TPL_ID}
			<input type="hidden" name="tpl_id" value="{$TPL_ID}" />
		{/if}
		<div class="padding1per" style="border:1px solid #ccc;">
			<label>
				<strong>{vtranslate('LBL_STEP_1',$QUALIFIED_MODULE)}: {vtranslate('LBL_ENTER_BASIC_INFO',$QUALIFIED_MODULE)}</strong>
			</label>
			<br>
			<div class="form-group">
				<label class="col-sm-2 control-label">
					{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-5 controls">
					{if $MODE eq 'edit'}
						<input type='text' class="form-control" disabled='disabled' value="{vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}" >
						<input type='hidden' name='module_name' value="{$MODULE_MODEL->get('name')}" >
					{else}
						<select class="chzn-select form-control" id="moduleName" name="module_name" required="true" data-placeholder="Select Module...">
							{foreach from=$MODULE_LIST item=item key=key}
								<option value="{$item}" {if $BASE_INFO['module_name'] eq $item} selected {/if}>{vtranslate($item, $item)}</option>
							{/foreach}
						</select>
					{/if}
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">
					{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}<span class="redColor">*</span>
				</label>
				<div class="col-sm-5 controls">
					<input type="text" name="summary" class="form-control" data-validation-engine='validate[required]' value="{$BASE_INFO['summary']}" id="summary" />
				</div>
			</div>
		</div>
		<br />
		<div class="pull-right">
			<button class="btn btn-success" id="next_step">{vtranslate('NEXT', $MODULE_NAME)}</button>
			<a href="index.php?module={$MODULE_NAME}&parent=Settings&view=Index" class="cancelLink btn btn-warning">{vtranslate('CANCEL', $MODULE_NAME)}</a>
		</div>
		<div class="clearfix"></div>
	</form>

