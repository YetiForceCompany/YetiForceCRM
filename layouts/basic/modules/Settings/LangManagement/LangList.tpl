{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="tpl-Settings-LangManagement-LangList">
<button class="btn btn-primary add_lang btn-sm float-right marginBottom10px"><span class="fa fa-plus u-mr-5px"></span>{\App\Language::translate('LBL_ADD_LANG', $QUALIFIED_MODULE)}</button>
<table  class="table tableRWD table-bordered table-sm listViewEntriesTable">
	<thead>
		<tr class="blockHeader">
			<th><strong>{\App\Language::translate('LBL_Lang_label',$QUALIFIED_MODULE)}</strong></th>
			<th><strong>{\App\Language::translate('LBL_Lang_name',$QUALIFIED_MODULE)}</strong></th>
			<th><strong>{\App\Language::translate('LBL_Lang_prefix',$QUALIFIED_MODULE)}</strong></th>
			<th><strong>{\App\Language::translate('LBL_Lang_action',$QUALIFIED_MODULE)}</strong></th>
		</tr>
	</thead>
	<tbody>
		{foreach from=App\Language::getAll(false, true) item=LANG key=ID}
			<tr data-prefix="{$LANG['prefix']}">
				<td>{$LANG['label']}</td>
				<td>{$LANG['name']}</td>
				<td>{$LANG['prefix']}</td>
				<td>
					<a href="index.php?module=LangManagement&parent=Settings&action=Export&lang={$LANG['prefix']}" class="btn btn-primary btn-sm marginLeft10">{\App\Language::translate('Export',$QUALIFIED_MODULE)}</a>
					{if $LANG['isdefault'] neq '1'}
						<button class="btn btn-success btn-sm marginLeft10" data-toggle="confirmation" id="setAsDefault">{\App\Language::translate('LBL_DEFAULT',$QUALIFIED_MODULE)}</button>
						<button class="btn btn-danger btn-sm" data-toggle="confirmation" data-original-title="" id="deleteItemC">{\App\Language::translate('LBL_Delete',$QUALIFIED_MODULE)}</button>
					{/if}
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>
</div>
