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
	<div class="popupUi modal fade" data-backdrop="false">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{\App\Language::translate('LBL_SET_VALUE',$QUALIFIED_MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<span class="col-md-4">
							<select class="textType form-control">
								<optgroup>
									<option data-ui="textarea" value="rawtext">
										{\App\Language::translate('LBL_RAW_TEXT',$QUALIFIED_MODULE)}
									</option>
									<option data-ui="textarea" value="fieldname">
										{\App\Language::translate('LBL_FIELD_NAME',$QUALIFIED_MODULE)}
									</option>
									<option data-ui="textarea" value="expression">
										{\App\Language::translate('LBL_EXPRESSION',$QUALIFIED_MODULE)}
									</option>
								</optgroup>
							</select>
						</span>
						<span class="col-md-4 d-none useFieldContainer">
							<span name="{$MODULE_MODEL->get('name')}" class="useFieldElement">
								{assign var=MODULE_FIELDS value=$MODULE_MODEL->getFields()}
								<select class="useField form-control"
										data-placeholder="{\App\Language::translate('LBL_USE_FIELD',$QUALIFIED_MODULE)}">
									<option></option>
									<optgroup>
										{foreach from=$MODULE_FIELDS item=MODULE_FIELD}
											<option value="{$MODULE_FIELD->getName()}">{\App\Language::translate($MODULE_FIELD->get('label'),$MODULE_MODEL->getName())}</option>
										{/foreach}
									</optgroup>
								</select>
							</span>
							{if !empty($RELATED_MODULE_MODEL)}
								<span name="{$RELATED_MODULE_MODEL->get('name')}" class="useFieldElement">
									{assign var=MODULE_FIELDS value=$RELATED_MODULE_MODEL->getFields()}
									<select class="useField form-control"
											data-placeholder="{\App\Language::translate('LBL_USE_FIELD',$QUALIFIED_MODULE)}">
										<option></option>
										<optgroup>
											{foreach from=$MODULE_FIELDS item=MODULE_FIELD}
												<option value="{$MODULE_FIELD->getName()}">{\App\Language::translate($MODULE_FIELD->get('label'), $MODULE_FIELD->getModuleName())}</option>
											{/foreach}
										</optgroup>
									</select>
								</span>
							{/if}
						</span>
						<span class="col-md-4 d-none useFunctionContainer">
							<select class="useFunction form-control"
									data-placeholder="{\App\Language::translate('LBL_USE_FUNCTION',$QUALIFIED_MODULE)}">
								<option></option>
								<optgroup>
									{foreach from=$FIELD_EXPRESSIONS key=FIELD_EXPRESSION_VALUE item=FIELD_EXPRESSIONS_KEY}
										<option value="{$FIELD_EXPRESSIONS_KEY}">{\App\Language::translate($FIELD_EXPRESSION_VALUE,$QUALIFIED_MODULE)}</option>
									{/foreach}
								</optgroup>
							</select>
						</span>
					</div>
					<br/>
					<div class="fieldValueContainer">
						<textarea data-textarea="true" class="fieldValue form-control"></textarea>
					</div>
					<br/>
					<div id="rawtext_help" class="alert alert-info helpmessagebox d-none">
						<p><h5>{\App\Language::translate('LBL_RAW_TEXT',$QUALIFIED_MODULE)}</h5></p>
						<p>2000</p>
						<p>{\App\Language::translate('LBL_VTIGER',$QUALIFIED_MODULE)}</p>
					</div>
					<div id="fieldname_help" class="helpmessagebox alert alert-info d-none">
						<p><h5>{\App\Language::translate('LBL_EXAMPLE_FIELD_NAME',$QUALIFIED_MODULE)}</h5></p>
						<p>{\App\Language::translate('LBL_ANNUAL_REVENUE',$QUALIFIED_MODULE)}</p>
						<p>{\App\Language::translate('LBL_NOTIFY_OWNER',$QUALIFIED_MODULE)}</p>
					</div>
					<div id="expression_help" class="alert alert-info helpmessagebox d-none">
						<p><h5>{\App\Language::translate('LBL_EXAMPLE_EXPRESSION',$QUALIFIED_MODULE)}</h5></p>
						<p>{\App\Language::translate('LBL_ANNUAL_REVENUE',$QUALIFIED_MODULE)}/12</p>
						<p>{\App\Language::translate('LBL_EXPRESSION_EXAMPLE2',$QUALIFIED_MODULE)}</p>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success mr-1" type="button" name="saveButton">
						<strong>
							<span class="fa fa-check mr-1"></span>{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}
						</strong>
					</button>
					<button class="btn btn-danger cancelLink" type="button" data-close-modal="modal">
						<strong>
							<span class="fa fa-times mr-1"></span>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}
						</strong>
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="clonedPopUp"></div>
{/strip}
