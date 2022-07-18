{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<!-- tpl-Settings-RecordCollector-Configuration -->
<div class="o-breadcrumb widget_header row mb-2">
	<div class="col-md-12">
		{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
	</div>
</div>
<div class="main_content">
	<form class="js-validation-form">
		{foreach from=$SHOP_RECORD_COLLECTOR item=SHOP_PRODUCT}
			{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert($SHOP_PRODUCT)}
			{if $CHECK_ALERT}
				<div class="alert alert-warning">
					<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
					{\App\YetiForce\Shop::getProduct($SHOP_PRODUCT)->getLabel()} -
					{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')}
					<a class="btn btn-primary btn-sm" href="index.php?parent=Settings&module=YetiForce&view=Shop&product={$SHOP_PRODUCT}&mode=showProductModal">
						<span class="yfi yfi-shop mr-2"></span>
						{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}
					</a>
				</div>
			{/if}
		{/foreach}
		<div class="form-row m-0">
			<div class="col-12 form-row mb-2">
				<div class="js-config-table table-responsive" data-js="container">

					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="col-3" scope="col">{\App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)}</th>
								<th class="col-4" scope="col">{\App\Language::translate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}</th>
								<th class="col-3" scope="col">{\App\Language::translate('LBL_DOC_URL', $QUALIFIED_MODULE)}</th>
								<th class="col-1 text-center" scope="col">{\App\Language::translate('LBL_ACTIVE', $QUALIFIED_MODULE)}</th>
								<th class="col-1 text-center" scope="col">{\App\Language::translate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$COLLECTORS item=ITEM}
								<tr>
									<td>
										<span class="{$ITEM['instance']->icon} mr-2"></span>
										{\App\Language::translate($ITEM['instance']->label, 'Other.RecordCollector')}
										{if \in_array($ITEM['name'], $PAID_RECORD_COLLECTOR)}<span class="yfi-premium color-red-600 js-popover-tooltip ml-2" title="{\App\Language::translate('LBL_PAID_FUNCTIONALITY', 'Settings::YetiForce')}"></span>{/if}
									</td>
									<td>
										{\App\Language::translate($ITEM['instance']->description, 'Other.RecordCollector')}
									</td>
									<td>
										<a href="{$ITEM['instance']->docUrl}" rel="noreferrer noopener" target="_blank">{$ITEM['instance']->docUrl}</a>
									</td>
									<td class="text-center">
										<input class="js-status-change" name="is_active" value="{$ITEM['name']}" type="checkbox" {if $ITEM['active']} checked {/if}>
									</td>
									<td class="text-center">
										{if !empty($ITEM['instance']->settingsFields)}
											<button class="btn btn-outline-secondary btn-sm js-show-config-modal js-popover-tooltip mr-1 {if !$ITEM['active']} d-none {/if}" type="button" data-name="{$ITEM['name']}"
												data-content="{\App\Language::translate('LBL_CONFIG', $QUALIFIED_MODULE)}">
												<span class="fas fa-cog"></span>
											</button>
										{/if}
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
	</form>
</div>
<!-- /tpl-Settings-RecordCollector-Configuration -->
