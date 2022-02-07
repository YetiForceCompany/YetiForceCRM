{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Kanban-Index -->
	<input id="js-module-name" type="hidden" value="{$SELECTED_MODULE_NAME}" data-js="value" />
	<div class="o-breadcrumb widget_header row align-items-lg-center">
		<div class="col-md-6">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
		<div class="col-md-6">
			<div class="btn-toolbar justify-content-end form-row">
				<button class="btn btn-primary float-right mr-2 js-add-board" type="button">
					<span class="fas fa-plus mr-2"></span>
					{\App\Language::translate('LBL_ADD_BOARD', $QUALIFIED_MODULE)}
				</button>
				<div class="btn-group col-5 float-right px-1">
					<select class="select2 form-control js-module-list" data-js="change">
						{foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
							<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	</div>
	{if !\App\YetiForce\Register::isRegistered()}
		<div class="col-md-12">
			<div class="alert alert-danger">
				<span class="yfi yfi-yeti-register-alert color-red-600 u-fs-5x mr-4 float-left"></span>
				<h1 class="alert-heading">{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_TITLE',$QUALIFIED_MODULE)}</h1>
				{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_DESC', $QUALIFIED_MODULE)}
			</div>
		</div>
	{else}
		{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForceKanban')}
		{if $CHECK_ALERT}
			<div class="alert alert-warning mt-2">
				<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
				{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')} <a class="btn btn-primary btn-sm" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceKanban&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
			</div>
		{/if}
		<div class="js-fields-list" data-js="container">
			{include file=\App\Layout::getTemplatePath('Fields.tpl', $QUALIFIED_MODULE)}
		</div>
	{/if}
	<!-- /tpl-Settings-Kanban-Index -->
{/strip}
