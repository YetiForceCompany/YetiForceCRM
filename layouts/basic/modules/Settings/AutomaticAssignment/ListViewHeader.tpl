{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-AutomaticAssignment-ListViewHeader -->
	<div>
		<div class="o-breadcrumb widget_header row">
			<div class="col-12 d-flex">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
		{if !\App\YetiForce\Register::isRegistered()}
			<div class="col-md-12">
				<div class="alert alert-danger">
					<span class="yfi yfi-yeti-register-alert color-red-600 u-fs-5x mr-4 float-left"></span>
					<h1 class="alert-heading">{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_TITLE',$QUALIFIED_MODULE)}</h1>
					{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_DESC',$QUALIFIED_MODULE)}
				</div>
			</div>
		{else}
			{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForceAutoAssignment')}
			{if $CHECK_ALERT}
				<div class="alert alert-warning">
					<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
					{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')} <a class="btn btn-primary btn-sm" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceAutoAssignment&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
				</div>
			{/if}
			<div class="listViewActionsDiv row mt-2 mb-2">
				<div class="{if !empty($SUPPORTED_MODULE_MODELS)}col-md-5{else}col-md-8{/if} btn-toolbar">
					{foreach item=LINK from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
						{if $LINK->getLabel()}
							{assign var="LABEL" value=\App\Language::translate($LINK->getLabel(), $QUALIFIED_MODULE)}
						{/if}
						<button type="button" title="{if $LINK->getLabel()}{$LABEL}{/if}"
							class="btn{if $LINK->getClassName()} {$LINK->getClassName()}{else} btn-light{/if} {if $LINK->get('modalView')}js-show-modal{/if}"
							{if $LINK->getUrl()}
								{if stripos($LINK->getUrl(), 'javascript:')===0} onclick='{$LINK->getUrl()|substr:strlen("javascript:")};'
								{else} onclick='window.location.href = "{$LINK->getUrl()}"'
								{/if}
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
			<!-- /tpl-Settings-AutomaticAssignment-ListViewHeader -->
{/strip}
