{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{include file=\App\Layout::getTemplatePath('DetailViewBlockLink.tpl', $MODULE_NAME) TYPE_VIEW='DetailTop'}
	{include file=\App\Layout::getTemplatePath('Detail/BlocksView.tpl', $MODULE_NAME) RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
	{if $MODULE_TYPE === 1}
		<div class="detailViewTable">
			<div class="js-toggle-panel c-panel">
				<div class="blockHeader c-panel__header d-flex justify-content-between">
					<div class="d-inline-flex align-items-center">
						<h5>
							<span class="menuIcon yfm-FInvoice" aria-hidden="true"></span> {\App\Language::translate('LBL_BEFORE_CORRECTION','FCorectingInvoice')}
						</h5>
						<div class="m-2">
							<span class="u-cursor-pointer js-block-toggle fas fa-angle-right d-none" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"></span>
							<span class="u-cursor-pointer js-block-toggle fas fa-angle-down" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show"></span>
						</div>
					</div>
					<div class="d-inline-flex js-stop-propagation">
						<div class="js-popover-tooltip m-2" data-js="popover" data-trigger="hover focus" data-content="{\App\Language::translate("LBL_INVOICE_INFO",'FCorectingInvoice')}">
							<span class="fas fa-info-circle"></span>
						</div>
					</div>
				</div>
				<div class="c-panel__body blockContent p-2 js-before-inventory" data-js="container">
					{include file=\App\Layout::getTemplatePath('Detail/InventoryView.tpl', $MODULE_NAME) MODULE_NAME='FInvoice' RECORD=FInvoice_Record_Model::getInstanceById($RECORD->get('finvoiceid'))}
				</div>
			</div>
		</div>
		<div class="detailViewTable">
			<div class="js-toggle-panel c-panel">
				<div class="blockHeader c-panel__header d-flex justify-content-between">
					<div class="d-inline-flex align-items-center">
						<h5>
							<span class="menuIcon yfm-FCorectingInvoice" aria-hidden="true"></span> {\App\Language::translate('LBL_AFTER_CORRECTION','FCorectingInvoice')}
						</h5>
						<div class="m-2">
							<span class="u-cursor-pointer js-block-toggle fas fa-angle-right d-none" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"></span>
							<span class="u-cursor-pointer js-block-toggle fas fa-angle-down" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show"></span>
						</div>
					</div>
					<div class="d-inline-flex js-stop-propagation">
						<div class="js-popover-tooltip m-2" data-js="popover" data-trigger="hover focus" data-content="{\App\Language::translate("LBL_AFTER_INVOICE_INFO",'FCorectingInvoice')}">
							<span class="fas fa-info-circle"></span>
						</div>
					</div>
				</div>
				<div class="c-panel__body blockContent p-2 js-after-inventory" data-js="container">
					{include file=\App\Layout::getTemplatePath('Detail/InventoryView.tpl', $MODULE_NAME) MODULE_NAME=$MODULE_NAME}
				</div>
			</div>
		</div>
	{/if}
	{include file=\App\Layout::getTemplatePath('DetailViewBlockLink.tpl', $MODULE_NAME) TYPE_VIEW='DetailBottom'}
{/strip}
