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
<div style="padding-left: 15px;">
    <form onsubmit="" action="index.php" enctype="multipart/form-data" method="POST" name="importBasic">
        <input type="hidden" name="module" value="{$FOR_MODULE}" />
        <input type="hidden" name="view" value="Import" />
        <input type="hidden" name="mode" value="uploadAndParse" />
	<div class='widget_header'>
		{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
	</div>
	<hr>
		<div class="row col-xs-12 searchUIBasic" style='margin:0 !important'>            
		<div class="col-xs-12 font-x-large">
		<strong>{'LBL_IMPORT'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$FOR_MODULE}</strong>
	    </div>
            {if $ERROR_MESSAGE neq ''}
		<div class="col-xs-12">
                    <div class="style1">
                        <span class="alert-warning">{$ERROR_MESSAGE}</span>
                    </div>
                </div>
            {/if}
		<div class="col-xs-12">
			<div class='leftFormBorder1 importContents'>
			    {include file='Import_Step1.tpl'|@vtemplate_path:'Import'}
			</div>
		</div>
		<div class="col-xs-12">
			<div class="leftFormBorder1 importContents">
				{include file='Import_Step2.tpl'|@vtemplate_path:'Import'}
			</div>
		</div>
            {if $DUPLICATE_HANDLING_NOT_SUPPORTED neq 'true'}
				<div class="col-xs-12">
                    <div class="leftFormBorder1 importContents">
                        {include file='Import_Step3.tpl'|@vtemplate_path:'Import'}
                    </div>
                </div>
            {/if}
			<div class="col-xs-12">
				<div>
                    {include file='Import_Basic_Buttons.tpl'|@vtemplate_path:'Import'}
                </div>
            </div>
        </div>
    </form>
</div>
{/strip}
