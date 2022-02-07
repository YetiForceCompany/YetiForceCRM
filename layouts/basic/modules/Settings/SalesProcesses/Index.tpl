{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="" id="salesProcessesContainer">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<ul id="tabs" class="nav nav-tabs mt-1 layoutTabs massEditTabs" data-tabs="tabs">
			<li class="nav-item"><a class="nav-link active" href="#popup" data-toggle="tab">{\App\Language::translate('LBL_PRODUCTS_AND_SERVICES_POPUP', $QUALIFIED_MODULE)} </a></li>
		</ul>
		{assign var=CONFIG value=$MODULE_MODEL->getConfig()}
		<div class="tab-content layoutContent">
			<div class="tab-pane active" id="popup">
				{assign var=POPUP value=$CONFIG['popup']}
				<div class="btn-group-toggle" data-toggle="buttons">
					<label class="btn {if $POPUP['limit_product_service'] eq 'true'}btn-success active{else}btn-light{/if} btn-block">
						<span class="far {if $POPUP['limit_product_service'] eq 'true'}fa-check-square{else}fa-square{/if} float-left"></span>
						<input id="limit_product_service" autocomplete="off" class="configField" type="checkbox" name="limit_product_service" data-type="popup" {if $POPUP['limit_product_service'] eq 'true'}checked="" {/if}>
						<div class="u-white-space-n">{\App\Language::translate('LBL_LIMIT_PRODUCT_AND_SERVICE', $QUALIFIED_MODULE)}</div>
					</label>
				</div>
			</div>
		</div>
	</div>
{/strip}
