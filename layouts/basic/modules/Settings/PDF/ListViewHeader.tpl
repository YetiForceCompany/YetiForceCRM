{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-PDF-ListViewHeader listViewPageDiv" id="listViewContainer">
		<div class="listViewTopMenuDiv">
			<div class="row widget_header mb-2">
				<div class="col-12">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
			</div>
			{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForcePdfPremium')}
			{if $CHECK_ALERT}
				<div class="alert alert-warning">
					<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
					{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')} <a class="btn btn-primary btn-sm" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForcePdfPremium&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
				</div>
			{/if}
			<div class="form-row">
				<div class="col-md-4 btn-toolbar mb-2 mb-xl-0  d-flex justify-content-center justify-content-md-start">
					<button class="btn btn-light addButton" id="addButton"
						data-url="{Settings_PDF_Module_Model::getCreateRecordUrl()}">
						<span class="fas fa-plus mr-1"></span>
						<strong>{\App\Language::translate('LBL_NEW', $QUALIFIED_MODULE)} {\App\Language::translate('LBL_PDF_TEMPLATE',$QUALIFIED_MODULE)}</strong>
					</button>
					<button class="btn btn-light importButton" id="importButton"
						data-url="{Settings_PDF_Module_Model::getImportViewUrl()}"
						title="{\App\Language::translate('LBL_IMPORT_TEMPLATE', $QUALIFIED_MODULE)}">
						<span class="fas fa-download"></span>
					</button>
				</div>
				<div class="col-md-8 col-lg-8 col-xl-3 btn-toolbar mb-2 mb-xl-0">
					<select class="select2" id="moduleFilter">
						<option value="">{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}</option>
						{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
							<option {if !empty($SOURCE_MODULE) && $SOURCE_MODULE eq $MODULE_MODEL->getName()} selected="" {/if}
								value="{$MODULE_MODEL->getName()}">
								{\App\Language::translate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
							</option>
						{/foreach}
					</select>
				</div>
				<div class="col-lg-12 col-xl-5 btn-toolbar pl-0 d-flex justify-content-center justify-content-xl-end">
					{include file=\App\Layout::getTemplatePath('ListViewActions.tpl')}
				</div>
			</div>
		</div>
		<div class="listViewContentDiv" id="listViewContents">
{/strip}
