{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div id="modTrackerContainer">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="contents">
			<div class="contents tabbable mt-2">
				<table class="table table-bordered table-sm listViewEntriesTable">
					<thead>
						<tr class="blockHeader">
							<th><strong>{\App\Language::translate('LBL_MODULE',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_ACTIVE',$QUALIFIED_MODULE)}</strong></th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$MODULE_MODEL->getModTrackerModules() item=item key=key}
							<tr data-id="{$item.id}" class="js-row" data-js="data">
								<td>{\App\Language::translate($item.module,$item.module)}</td>
								<td>
									<input class="js-active-modtracker" data-js="change" type="checkbox" name="active" value="1" {if $item.active}checked="" {/if}>
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
{/strip}
