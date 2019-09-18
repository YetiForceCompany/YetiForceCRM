{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Edit-Field-ActivityType -->
	<div class="text-center">
		{foreach item="ACTIVITYTYPE_VALUE" key="ACTIVITYTYPE_NAME" from=App\Fields\Picklist::getValues('activitytype')}
			{if !empty($ACTIVITYTYPE_VALUE['color'])}
				{append var='ACTIVITYTYPE_COLOR' value=$ACTIVITYTYPE_VALUE['color'] index=$ACTIVITYTYPE_VALUE['activitytype']}
			{/if}
			{if !empty($ACTIVITYTYPE_VALUE['icon'])}
				{append var='ACTIVITYTYPE_ICON' value=$ACTIVITYTYPE_VALUE['icon'] index=$ACTIVITYTYPE_VALUE['activitytype']}
			{/if}
		{/foreach}
		<div class="btn-group-toggle" data-toggle="buttons">
			{foreach item="PICKLIST_VALUE" key="PICKLIST_NAME" from=$FIELD_MODEL->getPicklistValues()}
				<label class="btn u-border-bottom-5px c-btn-outline-done mr-1 mb-1{if !empty($ACTIVITYTYPE_COLOR[$PICKLIST_NAME])} picklistCBr_Calendar_activitytype_{$PICKLIST_NAME}{/if}">
					<input type="radio" name="{$FIELD_MODEL->getFieldName()}"
						   id="option_{$PICKLIST_NAME}"
						   value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}"
						   title="{\App\Purifier::encodeHtml($PICKLIST_VALUE)}"
						   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
						   data-fieldinfo='{\App\Json::encode($FIELD_MODEL->getFieldInfo())|escape}'
							{if trim($FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)) eq trim($PICKLIST_NAME)} checked {/if}
							{if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
					{if !empty($ACTIVITYTYPE_ICON[$PICKLIST_NAME])}
						<i class="{$ACTIVITYTYPE_ICON[$PICKLIST_NAME]} align-middle"
						   title="{\App\Purifier::encodeHtml($PICKLIST_VALUE)}"></i>
					{else}
						<span>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</span>
					{/if}
				</label>
			{/foreach}
		</div>
	</div>
	<!-- /tpl-Calendar-Edit-Field-ActivityType -->
{/strip}
