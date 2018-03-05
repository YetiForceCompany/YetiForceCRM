{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="row">
		<div class="col-md-2"><strong>{\App\Language::translate('LBL_SET_FIELD_VALUES',$QUALIFIED_MODULE)}</strong></div>
	</div><br />
	<div>
		<button type="button" class="btn btn-light" id="addFieldBtn">{\App\Language::translate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
	</div><br />
	<div class="row conditionsContainer" id="save_fieldvaluemapping">
		{assign var=FIELD_VALUE_MAPPING value=\App\Json::decode($TASK_OBJECT->field_value_mapping)}
		<input type="hidden" id="fieldValueMapping" name="field_value_mapping" value='{\App\Purifier::encodeHtml($TASK_OBJECT->field_value_mapping)}' />
		{foreach from=$FIELD_VALUE_MAPPING item=FIELD_MAP}
			<div class="row conditionRow padding-bottom1per">
				<span class="col-md-4">
					<select name="fieldname" class="chzn-select" style="min-width: 250px" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
						<option></option>
						{foreach item=REFERENCE_FIELD from=$MODULE_MODEL->getFieldsByReference()}
							{foreach from=$REFERENCE_FIELD->getReferenceList() item=RELATION_MODULE_NAME}
								<optgroup label="{\App\Language::translate($RELATION_MODULE_NAME, $RELATION_MODULE_NAME)} - {\App\Language::translate('LBL_RELATIONSHIPS_BASED_ON_FIELDS')}">
									{assign var=RELATION_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATION_MODULE_NAME)}
									{foreach from=$RELATION_MODULE_MODEL->getFields() item=FIELD_MODEL}
										{if !$FIELD_MODEL->isEditable() or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->getName(),$RESTRICTFIELDS))} 
											{continue}
										{/if}
										{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
										{assign var=VALUE value=$REFERENCE_FIELD->get('name')|cat:'::'|cat:$RELATION_MODULE_NAME|cat:'::'|cat:$FIELD_MODEL->getName()}
										<option value="{$VALUE}" {if $FIELD_MAP['fieldname'] eq $VALUE} selected=""{/if} data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->getName()}" data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}" >
											{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATION_MODULE_NAME)}
										</option>
									{/foreach}
								</optgroup>
							{/foreach}
						{/foreach}
						{foreach item=RELATION_MODEL from=$MODULE_MODEL->getRelations()}
							{assign var=RELATION_MODULE_NAME value=$RELATION_MODEL->getRelationModuleName()}
							{assign var=RELATION_MODULE_MODEL value=$RELATION_MODEL->getRelationModuleModel()}
							<optgroup label="{\App\Language::translate($RELATION_MODULE_NAME, $RELATION_MODULE_NAME)} - {\App\Language::translate('LBL_RELATIONSHIPS_BASED_ON_MODULES')}">
								{foreach from=$RELATION_MODULE_MODEL->getFields() item=FIELD_MODEL}
									{if !$FIELD_MODEL->isEditable() or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->getName(),$RESTRICTFIELDS))} 
										{continue}
									{/if}
									{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
									<option value="{$RELATION_MODULE_NAME}::{$FIELD_MODEL->getName()}" {if $FIELD_MAP['fieldname'] eq $RELATION_MODULE_NAME|cat:'::'|cat:$FIELD_MODEL->getName()}selected=""{/if}data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->getName()}" data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}" >
										{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATION_MODULE_NAME)}
									</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</span>
				<span class="fieldUiHolder col-md-4 marginLeftZero">
					<input type="text" class="getPopupUi form-control" readonly="" name="fieldValue" value="{$FIELD_MAP['value']}" />
					<input type="hidden" name="valuetype" value="{$FIELD_MAP['valuetype']}" />
				</span>
				<p class="cursorPointer form-control-plaintext">
					<i class="alignMiddle deleteCondition fas fa-trash-alt"></i>
				</p>
			</div>
		{/foreach}
		{include file=\App\Layout::getTemplatePath('FieldExpressions.tpl', $QUALIFIED_MODULE)}
	</div><br />
	<div class="row basicAddFieldContainer d-none padding-bottom1per">
		<span class="col-md-4">
			<select name="fieldname" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}" class="form-control">
				<option></option>
				{foreach item=REFERENCE_FIELD from=$MODULE_MODEL->getFieldsByReference()}
					{foreach from=$REFERENCE_FIELD->getReferenceList() item=RELATION_MODULE_NAME}
						<optgroup label="{\App\Language::translate($RELATION_MODULE_NAME, $RELATION_MODULE_NAME)} - {\App\Language::translate('LBL_RELATIONSHIPS_BASED_ON_FIELDS')}">
							{assign var=RELATION_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATION_MODULE_NAME)}
							{foreach from=$RELATION_MODULE_MODEL->getFields() item=FIELD_MODEL}
								{if !$FIELD_MODEL->isEditable() or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->getName(),$RESTRICTFIELDS))} 
									{continue}
								{/if}
								{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
								{assign var=VALUE value=$REFERENCE_FIELD->get('name')|cat:'::'|cat:$RELATION_MODULE_NAME|cat:'::'|cat:$FIELD_MODEL->getName()}
								<option value="{$VALUE}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->getName()}" data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}" >
									{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATION_MODULE_NAME)}
								</option>
							{/foreach}
						</optgroup>
					{/foreach}
				{/foreach}
				{foreach item=RELATION_MODEL from=$MODULE_MODEL->getRelations()}
					{assign var=RELATION_MODULE_NAME value=$RELATION_MODEL->getRelationModuleName()}
					{assign var=RELATION_MODULE_MODEL value=$RELATION_MODEL->getRelationModuleModel()}
					<optgroup label="{\App\Language::translate($RELATION_MODULE_NAME, $RELATION_MODULE_NAME)} - {\App\Language::translate('LBL_RELATIONSHIPS_BASED_ON_MODULES')}">
						{foreach from=$RELATION_MODULE_MODEL->getFields() item=FIELD_MODEL}
							{if !$FIELD_MODEL->isEditable() or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->getName(),$RESTRICTFIELDS))} 
								{continue}
							{/if}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							<option value="{$RELATION_MODULE_NAME}::{$FIELD_MODEL->getName()}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->getName()}" data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}" >
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATION_MODULE_NAME)}
							</option>
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		</span>
		<span class="fieldUiHolder col-md-4 marginLeftZero">
			<input type="text" class="form-control" readonly="" name="fieldValue" value="" />
			<input type="hidden" name="valuetype" class="form-control" value="rawtext" />
		</span>
		<p class="cursorPointer form-control-plaintext">
			<span class="alignMiddle deleteCondition fas fa-trash-alt"></span>
		</p>
	</div>
{/strip}
