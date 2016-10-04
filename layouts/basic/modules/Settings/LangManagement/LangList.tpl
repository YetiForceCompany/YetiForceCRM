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
<button class="btn btn-primary add_lang btn-sm pull-right marginBottom10px">{vtranslate('LBL_ADD_LANG', $QUALIFIED_MODULE)}</button>
<table  class="table tableRWD table-bordered table-condensed listViewEntriesTable">
	<thead>
		<tr class="blockHeader">
			<th><strong>{vtranslate('LBL_Lang_label',$QUALIFIED_MODULE)}</strong></th>
			<th><strong>{vtranslate('LBL_Lang_name',$QUALIFIED_MODULE)}</strong></th>
			<th><strong>{vtranslate('LBL_Lang_prefix',$QUALIFIED_MODULE)}</strong></th>
			{*<th class="textAlignCenter" colspan="2"><strong>{vtranslate('LBL_Lang_active',$QUALIFIED_MODULE)}</strong></th>*}
			<th><strong>{vtranslate('LBL_Lang_action',$QUALIFIED_MODULE)}</strong></th>
		</tr>
		{*<tr class="blockHeader">
			<th></th>
			<th></th>
			<th></th>
			<th class="textAlignCenter">{vtranslate('LBL_Lang_ac_admin',$QUALIFIED_MODULE)}</th>
			<th class="textAlignCenter">{vtranslate('LBL_Lang_ac_user',$QUALIFIED_MODULE)}</th>
			<th></th>
		</tr>*}
	</thead>
	<tbody>
		{foreach from=$MODULE_MODEL->getLang() item=LANG key=ID}
			<tr data-prefix="{$LANG['prefix']}">
				<td>{$LANG['label']}</td>
				<td>{$LANG['name']}</td>
				<td>{$LANG['prefix']}</td>
				{* TO DO
				<td class="textAlignCenter">
					<input type="checkbox" data-name="ac_user" {if $LANG['ac_user']}checked{/if}>
				</td>
				<td class="textAlignCenter">
					<input type="checkbox" data-name="ac_admin" {if $LANG['ac_admin']}checked{/if}>
				</td>*}
				<td>
					<a href="index.php?module=LangManagement&parent=Settings&action=Export&lang={$LANG['prefix']}" class="btn btn-primary btn-xs marginLeft10">{vtranslate('Export',$QUALIFIED_MODULE)}</a>
					{if $LANG['isdefault'] neq '1'}
						<button class="btn btn-success btn-xs marginLeft10" data-toggle="confirmation" id="setAsDefault">{vtranslate('LBL_DEFAULT',$QUALIFIED_MODULE)}</button>
						<button class="btn btn-danger btn-xs" data-toggle="confirmation" data-original-title="" id="deleteItemC">{vtranslate('LBL_Delete',$QUALIFIED_MODULE)}</button>
					{/if}
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>
