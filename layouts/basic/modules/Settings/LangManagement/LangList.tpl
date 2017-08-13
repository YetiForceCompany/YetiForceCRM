{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<button class="btn btn-primary add_lang btn-sm pull-right marginBottom10px">{\App\Language::translate('LBL_ADD_LANG', $QUALIFIED_MODULE)}</button>
<table  class="table tableRWD table-bordered table-condensed listViewEntriesTable">
	<thead>
		<tr class="blockHeader">
			<th><strong>{\App\Language::translate('LBL_Lang_label',$QUALIFIED_MODULE)}</strong></th>
			<th><strong>{\App\Language::translate('LBL_Lang_name',$QUALIFIED_MODULE)}</strong></th>
			<th><strong>{\App\Language::translate('LBL_Lang_prefix',$QUALIFIED_MODULE)}</strong></th>
			{*<th class="textAlignCenter" colspan="2"><strong>{\App\Language::translate('LBL_Lang_active',$QUALIFIED_MODULE)}</strong></th>*}
			<th><strong>{\App\Language::translate('LBL_Lang_action',$QUALIFIED_MODULE)}</strong></th>
		</tr>
		{*<tr class="blockHeader">
			<th></th>
			<th></th>
			<th></th>
			<th class="textAlignCenter">{\App\Language::translate('LBL_Lang_ac_admin',$QUALIFIED_MODULE)}</th>
			<th class="textAlignCenter">{\App\Language::translate('LBL_Lang_ac_user',$QUALIFIED_MODULE)}</th>
			<th></th>
		</tr>*}
	</thead>
	<tbody>
		{foreach from=App\Language::getAll() item=LANG key=ID}
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
					<a href="index.php?module=LangManagement&parent=Settings&action=Export&lang={$LANG['prefix']}" class="btn btn-primary btn-xs marginLeft10">{\App\Language::translate('Export',$QUALIFIED_MODULE)}</a>
					{if $LANG['isdefault'] neq '1'}
						<button class="btn btn-success btn-xs marginLeft10" data-toggle="confirmation" id="setAsDefault">{\App\Language::translate('LBL_DEFAULT',$QUALIFIED_MODULE)}</button>
						<button class="btn btn-danger btn-xs" data-toggle="confirmation" data-original-title="" id="deleteItemC">{\App\Language::translate('LBL_Delete',$QUALIFIED_MODULE)}</button>
					{/if}
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>
