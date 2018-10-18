{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=dateFormat value=$USER_MODEL->get('date_format')}
	{assign var=PARAMS value=$FIELD_MODEL->getFieldParams()}
	<div class="tpl-ConditionBuilder-Date input-group date">
		{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
		<input name="{$FIELD_MODEL->getFieldName()}"
			   class="{if !$FIELD_MODEL->isEditableReadOnly()}dateField js-date-field{/if} form-control"
			   data-js="daterangepicker"
			   title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
			   id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text"
			   {if $PARAMS && $PARAMS['onChangeCopyValue']}data-copy-to-field="{$PARAMS['onChangeCopyValue']}"{/if}
			   data-date-format="{$dateFormat}"
			   value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}"
			   data-fieldinfo='{$FIELD_INFO}'
				{if !empty($MODE) && $MODE eq 'edit' && $FIELD_NAME eq 'due_date'} data-user-changed-time="true" {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}
			   autocomplete="off"/>
		<div class=" input-group-append">
			<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
				<span class="fas fa-calendar-alt"></span>
			</span>
		</div>
	</div>
{/strip}
