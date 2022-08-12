{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Workflows-Tasks-VTUpdateRelatedFieldTask -->
	<div class="d-flex px-1 px-md-2">
		<strong class="align-self-center mr-2">{\App\Language::translate('LBL_SET_FIELD_VALUES',$QUALIFIED_MODULE)}</strong>
		<button type="button" class="btn btn-outline-dark"
			id="addFieldBtn">{\App\Language::translate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
	</div>
	<br />
	<div class="row js-conditions-container no-gutters px-1" id="save_fieldvaluemapping" data-js="container">
		<input type="hidden" id="fieldValueMapping" name="field_value_mapping" value="{if isset($TASK_OBJECT->field_value_mapping)}{\App\Purifier::encodeHtml($TASK_OBJECT->field_value_mapping)}{/if}" />
		<input type="hidden" name="conditions" class="js-condition-value" value="{if isset($TASK_OBJECT->conditions)}{\App\Purifier::encodeHtml($TASK_OBJECT->conditions)}{/if}" />
		{if isset($TASK_OBJECT->field_value_mapping)}
			{foreach from=\App\Json::decode($TASK_OBJECT->field_value_mapping) item=FIELD_MAP}
				<div class="row no-gutters col-12 col-xl-6 js-conditions-row padding-bottom1per px-md-1"
					data-js="container | clone">
					<div class="col-md-5 mb-1 mb-md-0">
						<select name="fieldname" class="select2 form-control" style="min-width: 250px"
							data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
							<option></option>
							{foreach item=REFERENCE_FIELD from=$MODULE_MODEL->getFieldsByReference()}
								{foreach from=$REFERENCE_FIELD->getReferenceList() item=RELATION_MODULE_NAME}
									<optgroup
										label="{\App\Language::translate($RELATION_MODULE_NAME, $RELATION_MODULE_NAME)} - {\App\Language::translate('LBL_RELATIONSHIPS_BASED_ON_FIELDS')}">
										{assign var=RELATION_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATION_MODULE_NAME)}
										{foreach from=$RELATION_MODULE_MODEL->getFields() item=FIELD_MODEL}
											{if !$FIELD_MODEL->isEditable() || $FIELD_MODEL->isReferenceField() || ($RELATION_MODULE_MODEL->getName()=="Documents" && in_array($FIELD_MODEL->getName(),$RESTRICTFIELDS)) || in_array($FIELD_MODEL->getFieldDataType(), ['multiCurrency', 'multiDependField', 'multiDomain', 'multiEmail', 'multiImage', 'multiReferenceValue', 'image'])}
												{continue}
											{/if}
											{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
											{if in_array($FIELD_MODEL->getFieldDataType(), ['categoryMultipicklist', 'tree'])}
												{$FIELD_INFO['treetemplate'] = App\Purifier::decodeHtml($FIELD_MODEL->getFieldParams())}
											{/if}
											{assign var=VALUE value=$REFERENCE_FIELD->get('name')|cat:'::'|cat:$RELATION_MODULE_NAME|cat:'::'|cat:$FIELD_MODEL->getName()}
											<option value="{$VALUE}" {if $FIELD_MAP['fieldname'] eq $VALUE} selected="" {/if}
												data-fieldtype="{$FIELD_MODEL->getFieldType()}"
												data-field-name="{$FIELD_MODEL->getName()}"
												data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}">
												{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATION_MODULE_NAME)}
											</option>
										{/foreach}
									</optgroup>
								{/foreach}
							{/foreach}
							{foreach item=RELATION_MODEL from=Vtiger_Relation_Model::getAllRelations($MODULE_MODEL, false)}
								{assign var=RELATION_MODULE_NAME value=$RELATION_MODEL->getRelationModuleName()}
								{assign var=RELATION_MODULE_MODEL value=$RELATION_MODEL->getRelationModuleModel()}
								<optgroup
									label="{\App\Language::translate($RELATION_MODULE_NAME, $RELATION_MODULE_NAME)} - {\App\Language::translate('LBL_RELATIONSHIPS_BASED_ON_MODULES')}">
									{foreach from=$RELATION_MODULE_MODEL->getFields() item=FIELD_MODEL}
										{if !$FIELD_MODEL->isEditable() || $FIELD_MODEL->isReferenceField() || ($RELATION_MODULE_MODEL->getName()=="Documents" && in_array($FIELD_MODEL->getName(),$RESTRICTFIELDS)) || in_array($FIELD_MODEL->getFieldDataType(), ['multiCurrency', 'multiDependField', 'multiDomain', 'multiEmail', 'multiImage', 'multiReferenceValue', 'image'])}
											{continue}
										{/if}
										{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
										{if in_array($FIELD_MODEL->getFieldDataType(), ['categoryMultipicklist', 'tree'])}
											{$FIELD_INFO['treetemplate'] = App\Purifier::decodeHtml($FIELD_MODEL->getFieldParams())}
										{/if}
										{assign var=VALUE value="{$RELATION_MODULE_NAME}::{$FIELD_MODEL->getName()}"}
										<option value="{$VALUE}"
											{if $FIELD_MAP['fieldname'] eq $VALUE} selected="" {/if}
											data-fieldtype="{$FIELD_MODEL->getFieldType()}"
											data-field-name="{$FIELD_MODEL->getName()}"
											data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}">
											{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATION_MODULE_NAME)}
										</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
					</div>
					<div class="fieldUiHolder mb-1 col-12 col-xs-10 col-md-5 col-sm-10 px-md-2 mr-1">
						<input type="text" class="getPopupUi form-control" readonly="" name="fieldValue"
							value="{$FIELD_MAP['value']}" />
						<input type="hidden" name="valuetype" value="{$FIELD_MAP['valuetype']}" />
					</div>
					<div class="mb-1 float-right">
						<button class="btn btn-info mr-1 js-condition-modal float-xl-left" type="button" title="{\App\Language::translate('LBL_CONDITION_WIZARD',$QUALIFIED_MODULE)}" data-js="click">
							<span class="fas fa-filter"></span>
						</button>
						<button class="btn btn-danger js-condition-delete  float-xl-left" type="button" data-js="click">
							<span class="fas fa-trash-alt"></span>
						</button>
					</div>
				</div>
			{/foreach}
		{/if}
		{include file=\App\Layout::getTemplatePath('FieldExpressions.tpl', $QUALIFIED_MODULE)}
	</div>
	<br />
	<div class="row no-gutters col-12 col-xl-6 js-add-basic-field-container d-none padding-bottom1per px-md-2">
		<div class="col-md-5 mb-1 mb-md-0">
			<select name="fieldname" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}"
				class="form-control">
				<option></option>
				{foreach item=REFERENCE_FIELD from=$MODULE_MODEL->getFieldsByReference()}
					{foreach from=$REFERENCE_FIELD->getReferenceList() item=RELATION_MODULE_NAME}
						<optgroup
							label="{\App\Language::translate($RELATION_MODULE_NAME, $RELATION_MODULE_NAME)} - {\App\Language::translate('LBL_RELATIONSHIPS_BASED_ON_FIELDS')}">
							{assign var=RELATION_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATION_MODULE_NAME)}
							{foreach from=$RELATION_MODULE_MODEL->getFields() item=FIELD_MODEL}
								{if !$FIELD_MODEL->isEditable() || $FIELD_MODEL->isReferenceField() || ($RELATION_MODULE_MODEL->getName()=="Documents" && in_array($FIELD_MODEL->getName(),$RESTRICTFIELDS)) || in_array($FIELD_MODEL->getFieldDataType(), ['multiCurrency', 'multiDependField', 'multiDomain', 'multiEmail', 'multiImage', 'multiReferenceValue', 'image'])}
									{continue}
								{/if}
								{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
								{assign var=VALUE value=$REFERENCE_FIELD->get('name')|cat:'::'|cat:$RELATION_MODULE_NAME|cat:'::'|cat:$FIELD_MODEL->getName()}
								<option value="{$VALUE}" data-fieldtype="{$FIELD_MODEL->getFieldType()}"
									data-field-name="{$FIELD_MODEL->getName()}"
									data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}">
									{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATION_MODULE_NAME)}
								</option>
							{/foreach}
						</optgroup>
					{/foreach}
				{/foreach}
				{foreach item=RELATION_MODEL from=Vtiger_Relation_Model::getAllRelations($MODULE_MODEL, false)}
					{assign var=RELATION_MODULE_NAME value=$RELATION_MODEL->getRelationModuleName()}
					{assign var=RELATION_MODULE_MODEL value=$RELATION_MODEL->getRelationModuleModel()}
					<optgroup
						label="{\App\Language::translate($RELATION_MODULE_NAME, $RELATION_MODULE_NAME)} - {\App\Language::translate('LBL_RELATIONSHIPS_BASED_ON_MODULES')}">
						{foreach from=$RELATION_MODULE_MODEL->getFields() item=FIELD_MODEL}
							{if !$FIELD_MODEL->isEditable() || $FIELD_MODEL->isReferenceField() || ($RELATION_MODULE_MODEL->getName()=="Documents" && in_array($FIELD_MODEL->getName(),$RESTRICTFIELDS)) || in_array($FIELD_MODEL->getFieldDataType(), ['multiCurrency', 'multiDependField', 'multiDomain', 'multiEmail', 'multiImage', 'multiReferenceValue', 'image'])}
								{continue}
							{/if}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							<option value="{$RELATION_MODULE_NAME}::{$FIELD_MODEL->getName()}"
								data-fieldtype="{$FIELD_MODEL->getFieldType()}"
								data-field-name="{$FIELD_MODEL->getName()}"
								data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}">
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATION_MODULE_NAME)}
							</option>
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		</div>
		<div class="fieldUiHolder mb-1 col-12 col-xs-10 col-md-5 col-sm-10 px-md-2 mr-1">
			<input type="text" class="form-control" readonly="" name="fieldValue" value="" />
			<input type="hidden" name="valuetype" class="form-control" value="rawtext" />
		</div>
		<div class="mb-1 float-right">
			<button class="btn btn-info mr-1 js-condition-modal float-xl-left" type="button" title="{\App\Language::translate('LBL_CONDITION_WIZARD',$QUALIFIED_MODULE)}" data-js="click">
				<span class="fas fa-filter"></span>
			</button>
			<button class="btn btn-danger js-condition-delete  float-xl-left" type="button" data-js="click">
				<span class="fas fa-trash-alt"></span>
			</button>
		</div>
	</div>
	<!-- /tpl-Settings-Workflows-Tasks-VTUpdateRelatedFieldTask -->
{/strip}
