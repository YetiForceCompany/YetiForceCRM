{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=EDIT_VIEW value=true}
	{assign var=ID value=$RECORD_MODEL->getId()}
	{if empty($ID)}
		{assign var=EDIT_VIEW value=false}
	{/if}
	{if $TYPE != 'CreditLimits'}
		{assign var=PERCENTAGE value=true}
	{/if}
	<div class='modelContainer modal fade' id="addInventory" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button class="close vtButton" data-dismiss="modal" aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">&times;</button>
					{if $EDIT_VIEW}
						<h3 class="modal-title">{\App\Language::translate('LBL_EDITING', $QUALIFIED_MODULE)} {\App\Language::translate($PAGE_LABELS.title_single, $QUALIFIED_MODULE)}</h3>
					{else}
						<h3 class="modal-title">{\App\Language::translate('LBL_ADD', $QUALIFIED_MODULE)} {\App\Language::translate($PAGE_LABELS.title_single, $QUALIFIED_MODULE)}</h3>
					{/if}
				</div>
				<form id="formInventory" class="form-horizontal" method="POST">
					<input type="hidden" name="id" value="{$ID}" />
					<div class="modal-body">
						<div class="">
							<div class="form-group">
								<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)}</label>
								<div class="col-md-6 controls">
									<input class="form-control" type="text" name="name" placeholder="{\App\Language::translate('LBL_ENTER_NAME', $QUALIFIED_MODULE)}" value="{$RECORD_MODEL->getName()}" data-validation-engine='validate[required]' />
								</div>	
							</div>
							{if !$PERCENTAGE}
								{assign var=VALIDATOR value='Vtiger_Integer_Validator_Js.invokeValidation'}
							{else}
								{assign var=VALIDATOR value='Vtiger_Percentage_Validator_Js.invokeValidation'}
							{/if}	
							<div class="form-group">
								<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_VALUE', $QUALIFIED_MODULE)}</label>
								<div class="col-md-6 controls">
									<div class="input-group">
										<input class="form-control" type="text" name="value" placeholder="{\App\Language::translate('LBL_ENTER_VALUE', $QUALIFIED_MODULE)}" value="{$RECORD_MODEL->getValue()}" data-validation-engine='validate[required, funcCall[{$VALIDATOR}]]' />
										<span class="input-group-addon">{if $PERCENTAGE}%{else}{$CURRENCY.currency_symbol}{/if}</span>
									</div>
								</div>
							</div>
							{if $EDIT_VIEW}
								<div class="form-group">
									<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_STATUS', $QUALIFIED_MODULE)}</label>
									<div class="col-md-6 controls checkboxForm">
										<input type="hidden" name="status" value="1" />
										<input type="checkbox" name="status" value="0" class="status alignBottom" {if !$RECORD_MODEL->getStatus()} checked {/if} />
										<span>&nbsp;&nbsp;{\App\Language::translate('LBL_STATUS_DESC', $QUALIFIED_MODULE)}</span>
									</div>	
								</div>
							{else}
								<input type="hidden" class="addView" value="true" />
								<input type="hidden" name="status" value="0" />
							{/if}
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('ModalFooter.tpl', 'Vtiger')}
				</form>
			</div>
		</div>
	</div>
{/strip}
