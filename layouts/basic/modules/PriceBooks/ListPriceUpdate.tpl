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
{strip}
	<div class="tpl-PriceBooks-ListPriceUpdate" id="listPriceUpdateContainer">
		<div class="modal-header">
			<h5 class="modal-title">{\App\Language::translate('LBL_EDIT_LIST_PRICE', $MODULE)}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>	
		<form class="form-horizontal" id="listPriceUpdate" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="action" value="RelationAjax" />
			<input type="hidden" name="src_record" value="{$PRICEBOOK_ID}" />
			<input type="hidden" name="relid" value="{$REL_ID}" />
			<div class="modal-body">
				<div>
					<span><strong>{\App\Language::translate('LBL_EDIT_LIST_PRICE',$MODULE)}</strong></span>
					&nbsp;:&nbsp;
					<input type="text" class="form-control" name="currentPrice" value="{$CURRENT_PRICE}" data-validation-engine="validate[required,funcCall[Vtiger_Currency_Validator_Js.invokeValidation]]" 
						   data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' />
				</div>
			</div>
			{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
		</form>	
	</div>
{/strip}	
