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
			<th width="30%">{vtranslate('LBL_MODULE_NAME',$QUALIFIED_MODULE)} </th>
			<th>{vtranslate('DOC_NAME',$QUALIFIED_MODULE)}</th>
			<th colspan="2"></th>
		</tr>
	</thead>
	<tbody>
		{if !empty($DOC_TPL_LIST)}
			{foreach from=$DOC_TPL_LIST item=item key=key}
				<tr class="listViewEntries" data-id="{$item.id}">
					<td onclick="location.href = jQuery(this).data('url')" data-url="index.php?module={$MODULE_NAME}&parent=Settings&view=Step1&tpl_id={$item.id}">{vtranslate($item.module, $item.module)}</td>
					<td onclick="location.href = jQuery(this).data('url')" data-url="index.php?module={$MODULE_NAME}&parent=Settings&view=Step1&tpl_id={$item.id}">{vtranslate($item.summary, $QUALIFIED_MODULE)}</td>
						<td><a class="pull-right edit_tpl"><!--<i title="{vtranslate('LBL_EDIT')}" class="glyphicon glyphicon-pencil alignMiddle"></i>--></a>
						<a href='index.php?module={$MODULE_NAME}&parent=Settings&action=DeleteTemplate&tpl_id={$item.id}' class="pull-right marginRight10px">
							<span type="{vtranslate('REMOVE_TPL', $MODULE_NAME)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
					</td>
				<tr>
				{/foreach}
			{else}
			<tr>
				<td>
					{vtranslate('LBL_NO_PROJECT_TPL_ADDED',$QUALIFIED_MODULE)}
				</td>
				<td>
				</td>
				<td>
				</td>
			</tr>
		{/if}
	</tbody>
</table>
