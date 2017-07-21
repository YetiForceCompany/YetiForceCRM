{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
<div class="editContainer">
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
			<div class="contentsBackground col-md-12" style="border:1px solid #ccc;box-shadow: 2px 2px 2px rgba(0, 0, 0, 0.5);">
				{assign var="TPL" value="data_access/$ACTION.tpl"}
				<h4 class="padding-bottom1per"><strong>{\App\Language::translate('LBL_CONFIG_ACTION',$QUALIFIED_MODULE)}: {Settings_DataAccess_Module_Model::getActionName($ACTIONNAME,true)}</strong></h4>
				<div class="alert alert-info">{Settings_DataAccess_Module_Model::getActionName($ACTIONNAME,false)}</div>
				{include file=$TPL|@vtemplate_path:$ACTIONMOD}
				<br /><br />
				<div class="pull-right paddingTop20 paddingBottom20">
					<a class="btn btn-danger backStep" type="button" href="index.php?module={$MODULE_NAME}&parent=Settings&view=Step3&tpl_id={$TPL_ID}&base_module={$BASE_MODULE}&s=false">{\App\Language::translate('BACK', $QUALIFIED_MODULE)}</a>&nbsp;&nbsp;
					<button class="btn btn-success" type="submit"><strong>{\App\Language::translate('NEXT', $QUALIFIED_MODULE)}</strong></button>
					<a class="cancelLink btn btn-warning" type="reset" href="index.php?module=DataAccess&parent=Settings&view=Index">{\App\Language::translate('CANCEL', $QUALIFIED_MODULE)}</a>
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
