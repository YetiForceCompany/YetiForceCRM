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
<div class=" LangManagement">
	<div class="widget_header row">
		<div class="col-md-10">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
		<div class="col-md-2"></div>
	</div>
	<div class="">
		<table class="table table-bordered table-condensed listViewEntriesTable">
			<thead>
				<tr class="blockHeader">
					<th><strong>{vtranslate('LBL_PROFILE_NAME',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('LBL_VIEW_ALL',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('LBL_EDIT_ALL',$QUALIFIED_MODULE)}</strong></th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$GLOBALPERMISSIONS item=item key=key}
					<tr data-pid="{$key}">
						<td>{$item['profilename']}</td>
						<td>{$item['description']}</td>
						<td class="textAlignCenter">
							<input class="GP_SAVE" type="checkbox" data-globalactionid="1" title="{vtranslate('LBL_VIEW_ALL',$QUALIFIED_MODULE)}" {if $item['gp_1']== Settings_Profiles_Module_Model::IS_PERMITTED_VALUE}checked{/if}>
						</td>
						<td class="textAlignCenter">
							<input class="GP_SAVE" type="checkbox" title="{vtranslate('LBL_EDIT_ALL',$QUALIFIED_MODULE)}" data-globalactionid="2" {if $item['gp_2']== Settings_Profiles_Module_Model::IS_PERMITTED_VALUE}checked{/if}>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{/strip}
