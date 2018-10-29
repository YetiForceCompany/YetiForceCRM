{strip}
	<div class="tpl-Base-ConditionBuilderRow c-condition-builder__row d-flex pt-2 form-group-sm d-flex js-condition-builder-conditions-row" data-js="container">
		{if !$SELECTED_FIELD_MODEL && $CONDITIONS_ROW}
			{assign var=SELECTED_FIELD_MODEL value=Vtiger_Field_Model::getInstanceFromFilter($CONDITIONS_ROW['fieldname'])}
			{assign var=OPERATORS value=$SELECTED_FIELD_MODEL->getOperators()}
		{/if}
		{if !$SELECTED_OPERATOR && $CONDITIONS_ROW}
			{assign var=SELECTED_OPERATOR value=$CONDITIONS_ROW['operator']}

		{/if}
		{if !$FIELD_INFO && $CONDITIONS_ROW}
			{assign var=FIELD_INFO value=$CONDITIONS_ROW['fieldname']}
		{/if}
		<div class="col-4">
			{include file=\App\Layout::getTemplatePath('ConditionBuilderField.tpl', $MODULE_NAME)}
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
		<div class="col-4 input-group input-group-sm">
			{assign var=TEMPLATE_NAME value=$SELECTED_FIELD_MODEL->getOperatorTemplateName($SELECTED_OPERATOR)}
			{if !empty($TEMPLATE_NAME)}
				{include file=\App\Layout::getTemplatePath($TEMPLATE_NAME, $SOURCE_MODULE)
			FIELD_MODEL=$SELECTED_FIELD_MODEL VALUE=\App\Purifier::decodeHtml($CONDITIONS_ROW['value'])}
			{/if}
		</div>
		<div class="col-1">
			<button type="button" class="btn btn-sm btn-danger js-condition-delete" data-js="click">
				<span class="fa fa-trash"></span>
			</button>
		</div>
	</div>
{/strip}