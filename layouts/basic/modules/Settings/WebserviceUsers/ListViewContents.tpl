{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForceWebservicePremium')}
	{if $TYPE_API === 'WebservicePremium' && $CHECK_ALERT}
		<div class="alert alert-warning mt-2 mb-1">
			<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
			{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')}
			<a class="btn btn-primary btn-sm ml-2" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceWebservicePremium&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
		</div>
	{/if}
	<div class="editViewContainer tab-pane active mt-2" id="{$TYPE_API}" data-type="{$TYPE_API}">
		<div class="listViewActionsDiv row">
			<div class="col-md-8 tn-toolbar">
				{foreach item=LINK from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
					{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='listViewBasic'}
				{/foreach}
			</div>
			<div class="col-md-4 ">
				<div class="float-right">
					{include file=\App\Layout::getTemplatePath('ListViewActions.tpl', $QUALIFIED_MODULE)}
				</div>
			</div>
		</div>
		<div class="listViewContentDiv mt-2 table-responsive" id="listViewContents">
			{include file=\App\Layout::getTemplatePath('ListViewContents.tpl', 'Settings:Vtiger')}
		</div>
	</div>
	</div>
	</div>
{/strip}
