{*/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/*}

 <div class="container-fluid supportProcessesContainer" style="margin-top:10px;">
 	<h3>{vtranslate('LBL_MARKETING_PROCESSES', $QUALIFIED_MODULE)}</h3>&nbsp;<hr>
	{vtranslate('LBL_MARKETING_PROCESSES_DESCRIPTION', $QUALIFIED_MODULE)}
	<ul id="tabs" class="nav nav-tabs nav-justified" data-tabs="tabs">
		<li class="active"><a href="#conversiontoaccount" data-toggle="tab">{vtranslate('LBL_CONVERSION_TO_ACCOUNT', $QUALIFIED_MODULE)} </a></li>
	</ul>
	<div class="tab-content">
		<div class='editViewContainer tab-pane active' id="conversiontoaccount">
		<div class="container-fluid settingsIndexPage">
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
		</div>
	</div>
</div>
