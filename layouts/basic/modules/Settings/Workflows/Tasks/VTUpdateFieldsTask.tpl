{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Workflows-Tasks-VTUpdateFieldsTask -->
	<div class="d-flex px-1 px-md-2">
		<strong class="align-self-center mr-2">{\App\Language::translate('LBL_SET_FIELD_VALUES',$QUALIFIED_MODULE)}</strong>
		<button type="button" class="btn btn-outline-dark"
			id="addFieldBtn">{\App\Language::translate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
	</div>
	<br />
	<div class="row js-conditions-container no-gutters px-1" id="save_fieldvaluemapping" data-js="container">
		{if !empty($TASK_OBJECT->field_value_mapping)}
			{assign var=FIELD_VALUE_MAPPING value=$TASK_OBJECT->field_value_mapping}
		{else}
			{assign var=FIELD_VALUE_MAPPING value=""}
		{/if}
		{assign var=DECODE_FIELD_VALUE_MAPPING value=\App\Json::decode($FIELD_VALUE_MAPPING)}
		<input type="hidden" id="fieldValueMapping" name="field_value_mapping"
			value="{\App\Purifier::encodeHtml($FIELD_VALUE_MAPPING)}" />
		{foreach from=$DECODE_FIELD_VALUE_MAPPING item=FIELD_MAP}
			<div class="row no-gutters col-12 col-xl-6 js-conditions-row padding-bottom1per px-md-1"
				data-js="container | clone">
				<div class="col-md-5 mb-1 mb-md-0">
					<select name="fieldname" class="select2" data-select="allowClear">
						<optgroup class="p-0">
							<option value="">{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}</option>
						</optgroup>
						{foreach from=$MODULE_MODEL->getFields() item=FIELD_MODEL}
							{if !$FIELD_MODEL->isEditable() ||  ($MODULE_MODEL->getName()=="Documents" && in_array($FIELD_MODEL->getName(),$RESTRICTFIELDS)) || in_array($FIELD_MODEL->getFieldDataType(), ['multiCurrency', 'multiDependField', 'multiDomain', 'multiEmail', 'multiImage', 'multiReferenceValue', 'image'])}
								{continue}
							{/if}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							{if in_array($FIELD_MODEL->getFieldDataType(), ['categoryMultipicklist', 'tree'])}
								{$FIELD_INFO['treetemplate'] = App\Purifier::decodeHtml($FIELD_MODEL->getFieldParams())}
							{/if}
							{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
							<option value="{$FIELD_MODEL->getName()}"
								{if $FIELD_MAP['fieldname'] eq $FIELD_MODEL->getName()}selected=""
								{/if}data-fieldtype="{$FIELD_MODEL->getFieldType()}"
								data-field-name="{$FIELD_MODEL->getName()}"
								data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}">
								{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')}
									({\App\Language::translate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))}) - {\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_MODEL->get('name'))} ({\App\Language::translate($FIELD_MODEL->getBlockName(), $MODULE_MODEL->get('name'))})
								{else}
									{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
								{/if}
							</option>
						{/foreach}
					</select>
				</div>
				<div class="fieldUiHolder col-10 col-md-5 px-md-2">
					<input type="text" class="getPopupUi form-control" readonly="" name="fieldValue"
						value="{$FIELD_MAP['value']}" />
					<input type="hidden" name="valuetype" value="{$FIELD_MAP['valuetype']}" />
				</div>
				<div class="col-2">
					<button class="btn btn-danger js-condition-delete float-right float-xl-left" type="button" data-js="click">
						<span class="fas fa-trash-alt"></span>
					</button>
				</div>
			</div>
		{/foreach}
		{include file=\App\Layout::getTemplatePath('FieldExpressions.tpl', $QUALIFIED_MODULE)}
	</div>
	<br />
	<div class="row no-gutters col-12 col-xl-6 js-add-basic-field-container d-none padding-bottom1per px-md-2">
		<div class="col-md-5 mb-1 mb-md-0">
			<select name="fieldname" class="form-control" data-select="allowClear">
				<optgroup class="p-0">
					<option value="">{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}</option>
				</optgroup>
				{foreach from=$MODULE_MODEL->getFields() item=FIELD_MODEL}
					{if !$FIELD_MODEL->isEditable()  || ($MODULE_MODEL->getName()=="Documents" && in_array($FIELD_MODEL->getName(),$RESTRICTFIELDS)) || in_array($FIELD_MODEL->getFieldDataType(), ['multiCurrency', 'multiDependField', 'multiDomain', 'multiEmail', 'multiImage', 'multiReferenceValue', 'image'])}
						{continue}
					{/if}
					{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
					{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
					<option value="{$FIELD_MODEL->getName()}" data-fieldtype="{$FIELD_MODEL->getFieldType()}"
						data-field-name="{$FIELD_MODEL->getName()}"
						data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}">
						{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')}
							({\App\Language::translate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))}) - {\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_MODEL->get('name'))} ({\App\Language::translate($FIELD_MODEL->getBlockName(), $MODULE_MODEL->get('name'))})
						{else}
							{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
						{/if}
					</option>
				{/foreach}
			</select>
		</div>
		<div class="fieldUiHolder col-10 col-md-5 px-md-2">
			<input type="text" class="form-control" readonly="" name="fieldValue" value="" />
			<input type="hidden" name="valuetype" class="form-control" value="rawtext" />
		</div>
		<div class="col-2">
			<button class="btn btn-danger js-condition-delete float-right float-xl-left" type="button" data-js="click">
				<span class="fas fa-trash-alt"></span>
			</button>
		</div>
	</div>
	<!-- /tpl-Settings-Workflows-Tasks-VTUpdateFieldsTask -->
{/strip}
