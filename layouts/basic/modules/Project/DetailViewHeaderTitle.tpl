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
		<div class="col-xs-12 col-sm-12 col-md-8">
			<div class="moduleIcon">
				<span class="detailViewIcon userIcon-{$MODULE}" {if $COLORLISTHANDLERS}style="background-color: {$COLORLISTHANDLERS['background']};color: {$COLORLISTHANDLERS['text']};"{/if}></span>
			</div>
			<div class="paddingLeft5px">
				<h4 class="recordLabel textOverflowEllipsis pushDown marginbottomZero" title="{$RECORD->getName()}">
					<span class="moduleColor_{$MODULE_NAME}">{$RECORD->getName()}</span>
				</h4>
				{assign var=RELATED_TO value=$RECORD->get('linktoaccountscontacts')}
				{if !empty($RELATED_TO)}
					<div class="paddingLeft5px">
						<span class="muted">{vtranslate('Related to',$MODULE_NAME)} - </span>
						{$RECORD->getDisplayValue('linktoaccountscontacts')}
					</div>
				{/if}
				<div class="paddingLeft5px">
					<span class="muted">
						{vtranslate('Assigned To',$MODULE_NAME)}: {$RECORD->getDisplayValue('assigned_user_id')}
						{assign var=SHOWNERS value=$RECORD->getDisplayValue('shownerid')}
						{if $SHOWNERS != ''}
							<br/>{vtranslate('Share with users',$MODULE_NAME)} {$SHOWNERS}
						{/if}
					</span>
				</div>
			</div>
		</div>
		{include file='DetailViewHeaderFields.tpl'|@vtemplate_path:$MODULE_NAME}
	</div>
{/strip}
