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
			<div>
				<div class="pull-left spanModuleIcon moduleIcon{$MODULE_NAME}">
					<span class="moduleIcon">
						{assign var=IMAGE_DETAILS value=$RECORD->getImageDetails()}
						{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
							{if !empty($IMAGE_INFO.path)}
								<img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" class="pushDown" alt="{$IMAGE_INFO.orgname}" title="{$IMAGE_INFO.orgname}" width="65" height="80" align="left"><br>
							{else}
								<img src="{vimage_path('Contacts48.png')}" class="summaryImg" alt="{vtranslate($MODULE, $MODULE)}"/>
							{/if}
						{/foreach}
						{if empty($IMAGE_DETAILS)}
							<span class="detailViewIcon userIcon-{$MODULE}" {if $COLORLISTHANDLERS}style="background-color: {$COLORLISTHANDLERS['background']};color: {$COLORLISTHANDLERS['text']};"{/if}></span>
						{/if}
					</span>
				</div>
				<h4 class="recordLabel pushDown marginbottomZero textOverflowEllipsis" title="{$RECORD->getDisplayValue('salutationtype')}&nbsp;{$RECORD->getName()}">
					{if $RECORD->getDisplayValue('salutationtype')}
						<span class="salutation">{$RECORD->getDisplayValue('salutationtype')}</span>&nbsp;
					{/if}
					<span class="moduleColor_{$MODULE_NAME}">{$RECORD->getName()}</span>
				</h4>
			</div>
			<div class="paddingLeft5px">
				{$RECORD->getDisplayValue('parent_id')}
				<div>
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
