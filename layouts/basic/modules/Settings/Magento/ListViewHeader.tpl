{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-Magento-ListViewHeader -->
<div>
	<div class="o-breadcrumb widget_header row">
		<div class="col-12 d-flex">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
		</div>
	</div>
	{if !\App\YetiForce\Shop::check('YetiForceMagento')}
		<div class="alert alert-warning">
			<span class="yfi-premium mr-2 u-font-size-2x color-red-600 float-left"></span>
			{\App\Language::translate('LBL_PAID_FUNCTIONALITY', $QUALIFIED_MODULE)} <a href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceMagento&mode=showProductModal">{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
		</div>
	{elseif !$MODULE_MODEL->isActive()}
		<div class="alert alert-danger">
			<span class="mdi mdi-alert-outline mr-2 float-left"></span>
			{\App\Language::translateArgs('LBL_FUNCTIONALITY_HAS_NOT_YET_BEEN_ACTIVATED', $QUALIFIED_MODULE,'Magento')}
			<a class="btn btn-primary btn-sm ml-3" href="index.php?parent=Settings&module=Magento&action=Active">
				<span class="mdi mdi-check mr-2 float-left"></span>
				{\App\Language::translate('LBL_ACTIVATE_FUNCTIONALITY', $QUALIFIED_MODULE)}
			</a>
		</div>
	{else}
		<div class="listViewActionsDiv row mt-2 mb-2">
			<div class="{if !empty($SUPPORTED_MODULE_MODELS)}col-md-5{else}col-md-8{/if} btn-toolbar">
				{foreach item=LINK from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
					{if $LINK->getLabel()}
						{assign var="LABEL" value=\App\Language::translate($LINK->getLabel(), $QUALIFIED_MODULE)}
					{/if}
					<button type="button" title="{if $LINK->getLabel()}{$LABEL}{/if}"
							class="btn{if $LINK->getClassName()} {$LINK->getClassName()}{else} btn-light{/if} {if $LINK->get('modalView')}showModal{/if}"
							{if $LINK->getUrl()}
								{if stripos($LINK->getUrl(), 'javascript:')===0} onclick='{$LINK->getUrl()|substr:strlen("javascript:")};'
								{else} onclick='window.location.href = "{$LINK->getUrl()}"' {/if}
							{/if}
							{if $LINK->get('linkdata') neq '' && is_array($LINK->get('linkdata'))}
						{foreach from=$LINK->get('linkdata') key=NAME item=DATA}
							data-{$NAME}="{$DATA}"
						{/foreach}
							{/if}>
						{if $LINK->get('linkicon')}
							<span class="{$LINK->get('linkicon')}"></span>
						{/if}
						{if $LINK->getLabel() && $LINK->get('showLabel') eq 1}
							&nbsp;
							<strong>{$LABEL}</strong>
						{/if}
					</button>
				{/foreach}
			</div>
			{if !empty($SUPPORTED_MODULE_MODELS)}
				<div class="col-md-3 btn-toolbar marginLeftZero">
					<select class="select2 form-control" id="moduleFilter"
							data-placeholder="{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}"
							data-select="allowClear">
						<optgroup class="p-0">
							<option value="">{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}</option>
						</optgroup>
						{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
							<option {if !empty($SOURCE_MODULE) && $SOURCE_MODULE eq $MODULE_MODEL->getName()} selected="" {/if}
									value="{$TAB_ID}">
								{App\Language::translate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
							</option>
						{/foreach}
					</select>
				</div>
			{/if}
			<div class="col-12 col-sm-4 d-flex flex-row-reverse">
				{include file=\App\Layout::getTemplatePath('ListViewActions.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
	{/if}
	<div class="clearfix"></div>
	<div class="listViewContentDiv listViewPageDiv" id="listViewContents">
<!-- /tpl-Settings-Magento-ListViewHeader -->
{/strip}
