{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-content validationEngineContainer" id="EditView">
		<form>
			<input class="recordEditView" type="hidden">
			<input type="hidden" name="mappingRelatedField" value="{Vtiger_Util_Helper::toSafeHTML($MAPPING_RELATED_FIELD)}"/>
			<div class="modal-header row no-margin">
				<div class="col-xs-12 paddingLRZero">
					<div class="col-xs-8 paddingLRZero">
						{if $RECORD_MODEL}
							<h4>{vtranslate('LBL_TITLE_EDIT', $QUALIFIED_MODULE)}</h4>
						{else}
							<h4>{vtranslate('LBL_TITLE_ADDED', $QUALIFIED_MODULE)}</h4>
						{/if}
					</div>
					<div class="pull-right">
						<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
					</div>
				</div>
			</div>
			<div class="modal-body row">
				<div class="col-xs-12 marginBottom10px">
					<div class="col-xs-4 fieldLabel">
						<span class="redColor">*</span>{vtranslate('LBL_APP_NAME', $QUALIFIED_MODULE)}
					</div>
					<div class="col-xs-8">
						<input type="text" name="name" data-validation-engine="validate[required]" value="{if $RECORD_MODEL}{$RECORD_MODEL->getName()}{/if}" class="form-control">
					</div>
				</div>
				<div class="col-xs-12 marginBottom10px">
					<div class="col-xs-4 fieldLabel">
						{vtranslate('LBL_ADDRESS_URL', $QUALIFIED_MODULE)}
					</div>
					<div class="col-xs-8">
						<input type="text" name="addressUrl" value="{if $RECORD_MODEL}{$RECORD_MODEL->get('acceptable_url')}{/if}" class="form-control">
					</div>
				</div>
				<div class="col-xs-12 marginBottom10px">
					<div class="col-xs-4 fieldLabel">
						<span class="redColor">*</span>{vtranslate('LBL_PASS', $QUALIFIED_MODULE)}
					</div>
					<div class="col-xs-8">
						<input type="text" name="pass" data-validation-engine="validate[required]" value="{if $RECORD_MODEL}{$RECORD_MODEL->get('pass')}{/if}" class="form-control">
					</div>
				</div>
				<div class="col-xs-12 marginBottom10px">
					<div class="col-xs-4 fieldLabel">
						{vtranslate('Status', $QUALIFIED_MODULE)}
					</div>
					<div class="col-xs-8">
						<input type="checkbox" {if $RECORD_MODEL && $RECORD_MODEL->get('status') eq 1}checked{/if} name="status">
					</div>
				</div>
				<div class="col-xs-12 marginBottom10px">
					<div class="col-xs-4 fieldLabel">
						{vtranslate('LBL_TYPE_SERVER', $QUALIFIED_MODULE)}
					</div>
					<div class="col-xs-8">
						<select class="select2 typeServer" {if $RECORD_MODEL} disabled {/if}>
							{foreach from=$TYPES_SERVERS item=TYPE}
								<option value="{$TYPE}"
										{if $RECORD_MODEL && $TYPE eq  $RECORD_MODEL->get('type')}
											selected	
										{/if}
										>
									{$TYPE}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="col-xs-12 marginBottom10px">
					<div class="col-xs-4 fieldLabel">
						{vtranslate('SINGLE_Accounts', $QUALIFIED_MODULE)}
					</div>
					<div class="col-xs-8">
						<div class="fieldValue">
							<input name="popupReferenceModule" type="hidden" 
								   data-multi-reference="0" title="{vtranslate('Accounts', $QUALIFIED_MODULE)}" 
								   value="Accounts">
							<input name="accountsid" type="hidden" value="{if $RECORD_MODEL}{$RECORD_MODEL->get('accounts_id')}{/if}"
								   title="" class="sourceField" data-fieldtype="reference" 
								   data-displayvalue="">
							<div class="input-group referenceGroup">
								<input id="accountsid_display" name="accountsid_display" type="text" title=""
									   class="marginLeftZero form-control autoComplete ui-autocomplete-input" 
									   value="{if $RECORD_MODEL && $RECORD_MODEL->get('accountsModel')}{$RECORD_MODEL->get('accountsModel')->getName()}{/if}"
									   {if $RECORD_MODEL} readonly {/if}
									   data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
									   placeholder="{vtranslate('LBL_TYPE_SEARCH', $QUALIFIED_MODULE)}" 
									   autocomplete="off">
								<span class="input-group-btn cursorPointer">
									<button class="btn btn-default clearReferenceSelection" type="button">
										<span class="glyphicon glyphicon-remove-sign" 
											  title="{vtranslate('LBL_CLEAR', $QUALIFIED_MODULE)}"></span>
									</button>
									<button class="btn btn-default relatedPopup" type="button">
										<span class="glyphicon glyphicon-search" 
											title="{vtranslate('LBL_SELECT', $QUALIFIED_MODULE)}"></span>
									</button>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
		{include file='ModalFooter.tpl'|@vtemplate_path}
	</div>
{/strip}
