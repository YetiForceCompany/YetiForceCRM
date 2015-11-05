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
<table class="table table-bordered table-condensed listViewEntriesTable">
	<thead>
		<tr class="listViewHeaders" >
			<th width="30%">{vtranslate('LBL_ACTION',$QUALIFIED_MODULE)}</th>
			<th>{vtranslate('LBL_ACTIONDESC',$QUALIFIED_MODULE)}</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	{if !empty($ACTIONS_SELECTED)}
		{foreach from=$ACTIONS_SELECTED item=item key=key}
		<tr class="listViewEntries" data-key="{$key}">
			<td>{Settings_DataAccess_Module_Model::getActionName($item['an'],true)}</td>
			<td>{Settings_DataAccess_Module_Model::getActionName($item['an'],false)}</td>
			<td>
				<a href='index.php?module={$MODULE_NAME}&parent=Settings&action=DeleteAction&id={$TPL_ID}&a={$key}&m={$BASE_MODULE}'  class="pull-right marginRight10px">
					<i type="{vtranslate('REMOVE_TPL', $MODULE_NAME)}" class="glyphicon glyphicon-trash alignMiddle"></i>
				</a>
				{if $item['cf'] != 0}
					<a href='index.php?module={$MODULE_NAME}&parent=Settings&view=ActionConfig&did={$TPL_ID}&aid={$key}&an={$item['an']}&m={$BASE_MODULE}'  class="pull-right edit_tpl">
						<span class="glyphicon glyphicon-pencil alignMiddle" aria-hidden="true" title="{vtranslate('LBL_EDIT')}"></span>&nbsp;
					</a>
				{/if}
			</td>
		<tr>
		{/foreach}
	{else}
		<tr>
			<td class="textAlignCenter" colspan="3">
				{vtranslate('LBL_NO_ACTION',$QUALIFIED_MODULE)}
			</td>
		</tr>
	{/if}
	</tbody>
</table>
