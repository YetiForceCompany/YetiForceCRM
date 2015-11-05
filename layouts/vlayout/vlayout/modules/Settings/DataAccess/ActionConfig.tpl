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
<div class="editContainer" style="padding-left: 3%;padding-right: 3%">
	{include file='Header.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
	<div id="step">
		<form name="condition" action="index.php" method="post" id="ActionConfig" class="form-horizontal" >
			<input type="hidden" name="module" value="{$MODULE_NAME}" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="SaveActionConfig" />
			<input type="hidden" name="base_module" value="{$BASE_MODULE}" />
			<input type="hidden" name="tpl_id" value="{$TPL_ID}" />
			<input type="hidden" name="aid" value="{$AID}" />
			<input type="hidden" name="an" value="{$ACTIONNAME}" />
			<input type="hidden" name="data" value='' />
			<div class="row padding1per contentsBackground" style="border:1px solid #ccc;box-shadow: 2px 2px 2px rgba(0, 0, 0, 0.5);">
				{assign var="TPL" value="data_access/$ACTION.tpl"}
				<h4 class="padding-bottom1per"><strong>{vtranslate('LBL_CONFIG_ACTION',$QUALIFIED_MODULE)}: {Settings_DataAccess_Module_Model::getActionName($ACTIONNAME,true)}</strong></h4>
				<div class="alert alert-info">{Settings_DataAccess_Module_Model::getActionName($ACTIONNAME,false)}</div>
				{include file=$TPL|@vtemplate_path:$ACTIONMOD}
				<br><br>
				<div class="pull-right paddingTop20">
					<a class="btn btn-danger backStep" type="button" href="index.php?module={$MODULE_NAME}&parent=Settings&view=Step3&tpl_id={$TPL_ID}&base_module={$BASE_MODULE}&s=false">{vtranslate('BACK', $QUALIFIED_MODULE)}</a>&nbsp;&nbsp;
					<button class="btn btn-success" type="submit"><strong>{vtranslate('NEXT', $QUALIFIED_MODULE)}</strong></button>
					<a class="cancelLink btn btn-warning" type="reset" href="index.php?module=DataAccess&parent=Settings&view=Index">{vtranslate('CANCEL', $QUALIFIED_MODULE)}</a>
				</div>
			</div>
		</form>
	</div>
	<input type="hidden" name="next_step" value="Step4" />
	<div class="clearfix"></div>
</div>
<script type="text/javascript">
$('#ActionConfig').submit(function () {
var form = $(this);
var data = form.serializeFormData();
delete data.data;delete data.module;delete data.parent;delete data.action;delete data.base_module;delete data.tpl_id;delete data.aid;
$('[name="data"]').val( JSON.stringify( data ) );
});
</script>
{/strip}
