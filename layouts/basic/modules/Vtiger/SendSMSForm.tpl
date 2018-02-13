{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<div id="sendSmsContainer" class='modelContainer modal fade' tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button data-dismiss="modal" class="close" title="{\App\Language::translate('LBL_CLOSE')}">&times;</button>
					<h3 class="modal-title">{\App\Language::translate('LBL_SEND_SMS_TO_SELECTED_NUMBERS', $MODULE)}</h3>
				</div>
				<form class="form-horizontal validateForm" id="massSave" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
					<input type="hidden" name="action" value="MassSaveAjax" />
					<input type="hidden" name="viewname" value="{$VIEWNAME}" />
					<input type="hidden" name="selected_ids" value='{\App\Json::encode($SELECTED_IDS)}'>
					<input type="hidden" name="excluded_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($EXCLUDED_IDS))}">
					<input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
					<input type="hidden" name="operator" value="{$OPERATOR}" />
					<input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
					<input type="hidden" name="search_params" value='{\App\Json::encode($SEARCH_PARAMS)}' />
					<div class="modal-body">
						<div class="alert alert-info" role="alert">
							<span class="fas fa-info-circle"></span>&nbsp;&nbsp;
							{\App\Language::translate('LBL_MASS_SEND_SMS_INFO', $MODULE)}
						</div>
						<div class="col-12">
							<div class="form-group">
								<span><strong>{\App\Language::translate('LBL_STEP_1',$MODULE)}</strong></span>
								&nbsp;:&nbsp;
								{\App\Language::translate('LBL_SELECT_THE_PHONE_NUMBER_FIELDS_TO_SEND',$MODULE)}
								<select name="fields[]" data-placeholder="{\App\Language::translate('LBL_ADD_MORE_FIELDS',$MODULE)}" multiple class="select2 form-control" data-validation-engine="validate[ required]">
									<optgroup>
										{foreach item=PHONE_FIELD from=$PHONE_FIELDS}
											{if $PHONE_FIELD->isEditable() eq false} {continue} {/if}
											{assign var=PHONE_FIELD_NAME value=$PHONE_FIELD->get('name')}
											<option value="{$PHONE_FIELD_NAME}">
												{if !empty($SINGLE_RECORD)}
													{assign var=FIELD_VALUE value=$SINGLE_RECORD->getDisplayValue($PHONE_FIELD_NAME)}
												{/if}
												{\App\Language::translate($PHONE_FIELD->get('label'), $SOURCE_MODULE)}{if !empty($FIELD_VALUE)} ({$FIELD_VALUE}){/if}
											</option>
										{/foreach}
									</optgroup>
								</select>
							</div>
							<div class="form-group">
								<span><strong>{\App\Language::translate('LBL_STEP_2',$MODULE)}</strong></span>
								&nbsp;:&nbsp;
								{\App\Language::translate('LBL_TYPE_THE_MESSAGE',$MODULE)}&nbsp;(&nbsp;{\App\Language::translate('LBL_SMS_MAX_CHARACTERS_ALLOWED',$MODULE)}&nbsp;)
								<textarea class="input-xxlarge form-control" name="message" id="message" placeholder="{\App\Language::translate('LBL_WRITE_YOUR_MESSAGE_HERE', $MODULE)}" data-validation-engine="validate[ required]"></textarea>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-success" type="submit" name="saveButton"><span class="fas fa-check"></span>&nbsp;<strong>{\App\Language::translate('LBL_SEND', $MODULE)}</strong></button>
						<button class="btn btn-warning" type="reset" data-dismiss="modal"><span class="fas fa-times"></span>&nbsp;<strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong></button>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}
