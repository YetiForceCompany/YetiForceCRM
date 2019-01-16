{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="tpl-Settings-LangManagement-LangList">
	<div class="btn-toolbar" role="toolbar">
		<div class="btn-group mr-2" role="group">
			<a class="btn btn-primary btn-sm float-right marginBottom10px" href="{Settings_ModuleManager_Module_Model::getUserModuleImportUrl()}"><span class="fas fa-file-import u-mr-5px"></span>{\App\Language::translate('LBL_IMPORT_LANG', $QUALIFIED_MODULE)}
			</a>
		</div>
		<div class="btn-group mr-2" role="group">
			<button class="btn btn-info add_lang btn-sm float-right marginBottom10px">
				<span class="fa fa-plus u-mr-5px"></span>{\App\Language::translate('LBL_ADD_LANG', $QUALIFIED_MODULE)}
			</button>
		</div>
		{if $IS_NET_CONNECTED}
			<div class="btn-group mr-2" role="group">
				<button class="btn btn-success btn-sm u-h-fit mr-1 js-add-languages-modal" type="button" data-js="click">
					<span class="fas fas fa-download mr-1"></span>
					{\App\Language::translate('LBL_DOWNLOAD_LANG', 'Settings::YetiForce')}
				</button>
			</div>
		{/if}
	</div>
	<table class="table tableRWD table-bordered table-sm listViewEntriesTable">
		<thead>
		<tr class="blockHeader">
			<th><strong>{\App\Language::translate('LBL_Lang_name',$QUALIFIED_MODULE)}</strong></th>
			<th><strong>{\App\Language::translate('LBL_Lang_prefix',$QUALIFIED_MODULE)}</strong></th>
			<th><strong>{\App\Language::translate('LBL_LAST_UPDATE',$QUALIFIED_MODULE)}</strong></th>
			<th><strong>{\App\Language::translate('LBL_Lang_action',$QUALIFIED_MODULE)}</strong></th>
		</tr>
		</thead>
		<tbody>
		{foreach from=App\Language::getAll(false, true) item=LANG key=ID}
			<tr class="js-lang-row" data-prefix="{$LANG['prefix']}">
				<td>{$LANG['name']}</td>
				<td>{$LANG['prefix']}</td>
				<td class="js-last-update" data-js="html">{$LANG['lastupdated']}</td>
				<td>
					<a href="index.php?module=LangManagement&parent=Settings&action=Export&lang={$LANG['prefix']}" class="btn btn-primary btn-sm marginLeft10">{\App\Language::translate('Export',$QUALIFIED_MODULE)}</a>
					{if $LANG['isdefault'] neq '1'}
						<button class="btn btn-success btn-sm marginLeft10" data-toggle="confirmation" id="setAsDefault">{\App\Language::translate('LBL_DEFAULT',$QUALIFIED_MODULE)}</button>
						<button class="btn btn-danger btn-sm" data-toggle="confirmation" data-original-title="" id="deleteItemC">{\App\Language::translate('LBL_Delete',$QUALIFIED_MODULE)}</button>
					{/if}
					{if $IS_NET_CONNECTED}
						<button class="js-update btn btn-outline-primary btn-sm" data-js="click | data | class: fa-spin">
							<span class="js-update__icon fas fa-sync fa-xs mr-1"></span>
							{\App\Language::translate('LBL_UPDATE', 'Settings::YetiForce')}
						</button>
					{/if}
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>
