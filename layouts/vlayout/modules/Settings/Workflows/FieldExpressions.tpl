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
<div class="popupUi modal hide" data-backdrop="false" style="z-index: 1000006;min-width: 750px;overflow: visible">
	<div class="modal-header contentsBackground">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>{vtranslate('LBL_SET_VALUE',$QUALIFIED_MODULE)}</h3>
	</div>
	<div class="modal-body">
		<div class="row-fluid">
			<span class="span4">
				<select class="textType">
					<optgroup>
						<option data-ui="textarea" value="rawtext">{vtranslate('LBL_RAW_TEXT',$QUALIFIED_MODULE)}</option>
						<option data-ui="textarea" value="fieldname">{vtranslate('LBL_FIELD_NAME',$QUALIFIED_MODULE)}</option>
						<option data-ui="textarea" value="expression">{vtranslate('LBL_EXPRESSION',$QUALIFIED_MODULE)}</option>
					</optgroup>	
				</select>
			</span>
			<span class="span4 hide useFieldContainer">
				<span name="{$MODULE_MODEL->get('name')}" class="useFieldElement">
					{assign var=MODULE_FIELDS value=$MODULE_MODEL->getFields()}
					<select class="useField" data-placeholder="{vtranslate('LBL_USE_FIELD',$QUALIFIED_MODULE)}">
						<option></option>
						<optgroup>
							{foreach from=$MODULE_FIELDS item=MODULE_FIELD}
								<option value="{$MODULE_FIELD->getName()}">{vtranslate($MODULE_FIELD->get('label'),$MODULE_MODEL->get('name'))}</option>
							{/foreach}
						</optgroup>
					</select>
				</span>
				{if $RELATED_MODULE_MODEL neq ''}
					<span name="{$RELATED_MODULE_MODEL->get('name')}" class="useFieldElement">
						{assign var=MODULE_FIELDS value=$RELATED_MODULE_MODEL->getFields()}
						<select class="useField" data-placeholder="{vtranslate('LBL_USE_FIELD',$QUALIFIED_MODULE)}">
							<option></option>
							<optgroup>
								{foreach from=$MODULE_FIELDS item=MODULE_FIELD}
									<option value="{$MODULE_FIELD->getName()}">{vtranslate($MODULE_FIELD->get('label'),$QUALIFIED_MODULE)}</option>
								{/foreach}
							</optgroup>
						</select>
					</span>
				{/if}
			</span>
			<span class="span4 hide useFunctionContainer">
				<select class="useFunction" data-placeholder="{vtranslate('LBL_USE_FUNCTION',$QUALIFIED_MODULE)}">
					<option></option>
					<optgroup>
						{foreach from=$FIELD_EXPRESSIONS key=FIELD_EXPRESSION_VALUE item=FIELD_EXPRESSIONS_KEY}
							<option value="{$FIELD_EXPRESSIONS_KEY}">{vtranslate($FIELD_EXPRESSION_VALUE,$QUALIFIED_MODULE)}</option>
						{/foreach}
					</optgroup>
				</select>
			</span>
		</div><br>
		<div class="row-fluid fieldValueContainer">
			<textarea data-textarea="true" class="fieldValue row-fluid hide"></textarea>
		</div><br>
		<div id="rawtext_help" class="alert alert-info helpmessagebox hide">
			<p><h5>{vtranslate('LBL_RAW_TEXT',$QUALIFIED_MODULE)}</h5></p>
			<p>2000</p>
			<p>{vtranslate('LBL_VTIGER',$QUALIFIED_MODULE)}</p>
		</div>
		<div id="fieldname_help" class="helpmessagebox alert alert-info hide">
			<p><h5>{vtranslate('LBL_EXAMPLE_FIELD_NAME',$QUALIFIED_MODULE)}</h5></p>
			<p>{vtranslate('LBL_ANNUAL_REVENUE',$QUALIFIED_MODULE)}</p>
			<p>{vtranslate('LBL_NOTIFY_OWNER',$QUALIFIED_MODULE)}</p>
		</div>
		<div id="expression_help" class="alert alert-info helpmessagebox hide">
			<p><h5>{vtranslate('LBL_EXAMPLE_EXPRESSION',$QUALIFIED_MODULE)}</h5></p>
			<p>{vtranslate('LBL_ANNUAL_REVENUE',$QUALIFIED_MODULE)}/12</p>
			<p>{vtranslate('LBL_EXPRESSION_EXAMPLE2',$QUALIFIED_MODULE)}</p>
		</div>
	</div>
	<div class="modal-footer">
		<div class=" pull-right cancelLinkContainer">
			<a class="cancelLink closeModal" type="button">{vtranslate('LBL_CANCEL', $MODULE)}</a>
		</div>
		<button class="btn btn-success" type="button" name="saveButton"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
	</div>
</div>
<div class="clonedPopUp"></div>
{/strip}