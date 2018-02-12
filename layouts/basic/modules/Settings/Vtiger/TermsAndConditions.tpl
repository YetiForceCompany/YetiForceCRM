{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
	<div class="" id="TermsAndConditionsContainer">
		<div class='widget_header row '>
			<div class="col-xs-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			</div>
		</div>
		<div class="contents">
			<br />
			<textarea class="input-xxlarge TCContent textarea-autosize form-control" rows="3" placeholder="{\App\Language::translate('LBL_INV_TANDC_DESCRIPTION', $QUALIFIED_MODULE)}" style="width:100%;" >{$CONDITION_TEXT}</textarea>
			<div class="textAlignCenter">
				<br />
				<button class="btn btn-success saveTC hide float-right"><strong>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
			</div>
		</div>
	</div>
{/strip}
