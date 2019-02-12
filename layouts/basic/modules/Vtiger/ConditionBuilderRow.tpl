{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-ConditionBuilderRow c-condition-builder__row d-flex pt-2 form-group-sm js-condition-builder-conditions-row"
		 data-js="container">
		{if empty($SELECTED_FIELD_MODEL) && !empty($CONDITIONS_ROW)}
			{assign var=SELECTED_FIELD_MODEL value=Vtiger_Field_Model::getInstanceFromFilter($CONDITIONS_ROW['fieldname'])}
			{assign var=OPERATORS value=$SELECTED_FIELD_MODEL->getOperators()}
		{/if}
		{if empty($SELECTED_OPERATOR) && !empty($CONDITIONS_ROW)}
			{assign var=SELECTED_OPERATOR value=$CONDITIONS_ROW['operator']}

		{/if}
		{if empty($FIELD_INFO) && !empty($CONDITIONS_ROW)}
			{assign var=FIELD_INFO value=$CONDITIONS_ROW['fieldname']}
		{/if}
		<div class="col-4">
			<select class="select2 form-control js-conditions-fields" data-js="change">
				{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
					<optgroup label="{\App\Language::translate($BLOCK_LABEL, $SOURCE_MODULE)}">
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
							<option value="{$FIELD_MODEL->getCustomViewSelectColumnName()}" {if $FIELD_INFO eq $FIELD_MODEL->getCustomViewSelectColumnName()} selected="selected"{/if}>
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
							</option>
						{/foreach}
					</optgroup>
				{/foreach}
				{foreach key=MODULE_KEY item=RECORD_STRUCTURE_FIELD from=$RECORD_STRUCTURE_RELATED_MODULES}
					{foreach key=RELATED_FIELD_NAME item=RECORD_STRUCTURE from=$RECORD_STRUCTURE_FIELD}
						{assign var=RELATED_FIELD_LABEL value=Vtiger_Field_Model::getInstance($RELATED_FIELD_NAME, Vtiger_Module_Model::getInstance($SOURCE_MODULE))->getFieldLabel()}
						{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
							<optgroup
									label="{\App\Language::translate($RELATED_FIELD_LABEL, $SOURCE_MODULE)}&nbsp;-&nbsp;{\App\Language::translate($MODULE_KEY, $MODULE_KEY)}&nbsp;-&nbsp;{\App\Language::translate($BLOCK_LABEL, $MODULE_KEY)}">
								{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
									<option value="{$FIELD_MODEL->getCustomViewSelectColumnName($RELATED_FIELD_NAME)}" {if $FIELD_INFO eq $FIELD_MODEL->getCustomViewSelectColumnName($RELATED_FIELD_NAME)} selected="selected"{/if}>
										{\App\Language::translate($RELATED_FIELD_LABEL, $SOURCE_MODULE)}
										&nbsp;-&nbsp;{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_KEY)}
									</option>
								{/foreach}
							</optgroup>
						{/foreach}
					{/foreach}
				{/foreach}
			</select>
		</div>
		<div class="col-3">
			<select class="select2 form-control js-conditions-operator" data-js="change">
				{foreach key=OP item=OPERATOR from=$OPERATORS}
					<option value="{$OP}" {if $SELECTED_OPERATOR eq $OP}selected="selected"{/if}>
						{\App\Language::translate($OPERATOR, $SOURCE_MODULE)}
					</option>
				{/foreach}
			</select>
		</div>
		<div class="col-4">
			{assign var=TEMPLATE_NAME value=$SELECTED_FIELD_MODEL->getOperatorTemplateName($SELECTED_OPERATOR)}
			{if !empty($TEMPLATE_NAME)}
				{if !empty($CONDITIONS_ROW['value'])}
					{assign var=CONDITION_ROW_VALUE value=\App\Purifier::decodeHtml($CONDITIONS_ROW['value'])}
				{else}
					{assign var=CONDITION_ROW_VALUE value=''}
				{/if}
				{include file=\App\Layout::getTemplatePath($TEMPLATE_NAME, $SOURCE_MODULE)
			FIELD_MODEL=$SELECTED_FIELD_MODEL VALUE=$CONDITION_ROW_VALUE}
			{/if}
		</div>
		<div class="col-1 d-flex justify-content-end">
			<button type="button" class="btn btn-sm btn-danger js-condition-delete" data-js="click">
				<span class="fa fa-trash"></span>
			</button>
		</div>
	</div>
{/strip}