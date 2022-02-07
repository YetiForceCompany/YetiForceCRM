{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-FCorectingInvoice-Edit-Inventory -->
	<div class="detailViewTable">
		<div class="js-toggle-panel c-panel">
			<div class="blockHeader c-panel__header">
				<div class="m-2">
					<span class="u-cursor-pointer js-block-toggle fas fa-angle-right d-none" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"></span>
					<span class="u-cursor-pointer js-block-toggle fas fa-angle-down" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show"></span>
				</div>
				<div class="d-inline-flex ml-auto mr-2 js-stop-propagation">
					<div class="js-popover-tooltip" data-js="popover" data-trigger="hover focus" data-content="{\App\Language::translate("LBL_INVOICE_INFO",$MODULE_NAME)}">
						<span class="fas fa-info-circle"></span>
					</div>
				</div>
				<div class="d-inline-flex align-items-center">
					<h5>
						<span class="menuIcon yfm-FInvoice" aria-hidden="true"></span> {\App\Language::translate('LBL_BEFORE_CORRECTION',$MODULE_NAME)}
					</h5>
				</div>
			</div>
			<div class="c-panel__body blockContent p-2 js-before-inventory" data-js="container">
				{if $RECORD->get('finvcoiceid')}
					{include file=\App\Layout::getTemplatePath('Detail/InventoryView.tpl', $MODULE_NAME)  VIEW='Detail' MODULE_NAME='FInvoice' RECORD=Vtiger_Record_Model::getInstanceById($RECORD->get('finvoiceid'))}
				{else}
					<div class="text-center">{\App\Language::translate('LBL_CHOOSE_INVOICE',$MODULE_NAME)}</div>
				{/if}
			</div>
		</div>
	</div>
	<div class="detailViewTable">
		<div class="js-toggle-panel c-panel">
			<div class="blockHeader c-panel__header">
				<div class="m-2">
					<span class="u-cursor-pointer js-block-toggle fas fa-angle-right d-none" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"></span>
					<span class="u-cursor-pointer js-block-toggle fas fa-angle-down" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show"></span>
				</div>
				<div class="d-inline-flex js-stop-propagation ml-auto align-items-center">
					<a href class="btn btn-primary btn-sm mr-1 js-copy-from-invoice" data-js="click">
						<span class="fas fa-copy"></span> {\App\Language::translate('LBL_COPY_FROM_INVOICE',$MODULE_NAME)}
					</a>
					<div class="js-popover-tooltip mx-2" data-js="popover" data-trigger="hover focus" data-content="{\App\Language::translate("LBL_AFTER_INVOICE_INFO_EDIT",$MODULE_NAME)}">
						<span class="fas fa-info-circle"></span>
					</div>
				</div>
				<div class="d-inline-flex align-items-center">
					<h5>
						<span class="menuIcon yfm-FCorectingInvoice" aria-hidden="true"></span> {\App\Language::translate('LBL_AFTER_CORRECTION',$MODULE_NAME)}
					</h5>
				</div>
			</div>
			<div class="c-panel__body blockContent p-2 js-after-inventory" data-js="container">
				{include file=\App\Layout::getTemplatePath('Edit/Inventory.tpl', 'Vtiger')}
			</div>
		</div>
	</div>
	<!-- /tpl-FCorectingInvoice-Edit-Inventory -->
{/strip}
