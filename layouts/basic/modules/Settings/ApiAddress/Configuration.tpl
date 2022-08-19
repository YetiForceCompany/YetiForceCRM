{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<!-- tpl-Settings-ApiAddress-Configuration -->
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
					<div class="col-sm-6 col-md-4">
						<div>
							{\App\Language::translate('LBL_MIN_LOOKUP_LENGTH', $MODULENAME)}:
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div class="text-center">
							<input name="min_length" type="number" min="0" class="api form-control m-0" value="{$CONFIG['global']['min_length']}"
								data-validation-engine="validate[required,min[0],max[100],funcCall[Vtiger_Integer_Validator_Js.invokeValidation]]">
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="col-12 form-row  mb-2">
					<div class='col-sm-6 col-md-4'>
						<div>
							{\App\Language::translate('LBL_NUMBER_SEARCH_RESULTS', $MODULENAME)}:
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div class="text-center">
							<input name="result_num" type="number" min="0" class="api form-control m-0" value="{$CONFIG['global']['result_num']}"
								data-validation-engine="validate[required,min[1],max[100],funcCall[Vtiger_Integer_Validator_Js.invokeValidation]]">
						</div>
					</div>
				</div>
				<div class="js-config-table table-responsive" data-js="container">
					<hr>
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="" scope="col">{\App\Language::translate('LBL_PROVIDER_NAME', $MODULENAME)}</th>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_ACTIVE', $MODULENAME)}</th>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_DEFAULT', $MODULENAME)}</th>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_ACTIONS', $MODULENAME)}</th>
							</tr>
						</thead>
						<tbody>
							{function UNSET_POPOVER}
								class="js-popover-tooltip text-center" data-content="{\App\Language::translate('LBL_PROVIDER_UNSET', $MODULENAME)}" data-placement="top"
							{/function}
							{foreach from=\App\Map\Address::getAllProviders() item=ITEM key=KEY}
								{assign var=CONFIGURED value=$ITEM->isConfigured()}
								<tr>
									<th scope="row">
										{\App\Language::translate('LBL_PROVIDER_'|cat:$KEY|upper, $MODULENAME)}
										{if $KEY === 'YetiForceGeocoder'}
											<span class="btn js-popover-tooltip" data-content="{\App\Language::translate('LBL_PAID_FUNCTIONALITY', 'Settings::YetiForce')}">
												<span class="yfi-premium color-red-600"></span>
											</span>
										{else}
											<span class="btn js-popover-tooltip" data-content="{\App\Language::translate('LBL_PROVIDER_NOT_VERIFIED', 'Settings::Map')}">
												<span class="fas fa-triangle-exclamation color-red-600"></span>
											</span>
										{/if}
									</th>
									<td {if !$CONFIGURED}{UNSET_POPOVER}{else}class="text-center" {/if}>
										<input name="active" data-type="{$KEY}" type="checkbox" {if !empty($ITEM->config['active'])} checked{/if}{if !$CONFIGURED} disabled{/if}>
									</td>
									<td {if !$CONFIGURED}{UNSET_POPOVER}{else}class="text-center" {/if}>
										<input name="default_provider" value="{$KEY}" type="radio" {if $DEFAULT_PROVIDER eq $KEY && $CONFIGURED} checked{/if}{if !$CONFIGURED} disabled{/if}>
									</td>
									<td class="text-center">
										<button class="btn btn-outline-secondary btn-sm js-show-config-modal js-popover-tooltip mr-1" type="button" data-provider="{$KEY}"
											data-content="{\App\Language::translate('LBL_PROVIDER_CONFIG', $MODULENAME)}">
											<span class="fas fa-cog"></span>
										</button>
										<a href="{$ITEM->getDocUrl()}" class="btn btn-outline-primary btn-sm js-popover-tooltip" role="button" target="_blank"
											data-content="{\App\Language::translate('LBL_PROVIDER_INFO_'|cat:$KEY|upper, $MODULENAME)}">
											<span class="fas fa-link"></span>
										</a>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
					<div class="w-100 d-flex justify-content-end mb-2">
						<button type="button" class="btn btn-success saveGlobal"><span class="fa fa-check mr-2"></span>{\App\Language::translate('LBL_SAVE_GLOBAL_SETTINGS', $MODULENAME)}</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<!-- /tpl-Settings-ApiAddress-Configuration -->
