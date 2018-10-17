{strip}
	<div class="tpl-Base-ConditionBuilderRow d-flex js-condition-builder-conditions-row">
		<div class="col-4">
			<select class="select2 form-control js-conditions-fields" data-js="change">
				{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
					<optgroup label="{\App\Language::translate($BLOCK_LABEL, $MODULE_NAME)}">
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
							<option value="{$FIELD_MODEL->getCustomViewSelectColumnName()}" {if $FIELD_INFO eq $FIELD_MODEL->getCustomViewSelectColumnName()} selected="selected"{/if}>
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
							</option>
						{/foreach}
					</optgroup>
				{/foreach}
				{foreach key=MODULE_KEY item=RECORD_STRUCTURE_FIELD from=$RECORD_STRUCTURE_RELATED_MODULES}
					{foreach key=RELATED_FIELD_NAME item=RECORD_STRUCTURE from=$RECORD_STRUCTURE_FIELD}
						{assign var=RELATED_FIELD_LABEL value=Vtiger_Field_Model::getInstance($RELATED_FIELD_NAME, Vtiger_Module_Model::getInstance($MODULE_NAME))->getFieldLabel()}
						{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
							<optgroup label="{\App\Language::translate($RELATED_FIELD_LABEL, $MODULE_NAME)}&nbsp;-&nbsp;{\App\Language::translate($MODULE_KEY, $MODULE_KEY)}&nbsp;-&nbsp;{\App\Language::translate($BLOCK_LABEL, $MODULE_KEY)}">
								{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
									<option value="{$FIELD_MODEL->getCustomViewSelectColumnName($RELATED_FIELD_NAME)}" {if $FIELD_INFO eq $FIELD_MODEL->getCustomViewSelectColumnName($RELATED_FIELD_NAME)} selected="selected"{/if}>
										{\App\Language::translate($RELATED_FIELD_LABEL, $MODULE_NAME)}
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
						{\App\Language::translate($OPERATOR, $MODULE_NAME)}
					</option>
				{/foreach}
			</select>
		</div>
		<div class="col-4">
			{assign var=TEMPLATE_NAME value=$SELECTED_FIELD_MODEL->getOperatorTemplateName($SELECTED_OPERATOR)}
			{if !empty($TEMPLATE_NAME)}
				{include file=\App\Layout::getTemplatePath($TEMPLATE_NAME, $MODULE_NAME)
			FIELD_MODEL=$SELECTED_FIELD_MODEL }
			{/if}
		</div>
		<div class="col-1">
			<button class="btn btn-sm btn-danger js-condition-delete" data-js="click">
				<span class="fa fa-trash"></span>
			</button>
		</div>
	</div>

{/strip}