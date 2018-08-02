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
    {assign var=CURRENCY_ID value=$RECORD_MODEL->getId()}
    <div class="tpl-Settings-Currency-TransformEdit currencyTransformModalContainer modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<span class="fas fa-exchange-alt u-mr-5px mt-2"></span>	<h5 class="modal-title">{\App\Language::translate('LBL_TRANSFER_CURRENCY', $QUALIFIED_MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form id="transformCurrency" class="form-horizontal" method="POST">
					<input type="hidden" name="record" value="{$CURRENCY_ID}" />
					<div class="modal-body">
						<div class="form-group row">
							<label class="muted col-form-label col-md-5">{\App\Language::translate('LBL_CURRENT_CURRENCY', $QUALIFIED_MODULE)}</label>
							<div class="controls col-md-7 form-control-plaintext">
								<span>{\App\Language::translate($RECORD_MODEL->get('currency_name'), $QUALIFIED_MODULE)}</span>
							</div>	
						</div>
						<div class="form-group row">
							<label class="muted col-form-label col-md-5">{\App\Language::translate('LBL_TRANSFER_CURRENCY', $QUALIFIED_MODULE)}&nbsp;{\App\Language::translate('LBL_TO', $QUALIFIED_MODULE)}</label>
							<div class="controls col-md-7">
								<select class="select2 form-control" name="transform_to_id">
									{foreach key=CURRENCY_ID item=CURRENCY_MODEL from=$CURRENCY_LIST}
										<option value="{$CURRENCY_ID}">{\App\Language::translate($CURRENCY_MODEL->get('currency_name'), $QUALIFIED_MODULE)}</option>
									{/foreach}
								</select>
							</div>	
						</div>
					</div>
					{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', 'Vtiger') BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
    </div>
{/strip}
