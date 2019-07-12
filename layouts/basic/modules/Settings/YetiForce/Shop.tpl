{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-Shop">
		<div class="o-breadcrumb widget_header mb-2 d-flex flex-nowrap flex-md-wrap justify-content-between px-2 row">
			<div class="o-breadcrumb__container">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="container mt-3">
			{if $STATUS}
				<div class="col-md-12">
					{if 'success'===$STATUS}
						<div class="alert alert-success">
							{\App\Language::translate('LBL_SUCCESSFUL_TRANSACTION', $QUALIFIED_MODULE)}
						</div>
					{else}
						<div class="alert alert-danger">
							{\App\Language::translate('LBL_FAILED_TRANSACTION', $QUALIFIED_MODULE)}
						</div>
					{/if}
				</div>
			{/if}
			<div class="row">
				<div class="col-md-12">
				{foreach $PRODUCTS as $PRODUCT}
					{include file=\App\Layout::getTemplatePath('Shop/Product.tpl', $QUALIFIED_MODULE)}
				{/foreach}
				</div>
			</div>
		</div>
	</div>
{/strip}
