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
	<div class="col-md-12 paddingLRZero row">
		<div class="col-12 col-sm-12 col-md-8">
			<div class="moduleIcon">
				{if AppConfig::module($MODULE_NAME, 'COUNT_IN_HIERARCHY')}
					<span class="hierarchy">
						<span class="badge {if $RECORD->get('active')} bgGreen {else} bgOrange {/if}"></span>
					</span>
				{/if}
				<span class="detailViewIcon cursorPointer userIcon-{$MODULE}"></span>
			</div>
			<div class="paddingLeft5px">
				<h4 class="recordLabel textOverflowEllipsis pushDown marginbottomZero" title="{$RECORD->getName()}">
					<span class="modCT_{$MODULE_NAME}">{$RECORD->getName()}</span>
					{assign var=RECORD_STATE value=\App\Record::getState($RECORD->getId())}
					{if $RECORD_STATE !== 'Active'}
						&nbsp;&nbsp;
						{assign var=COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
						<span class="label label-default" {if $COLOR[$RECORD_STATE]}style="background-color: {$COLOR[$RECORD_STATE]};"{/if}>
							{if \App\Record::getState($RECORD->getId()) === 'Trash'}
								{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
							{else}
								{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
							{/if}
						</span>
					{/if}
				</h4>
				<span class="muted">
					{\App\Language::translate('Assigned To',$MODULE_NAME)}: {$RECORD->getDisplayValue('assigned_user_id')}
					{assign var=SHOWNERS value=$RECORD->getDisplayValue('shownerid')}
					{if $SHOWNERS != ''}
						<br />{\App\Language::translate('Share with users',$MODULE_NAME)} {$SHOWNERS}
					{/if}
				</span>
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('DetailViewHeaderFields.tpl', $MODULE_NAME)}
	</div>
{/strip}
