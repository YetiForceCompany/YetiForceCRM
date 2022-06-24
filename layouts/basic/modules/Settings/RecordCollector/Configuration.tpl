{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<!-- tpl-Settings-RecordCollector-Configuration -->
<div id="menuEditorContainer">
	<div class="o-breadcrumb widget_header row mb-2">
		<div class="col-md-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="main_content">
		<form class="js-validation-form">
			<div class="col-12 form-row m-0">
				<div class="col-12 form-row mb-2">
				<div class="js-config-table table-responsive" data-js="container">
					<hr>
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="" scope="col">{\App\Language::translate('LBL_NAME', $MODULENAME)}</th>
								<th class="" scope="col">{\App\Language::translate('LBL_DESCRIPTION', $MODULENAME)}</th>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_ACTIVE', $MODULENAME)}</th>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_ACTIONS', $MODULENAME)}</th>
							</tr>
						</thead>
						<tbody>
							{function UNSET_POPOVER}
								class="js-popover-tooltip text-center" data-content="{\App\Language::translate('LBL_UNSET', $MODULENAME)}" data-placement="top"
							{/function}
							{foreach from=$COLLECTORS item=ITEM}
								<tr>
									<td>
										<span class="{$ITEM['instance']->icon}"> </span> {\App\Language::translate($ITEM['instance']->label, 'Other.RecordCollector')}
									</td>
									<td>
										{\App\Language::translate($ITEM['instance']->description, 'Other.RecordCollector')}
									</td>
									<td class="text-center">
										<input class="js-status-change" name="is_active" value="{$ITEM['name']}" type="checkbox" {if $ITEM['active']} checked {/if}>
									</td>
									<td class="text-center">
										<button class="btn btn-outline-secondary btn-sm js-show-config-modal js-popover-tooltip mr-1" type="button" data-name="{$ITEM['instance']->label}"
											data-content="{\App\Language::translate('LBL_CONFIG', $MODULENAME)}">
											<span class="fas fa-cog"></span>
										</button>
										{* {if false}
											<button class="js-validate btn btn-outline-success btn-sm js-popover-tooltip" data-name="{$ITEM['name']}" type="button" data-content="{\App\Language::translate('LBL_VALIDATE', $MODULENAME)}" data-js="click | data | class: fa-spin">
												<span class="js-validate__icon fas fa-sync fa-xs mr-1"></span>
											</button>
										{/if} *}
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</form>
	</div>
</div>
<!-- /tpl-Settings-RecordCollector-Configuration -->
