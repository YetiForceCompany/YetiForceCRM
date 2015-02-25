{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}

{strip}
	<div class="container-fluid settingsIndexPage">
		<div class="widget_header row-fluid">
			<h3>{vtranslate('LBL_CONVERSION_TO_ACCOUNT',$QUALIFIED_MODULE)}</h3>
		</div>
		<hr>
		<form  method="post" action="index.php">
			<div class="control-group span5" >
				<label class="span3">{vtranslate('LBL_CONVERSION_TO_ACCOUNT',$QUALIFIED_MODULE)}</label>
				<input class="span1" type="checkbox" name="conversiontoaccount"  {if $STATE} checked {/if} />
			</div>

			<span class="alert alert-info pull-right span7">
				{vtranslate('LBL_CONVERSION_TO_ACCOUNT_INFO',$QUALIFIED_MODULE)}
			</span>
		</form>
		<span class="span12">
			<button style="margin-left: 20px;" id="saveConversionState" class="btn btn-success">{vtranslate('LBL_SAVE',$QUALIFIED_MODULE)}</button>
		</span>
		
	</div>
{/strip}
