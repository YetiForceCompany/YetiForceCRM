{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<div class="tpl-TooltipContents" data-js="container">
		<table class="table table-bordered">
			{foreach item=FIELD_MODEL key=FIELD_NAME from=$RECORD_STRUCTURE['TOOLTIP_FIELDS'] name=fieldsCount}
				{if $smarty.foreach.fieldsCount.index < 7}
					<tr>
						<td>
							<label>{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$MODULE)}</label>
						</td>
						<td>
							<span>
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
							</span>
						</td>
					</tr>
				{/if}
			{/foreach}
		</table>
	</div>
{/strip}
