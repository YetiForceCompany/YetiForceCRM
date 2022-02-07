{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class=" LangManagement">
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-10">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
			<div class="col-md-2"></div>
		</div>
		<div class="">
			<table class="table table-bordered table-sm listViewEntriesTable">
				<thead>
					<tr class="blockHeader">
						<th><strong>{\App\Language::translate('LBL_PROFILE_NAME',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{\App\Language::translate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{\App\Language::translate('LBL_VIEW_ALL',$QUALIFIED_MODULE)}</strong></th>
						<th><strong>{\App\Language::translate('LBL_EDIT_ALL',$QUALIFIED_MODULE)}</strong></th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$GLOBALPERMISSIONS item=item key=key}
						<tr data-pid="{$key}" class="js-row" data-js="data/checked">
							<td>{$item['profilename']}</td>
							<td>{$item['description']}</td>
							<td class="textAlignCenter">
								<input class="js-save" data-js="change" type="checkbox" data-globalactionid="1" title="{\App\Language::translate('LBL_VIEW_ALL',$QUALIFIED_MODULE)}" {if $item['gp_1']== Settings_Profiles_Module_Model::IS_PERMITTED_VALUE}checked{/if}>
							</td>
							<td class="textAlignCenter">
								<input class="js-save" data-js="change" type="checkbox" title="{\App\Language::translate('LBL_EDIT_ALL',$QUALIFIED_MODULE)}" data-globalactionid="2" {if $item['gp_2']== Settings_Profiles_Module_Model::IS_PERMITTED_VALUE}checked{/if}>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
{/strip}
