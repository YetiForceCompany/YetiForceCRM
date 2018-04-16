{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div class="col-md-12 pr-0 row">
		<input type="hidden" id="conversion_available_status" value="{\App\Purifier::encodeHtml($CONVERSION_AVAILABLE_STATUS)}" />
		<div class="col-12 col-sm-12 col-md-8">
			<div class="moduleIcon">
				<span class="detailViewIcon userIcon-{$MODULE}"></span>
			</div>
			<div class="paddingLeft5px">
				<h4 class="recordLabel u-text-ellipsis pushDown marginbottomZero" title="{$RECORD->getName()}">
					<span class="modCT_{$MODULE_NAME}">{$RECORD->getName()}</span>
					{assign var=RECORD_STATE value=\App\Record::getState($RECORD->getId())}
					{if $RECORD_STATE !== 'Active'}
						&nbsp;&nbsp;
						{assign var=COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
						<span class="badge badge-secondary" {if $COLOR[$RECORD_STATE]}style="background-color: {$COLOR[$RECORD_STATE]};"{/if}>
							{if \App\Record::getState($RECORD->getId()) === 'Trash'}
								{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
							{else}
								{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
							{/if}
						</span>
					{/if}
				</h4>
				<div class="paddingLeft5px">
					<span class="designation_label">{$RECORD->getDisplayValue('designation')}</span>
					{if $RECORD->getDisplayValue('designation') && $RECORD->getDisplayValue('company')}
						&nbsp;{\App\Language::translate('LBL_AT')}&nbsp;
					{/if}
					<span class="company_label">{$RECORD->getDisplayValue('company')}</span>
				</div>
				<div class="paddingLeft5px">
					<span class="muted">
						{\App\Language::translate('Assigned To',$MODULE_NAME)}: {$RECORD->getDisplayValue('assigned_user_id')}
						{assign var=SHOWNERS value=$RECORD->getDisplayValue('shownerid')}
						{if $SHOWNERS != ''}
							<br />{\App\Language::translate('Share with users',$MODULE_NAME)} {$SHOWNERS}
						{/if}
					</span>
				</div>
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('Detail/HeaderFields.tpl', $MODULE_NAME)}
	</div>
{/strip}
