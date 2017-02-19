{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="row">
		<div class="col-md-2"><strong>{vtranslate('LBL_SET_FIELD_VALUES',$QUALIFIED_MODULE)}</strong></div>
	</div><br>
	<div>
		<button type="button" class="btn btn-default" id="addFieldBtn">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
	</div><br>
	<div class="row conditionsContainer" id="save_fieldvaluemapping">
		{assign var=FIELD_VALUE_MAPPING value=\App\Json::decode($TASK_OBJECT->field_value_mapping)}
		<input type="hidden" id="fieldValueMapping" name="field_value_mapping" value='{Vtiger_Util_Helper::toSafeHTML($TASK_OBJECT->field_value_mapping)}' />
		{foreach from=$FIELD_VALUE_MAPPING item=FIELD_MAP}
			<div class="row conditionRow padding-bottom1per">
				<span class="col-md-4">
					<select name="fieldname" class="chzn-select" style="min-width: 250px" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
						<option></option>
						{foreach item=RELATION_MODEL from=$MODULE_MODEL->getRelations()}
							{assign var=RELATION_MODULE_NAME value=$RELATION_MODEL->getRelationModuleName()}
							{assign var=RELATION_MODULE_MODEL value=$RELATION_MODEL->getRelationModuleModel()}
							<optgroup label="{vtranslate($RELATION_MODULE_NAME, $RELATION_MODULE_NAME)}">
								{foreach from=$RELATION_MODULE_MODEL->getFields() item=FIELD_MODEL}
									{if !$FIELD_MODEL->isWritable() or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->get('name'),$RESTRICTFIELDS))} 
										{continue}
									{/if}
									{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
									<option value="{$RELATION_MODULE_NAME}::{$FIELD_MODEL->get('name')}" {if $FIELD_MAP['fieldname'] eq $RELATION_MODULE_NAME|cat:'::'|cat:$FIELD_MODEL->get('name')}selected=""{/if}data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->get('name')}" data-fieldinfo="{\App\Json::encode($FIELD_INFO)}" >
										{vtranslate($FIELD_MODEL->get('label'), $RELATION_MODULE_NAME)}
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
				<p class="cursorPointer form-control-static">
					<i class="alignMiddle deleteCondition glyphicon glyphicon-trash"></i>
				</p>
			</div>
		{/foreach}
		{include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
	</div><br>
	<div class="row basicAddFieldContainer hide padding-bottom1per">
		<span class="col-md-4">
			<select name="fieldname" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}" class="form-control">
				<option></option>
				{foreach item=RELATION_MODEL from=$MODULE_MODEL->getRelations()}
					{assign var=RELATION_MODULE_NAME value=$RELATION_MODEL->getRelationModuleName()}
					{assign var=RELATION_MODULE_MODEL value=$RELATION_MODEL->getRelationModuleModel()}
					<optgroup label="{vtranslate($RELATION_MODULE_NAME, $RELATION_MODULE_NAME)}">
						{foreach from=$RELATION_MODULE_MODEL->getFields() item=FIELD_MODEL}
							{if !$FIELD_MODEL->isWritable() or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->get('name'),$RESTRICTFIELDS))} 
								{continue}
							{/if}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							<option value="{$RELATION_MODULE_NAME}::{$FIELD_MODEL->get('name')}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->get('name')}" data-fieldinfo="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_INFO))}" >
								{vtranslate($FIELD_MODEL->get('label'), $RELATION_MODULE_NAME)}
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
		<p class="cursorPointer form-control-static">
			<span class="alignMiddle deleteCondition glyphicon glyphicon-trash"></span>
		</p>
	</div>
{/strip}
