{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<table class="table table-bordered table-condensed listViewEntriesTable">
	<thead>
		<tr class="listViewHeaders" >
			<th width="30%">{\App\Language::translate('LBL_MODULE_NAME',$QUALIFIED_MODULE)} </th>
			<th>{\App\Language::translate('DOC_NAME',$QUALIFIED_MODULE)}</th>
			<th colspan="2"></th>
		</tr>
	</thead>
	<tbody>
		{if !empty($DOC_TPL_LIST)}
			{foreach from=$DOC_TPL_LIST item=item key=key}
				<tr class="listViewEntries" data-id="{$item.id}">
					<td onclick="location.href = jQuery(this).data('url')" data-url="index.php?module={$MODULE_NAME}&parent=Settings&view=Step1&tpl_id={$item.id}">{\App\Language::translate($item.module, $item.module)}</td>
					<td onclick="location.href = jQuery(this).data('url')" data-url="index.php?module={$MODULE_NAME}&parent=Settings&view=Step1&tpl_id={$item.id}">{\App\Language::translate($item.summary, $QUALIFIED_MODULE)}</td>
						<td><a class="pull-right edit_tpl"><!--<i title="{\App\Language::translate('LBL_EDIT')}" class="glyphicon glyphicon-pencil alignMiddle"></i>--></a>
						<a href='index.php?module={$MODULE_NAME}&parent=Settings&action=DeleteTemplate&tpl_id={$item.id}' class="pull-right marginRight10px">
							<span type="{\App\Language::translate('REMOVE_TPL', $MODULE_NAME)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
					</td>
				<tr>
				{/foreach}
			{else}
			<tr>
				<td>
					{\App\Language::translate('LBL_NO_PROJECT_TPL_ADDED',$QUALIFIED_MODULE)}
				</td>
				<td>
				</td>
				<td>
				</td>
			</tr>
		{/if}
	</tbody>
</table>
