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
<div class="">
	<form id="EditView" method="POST">
		<div class="widget_header row">
			<div class="col-xs-12">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			</div>
			<div class="col-xs-12">
				<span>{vtranslate('LBL_CUSTOMIZE_MODENT_NUMBER_DESCRIPTION', $QUALIFIED_MODULE)}</span>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<table id="customRecordNumbering" class="table table-bordered">
				{assign var=DEFAULT_MODULE_NAME value=$DEFAULT_MODULE_MODEL->getName()}
				{assign var=DEFAULT_MODULE_DATA value=\App\Fields\RecordNumber::getNumber($DEFAULT_MODULE_NAME)}
				{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
					<thead>
						<tr>
							<th width="30%" class="{$WIDTHTYPE}">
								<strong>{vtranslate('LBL_CUSTOMIZE_RECORD_NUMBERING', $QUALIFIED_MODULE)}</strong>
							</th>
							<th width="70%" class="{$WIDTHTYPE}" style="border-left: none">
							<span class="pull-right">
								<button type="button" class="btn btn-info" name="updateRecordWithSequenceNumber"><b>{vtranslate('LBL_UPDATE_MISSING_RECORD_SEQUENCE', $QUALIFIED_MODULE)}</b></button>
							</span>
							</th>
						</tr>
					</thead>

					<tbody>
					<tr>
						<td class="{$WIDTHTYPE}">
							<label class="pull-right marginRight10px"><b>{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}</b></label>
						</td>
						<td class="fieldValue {$WIDTHTYPE}" style="border-left: none">
							<select class="chzn-select form-control" name="sourceModule">
								{foreach key=index item=MODULE_MODEL from=$SUPPORTED_MODULES}
									{assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
									<option value={$MODULE_NAME} {if $MODULE_NAME eq $DEFAULT_MODULE_NAME} selected {/if}>
										{vtranslate($MODULE_NAME, $MODULE_NAME)}
									</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td class="{$WIDTHTYPE}">
							<label class="pull-right marginRight10px"><b>{vtranslate('LBL_USE_PREFIX', $QUALIFIED_MODULE)}</b></label>
						</td>
						<td class="fieldValue {$WIDTHTYPE}" style="border-left: none">
							<input type="text" class="form-control" value="{$DEFAULT_MODULE_DATA['prefix']}" placeholder="{vtranslate('LBL_NO_PREFIX', $QUALIFIED_MODULE)}" data-old-prefix="{$DEFAULT_MODULE_DATA['prefix']}" name="prefix" data-validation-engine="validate[funcCall[Vtiger_AlphaNumericWithSlashesCurlyBraces_Validator_Js.invokeValidation]]"/>
						</td>
					</tr>
					<tr>
						<td class="{$WIDTHTYPE}">
							<label class="pull-right marginRight10px">
								<b>{vtranslate('LBL_START_SEQUENCE', $QUALIFIED_MODULE)}</b><span class="redColor">*</span>
							</label>
						</td>
						<td class="fieldValue {$WIDTHTYPE}" style="border-left: none">
							<input type="text" class="form-control" value="{$DEFAULT_MODULE_DATA['sequenceNumber']}"
								   data-old-sequence-number="{$DEFAULT_MODULE_DATA['sequenceNumber']}" name="sequenceNumber"
								   data-validation-engine="validate[required,funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]"/>
						</td>
					</tr>
					<tr>
						<td class="{$WIDTHTYPE}">
							<label class="pull-right marginRight10px"><b>{vtranslate('LBL_USE_POSTFIX', $QUALIFIED_MODULE)}</b></label>
						</td>
						<td class="fieldValue {$WIDTHTYPE}" style="border-left: none">
							<input type="text" class="form-control" value="{$DEFAULT_MODULE_DATA['postfix']}" placeholder="{vtranslate('LBL_NO_POSTFIX', $QUALIFIED_MODULE)}" data-old-postfix="{$DEFAULT_MODULE_DATA['postfix']}" name="postfix" data-validation-engine="validate[funcCall[Vtiger_AlphaNumericWithSlashesCurlyBraces_Validator_Js.invokeValidation]]"/>
						</td>
					</tr>
				</tbody>
				</table>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<table id="customRecordNumbering" class="table table-bordered">
					<thead>
						<tr>
							<th width="30%" class="{$WIDTHTYPE}">
								<strong>{vtranslate('LBL_USE_CUSTOME_VARIABLES', $QUALIFIED_MODULE)}</strong>
							</th>
							<th width="70%" class="{$WIDTHTYPE}" style="border-left: none"></th>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td class="{$WIDTHTYPE}">
							<label class="pull-right marginRight10px"><b>{vtranslate('LBL_CUSTOME_VARIABLES', $QUALIFIED_MODULE)}</b></label>
						</td>
						<td class="fieldValue {$WIDTHTYPE}" style="border-left: none">
							<div class="row">
								<div class="col-md-11">
									<select class="chzn-select form-control" id="customVariables" name="custom_variables">
										<option value="YYYY">{vtranslate('LBL_CV_FULL_YEAR', $QUALIFIED_MODULE)}</option>
										<option value="YY">{vtranslate('LBL_CV_YEAR', $QUALIFIED_MODULE)}</option>
										<option value="MM">{vtranslate('LBL_CV_FULL_MONTH', $QUALIFIED_MODULE)}</option>
										<option value="M">{vtranslate('LBL_CV_MONTH', $QUALIFIED_MODULE)}</option>
										<option value="DD">{vtranslate('LBL_CV_FULL_DAY', $QUALIFIED_MODULE)}</option>
										<option value="D">{vtranslate('LBL_CV_DAY', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
								<div class="col-md-1">
									<input type="hidden" value="" id="customVariable" />
									<button class="btn btn-sm btn-info pull-right" id="customVariableCopy" title="{vtranslate('LBL_COPY_CV', $QUALIFIED_MODULE)}">
										<span class="glyphicon glyphicon-copy"></span>
									</button>
								</div>
							</div>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12 pull-right">
				<div class="pull-right">
					<button class="btn btn-success saveButton" type="submit" disabled="disabled"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
					<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
				</div>
			</div>
		</div>
	</form>
</div>
{/strip}
