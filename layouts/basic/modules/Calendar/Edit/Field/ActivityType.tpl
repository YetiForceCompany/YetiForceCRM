{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Edit-Field-ActivityType -->
	<div class="text-center">
		<div class="btn-group-toggle" data-toggle="buttons">
			{foreach item="PICKLIST_VALUE" key="PICKLIST_NAME" from=$FIELD_MODEL->getPicklistValues()}
				{assign var="CHECK" value=trim($FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)) eq trim($PICKLIST_NAME)}
				{assign var=VALUE value=\App\Purifier::encodeHtml($PICKLIST_VALUE)}
				<label class="btn u-border-bottom-5px c-btn-outline-done mr-1 mb-1 picklistCBr_Calendar_activitytype_{$PICKLIST_NAME} {if $CHECK}active{/if}" title="{$VALUE}">
					<input type="radio" name="{$FIELD_MODEL->getFieldName()}"
						id="option_{$PICKLIST_NAME}"
						value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}"
						data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
						data-fieldinfo='{\App\Json::encode($FIELD_MODEL->getFieldInfo())|escape}'
						{if $CHECK} checked {/if}
						{if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if}
						title="{$VALUE}">
					{assign var=ACTIVITYTYPE_ICON value=\App\Fields\Picklist::getIcon($FIELD_MODEL->getName(), $PICKLIST_NAME)}
					{if !empty($ACTIVITYTYPE_ICON)}
						{if $ACTIVITYTYPE_ICON['type'] eq 'icon'}
							<span class="{$ACTIVITYTYPE_ICON['name']} mr-1"></span>
						{elseif $ACTIVITYTYPE_ICON['type'] eq 'image' && \App\Layout\Media::getImageUrl($ACTIVITYTYPE_ICON['name'])}
							<img class="icon-img--picklist mr-1" src="{\App\Layout\Media::getImageUrl($ACTIVITYTYPE_ICON['name'])}">
						{/if}
					{/if}
					<span>{$VALUE}</span>
				</label>
			{/foreach}
		</div>
	</div>
	<!-- /tpl-Calendar-Edit-Field-ActivityType -->
{/strip}
