{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="row">
		<div class="col-md-2"><strong>{\App\Language::translate('LBL_SET_FIELD_VALUES',$QUALIFIED_MODULE)}</strong></div>
	</div><br />
	<div>
		<button type="button" class="btn btn-outline-secondary" id="addFieldBtn">{\App\Language::translate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
	</div><br />
	<div class="row conditionsContainer" id="save_fieldvaluemapping">
		{assign var=FIELD_VALUE_MAPPING value=\App\Json::decode($TASK_OBJECT->field_value_mapping)}
		<input type="hidden" id="fieldValueMapping" name="field_value_mapping" value='{\App\Purifier::encodeHtml($TASK_OBJECT->field_value_mapping)}' />
		{foreach from=$FIELD_VALUE_MAPPING item=FIELD_MAP}
			<div class="row conditionRow padding-bottom1per">
				<span class="col-md-4">
					<select name="fieldname" class="chzn-select" style="min-width: 250px" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
						<option></option>
						{foreach from=$MODULE_MODEL->getFields() item=FIELD_MODEL}
                            {if !$FIELD_MODEL->isEditable() or $FIELD_MODEL->getFieldDataType() eq 'reference' or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->getName(),$RESTRICTFIELDS))} 
                                {continue}
                            {/if}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
							<option value="{$FIELD_MODEL->getName()}" {if $FIELD_MAP['fieldname'] eq $FIELD_MODEL->getName()}selected=""{/if}data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->getName()}" data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}" >
								{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')}
									({\App\Language::translate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))})  {\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_MODEL->get('name'))}
								{else}
									{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
								{/if}
							</option>
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
	<div class="row basicAddFieldContainer d-none padding-bottom1per w-100">
		<span class="col-md-4">
			<select name="fieldname" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}" class="form-control">
				<option></option>
				{foreach from=$MODULE_MODEL->getFields() item=FIELD_MODEL}
					{if !$FIELD_MODEL->isEditable() or $FIELD_MODEL->getFieldDataType() eq 'reference' or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->getName(),$RESTRICTFIELDS))}
						{continue}
					{/if}
					{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
					{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
					<option value="{$FIELD_MODEL->getName()}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->getName()}" data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_INFO))}" >
						{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')}
							({\App\Language::translate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))})  {\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_MODEL->get('name'))}
						{else}
							{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
						{/if}
					</option>
				{/foreach}
			</select>
		</span>
		<span class="fieldUiHolder col-md-4 marginLeftZero">
			<input type="text" class="form-control" readonly="" name="fieldValue" value="" />
			<input type="hidden" name="valuetype" class="form-control" value="rawtext" />
		</span>
		<p class="cursorPointer form-control-plaintext w-auto">
			<span class="alignMiddle deleteCondition fas fa-trash-alt"></span>
		</p>
	</div>
{/strip}
