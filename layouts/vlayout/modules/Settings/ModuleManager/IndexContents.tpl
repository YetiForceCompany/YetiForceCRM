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
	<div class="well contentsBackground">
		<div class="row-fluid">
			<span class="span12">
					{foreach item=MODULE_MODEL key=MODULE_ID from=$ALL_MODULES}
						{$MODULE_MODEL->get('name')}  &nbsp;&nbsp;
						{if $MODULE_MODEL->isEntityModule() eq true}
							{$MODULE_MODEL->getSettingLinks()} &nbsp;&nbsp; 
						{/if}<br>
					{/foreach}
			</span>
			
		</div>
		
	</div>
{/strip}