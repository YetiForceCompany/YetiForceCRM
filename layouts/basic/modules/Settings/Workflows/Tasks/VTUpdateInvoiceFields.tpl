{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<div class="tpl-Settings-Workflows-Tasks-VTUpdateInvoiceFields js-conditions-container" id="save_fieldvaluemapping"
	 data-js="container">
	<div class="row js-add-basic-field-container js-conditions-row w-100 mb-2" data-js="clone | container">
		<div class="col-md-3 align-self-md-center"><strong>{\App\Language::translate('LBL_SUM_VALUE_INVOICE_FIELD',$QUALIFIED_MODULE)}</strong></div>
		<div class="col-md-4">
			<select name="source_fieldname" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}"
					class="select2 form-control">
				<option></option>
				{foreach from=$MODULE_MODEL->getFieldsByType(['currency', 'boolean', 'double']) item=FIELD_MODEL}
					{$FIELD_MODEL->getName()} - {$FIELD_MODEL->getFieldDataType()}
					{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
					{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
					<option value="{$FIELD_MODEL->getName()}" data-fieldtype="{$FIELD_MODEL->getFieldType()}"
							data-field-name="{$FIELD_MODEL->getName()}"
							data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}"
							{if $TASK_OBJECT->source_fieldname === $FIELD_MODEL->getName()}selected{/if}
					>
						{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')}
							({\App\Language::translate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))})  {\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_MODEL->get('name'))}
						{else}
							{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
						{/if}
					</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="row js-add-basic-field-container js-conditions-row w-100" data-js="clone | container">
		<div class="col-md-3 align-self-md-center"><strong>{\App\Language::translate('LBL_SET_FIELD_VALUES',$QUALIFIED_MODULE)}</strong></div>
		<div class="col-md-4">
			<select name="target_fieldname" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}"
					class="select2 form-control">
				<option></option>
				{foreach item=REFERENCE_FIELD from=$MODULE_MODEL->getFieldsByReference()}
					{foreach from=$REFERENCE_FIELD->getReferenceList() item=RELATION_MODULE_NAME}
						<optgroup
								label="{\App\Language::translate($RELATION_MODULE_NAME, $RELATION_MODULE_NAME)} - {\App\Language::translate('LBL_RELATIONSHIPS_BASED_ON_FIELDS')}">
							{assign var=RELATION_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATION_MODULE_NAME)}
							{foreach from=$RELATION_MODULE_MODEL->getFieldsByType(['currency', 'boolean', 'double'])  item=FIELD_MODEL}
								##{$FIELD_MODEL->getName()} - {$FIELD_MODEL->getFieldDataType()}##
								{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
								{assign var=VALUE value=$REFERENCE_FIELD->getName()|cat:'::'|cat:$RELATION_MODULE_NAME|cat:'::'|cat:$FIELD_MODEL->getName()}
								<option value="{$VALUE}" data-fieldtype="{$FIELD_MODEL->getFieldType()}"
										data-field-name="{$FIELD_MODEL->getName()}"
										data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}"
								{if $TASK_OBJECT->target_fieldname === $VALUE}selected{/if}
								>
									{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATION_MODULE_NAME)}
								</option>
							{/foreach}
						</optgroup>
					{/foreach}
				{/foreach}
			</select>
		</div>
	</div>
</div>
{/strip}
