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
	{include file='Header.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
	<div id="step">
		<form name="condition" action="index.php" method="post" id="dataaccess_step3" class="form-horizontal" >
			<input type="hidden" name="module" value="{$MODULE_NAME}" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="base_module" value="{$BASE_MODULE}" />
			<input type="hidden" name="tpl_id" value="{$TPL_ID}" />
			<input type="hidden" name="save_actions" value='{$ACTIONS_JASON}' />
			<div class="row padding1per contentsBackground" style="border:1px solid #ccc;box-shadow: 2px 2px 2px rgba(0, 0, 0, 0.5);">
				<h4 class="padding-bottom1per"><strong>{vtranslate('LBL_CREATION_ACTION',$QUALIFIED_MODULE)}</strong></h4>
				{vtranslate('LBL_CREATION_DESC',$QUALIFIED_MODULE)}<br>
				<select name="actions_list" class="chzn-select form-control col-md-12" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
					{foreach key=key item=item from=$ACTIONS_LIST}
						<option value="{$item}">{Settings_DataAccess_Module_Model::getActionName($item,true)}</option>
					{/foreach}
				</select>
				<br><br>
				<table class="table table-bordered table-condensed listViewEntriesTable">
					<thead>
						<tr class="listViewHeaders" >
							<th width="30%">{vtranslate('LBL_ACTION',$QUALIFIED_MODULE)}</th>
							<th>{vtranslate('LBL_ACTIONDESC',$QUALIFIED_MODULE)}</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$ACTIONS_LIST item=item key=key}
							<tr class="listViewEntries">
								<td>
									{Settings_DataAccess_Module_Model::getActionName($item,true)}
								</td>
								<td>
									{Settings_DataAccess_Module_Model::getActionName($item,false)}
								</td>
							<tr>
						{/foreach}	
					</tbody>
				</table>	
				<br>
				<div class="pull-right">
					<a class="btn btn-danger backStep" type="button" href="index.php?module={$MODULE_NAME}&parent=Settings&view=Step3&tpl_id={$TPL_ID}&base_module={$BASE_MODULE}&s=false">{vtranslate('BACK', $QUALIFIED_MODULE)}</a>&nbsp;&nbsp;
					<button class="btn btn-success" type="submit"><strong>{vtranslate('NEXT', $QUALIFIED_MODULE)}</strong></button>
					<a class="cancelLink btn btn-warning" type="reset" href="index.php?module=DataAccess&parent=Settings&view=Index">{vtranslate('CANCEL', $QUALIFIED_MODULE)}</a>
				</div>
			</div>
		</form>
	</div>
	<input type="hidden" name="next_step" value="Step4" />
	<div class="clearfix"></div>
</div>
{/strip}
