{strip}
	{*<!--
	/*********************************************************************************
	** The contents of this file are subject to the vtiger CRM Public License Version 1.0
	* ("License"); You may not use this file except in compliance with the License
	* The Original Code is:  vtiger CRM Open Source
	* The Initial Developer of the Original Code is vtiger.
	* Portions created by vtiger are Copyright (C) vtiger.
	* All Rights Reserved.
	*
	********************************************************************************/
	-->*}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	{include file=\App\Layout::getTemplatePath('DetailViewBlockLink.tpl', $MODULE_NAME) TYPE_VIEW='DetailTop'}
	{include file=\App\Layout::getTemplatePath('DetailViewBlockView.tpl', $MODULE_NAME) RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
	{if $MODULE_TYPE == '1'}
		<div class="detailViewTable">
			<div class="js-toggle-panel c-panel">
				<div class="blockHeader c-panel__header">
					<span class="u-cursor-pointer js-block-toggle fas fa-angle-right m-2" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"></span>
					<span class="u-cursor-pointer js-block-toggle fas fa-angle-down m-2" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show"></span>
					<h5><span class="menuIcon userIcon-FInvoice" aria-hidden="true"></span> {\App\Language::translate('LBL_BEFORE_CORRECTION','FCorectingInvoice')}</h5>
				</div>
				<div class="c-panel__body blockContent p-3" id="beforeInventory" data-js="container">
					{include file=\App\Layout::getTemplatePath('DetailViewInventoryView.tpl', $MODULE_NAME) MODULE_NAME='FInvoice' RECORD=FInvoice_Record_Model::getInstanceById($RECORD->get('finvoiceid'))}
				</div>
			</div>
		</div>
		<div class="detailViewTable">
			<div class="js-toggle-panel c-panel">
				<div class="blockHeader c-panel__header">
					<span class="u-cursor-pointer js-block-toggle fas fa-angle-right m-2" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"></span>
					<span class="u-cursor-pointer js-block-toggle fas fa-angle-down m-2" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show"></span>
					<h5><span class="menuIcon userIcon-FCorectingInvoice" aria-hidden="true"></span> {\App\Language::translate('LBL_AFTER_CORRECTION','FCorectingInvoice')}</h5>
				</div>
				<div class="c-panel__body blockContent p-3" id="afterInventory" data-js="container">
					{include file=\App\Layout::getTemplatePath('DetailViewInventoryView.tpl', $MODULE_NAME) MODULE_NAME=$MODULE_NAME}
				</div>
			</div>
		</div>
	{/if}
	{include file=\App\Layout::getTemplatePath('DetailViewBlockLink.tpl', $MODULE_NAME) TYPE_VIEW='DetailBottom'}
{/strip}
