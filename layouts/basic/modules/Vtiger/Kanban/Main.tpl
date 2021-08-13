{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Kanban-Main -->
<div class="o-breadcrumb widget_header row mb-1">
	<div class="col-md-8">
		{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
	</div>
</div>
<div class="row border-bottom js-kanban-header" js-data="container">
	<input type="hidden" id="orderBy" name="orderBy" class="js-params" value="{\App\Purifier::encodeHtml(\App\Json::encode([]))}" data-js="change|value">
	<div class="col-auto">
		{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS=buttonTextHolder}
	</div>
	<div class="col-auto">
		<button type="button" class="btn btn-light modCT_{$MODULE_NAME} js-quick-create-modal js-popover-tooltip" data-module="{$MODULE_NAME}">
			<span class="fas fa-plus-square mr-2"></span>
			{\App\Language::translate('LBL_ADD_RECORD')}
		</button>
	</div>
	<div class="js-hide-filter col-auto">
		<ul class="nav nav-tabs justify-content-center">
			{foreach item=BOARD from=$BOARDS}
				{assign var=BOARDS_FIELD_MODEL value=\Vtiger_Field_Model::getInstanceFromFieldId($BOARD['fieldid'])}
				{assign var=ICON value=$BOARDS_FIELD_MODEL->getIcon()}
				<li class="nav-item">
					<a role="button" class="flCT_{$MODULE_NAME}_{$BOARDS_FIELD_MODEL->getFieldName()} px-4 js-board-tab nav-link{if $BOARD['fieldid'] == $ACTIVE_BOARD['fieldid']} active{/if}" data-id="{$BOARD['fieldid']}">
						{if $ICON}
							<span class="{$ICON} mr-2"></span>
						{/if}
						{$BOARDS_FIELD_MODEL->getFullLabelTranslation()}
					</a>
				</li>
			{/foreach}
		</ul>
	</div>
	<div class="js-hide-filter col-2 ml-auto">
		{if $CUSTOM_VIEWS|@count gt 0}
			<select name="viewName" class="form-control select2 js-custom-filter js-params" title="{\App\Language::translate('LBL_CUSTOM_FILTER')}" data-js="select2|change|value">
				{foreach item="CUSTOM_VIEW" from=$CUSTOM_VIEWS}
					<option value="{$CUSTOM_VIEW->get('cvid')}" data-id="{$CUSTOM_VIEW->get('cvid')}" {if $VIEWID neq '' && $VIEWID neq '0'  && $VIEWID == $CUSTOM_VIEW->getId()} selected="selected"{elseif ($VIEWID == '' or $VIEWID == '0')&& $CUSTOM_VIEW->isDefault() eq 'true'}selected="selected"{/if} class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}">
						{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $MODULE_NAME)}
					</option>
				{/foreach}
			</select>
			<span class="fas fa-filter filterImage mr-2" style="display:none;"></span>
		{else}
			<input type="hidden" value="0" id="customFilter"/>
		{/if}
	</div>
	<div class="js-hide-filter col-auto">
		{if $MODULE_MODEL->isAdvSortEnabled()}
			<button type="button" class="ml-2 btn btn-info js-show-modal js-popover-tooltip" data-content="{\App\Language::translate('LBL_SORTING_SETTINGS')}" data-url="index.php?view=SortOrderModal&module={$MODULE_NAME}" data-modalid="sortOrderModal-{\App\Layout::getUniqueId()}">
				<span class="fas fa-sort"></span>
			</button>
			<div class="js-list-reload" data-js="click"></div>
		{/if}
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
		<div class="alert alert-warning mt-4">
			<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
			{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')} <a class="btn btn-primary btn-sm" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceKanban&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
		</div>
	{/if}
	<div class="js-kanban-container pb-2 c-kanban__container" js-data="container">
		{include file=\App\Layout::getTemplatePath('Kanban/Kanban.tpl', $MODULE_NAME)}
	</div>
{/if}
<!-- tpl-Base-Kanban-Main -->
{/strip}
