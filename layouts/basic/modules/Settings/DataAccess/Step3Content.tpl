{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<div class="row padding1per contentsBackground no-margin" style="border:1px solid #ccc;box-shadow: 2px 2px 2px rgba(0, 0, 0, 0.5);">
	<div id="advanceFilterContainer" class="">
		<a class="btn btn-primary pull-right marginBottom10px" href="index.php?module={$MODULE_NAME}&parent=Settings&view=AddAction&id={$TPL_ID}&base_module={$BASE_MODULE}">
			<strong>{\App\Language::translate('LBL_NEW_ACTION', $QUALIFIED_MODULE)}</strong>
		</a>
		<h5 class="padding-bottom1per"><strong>{\App\Language::translate('LBL_CHOOSE_ACTIONS',$QUALIFIED_MODULE)}</strong></h5>
		{include file='ListAction.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
		<br />
		<div class="pull-right">
			<a class="btn btn-danger backStep" href="index.php?module=DataAccess&parent=Settings&view=Step2&tpl_id={$TPL_ID}&base_module={$BASE_MODULE}"><strong>{\App\Language::translate('BACK', $QUALIFIED_MODULE)}</strong></a>
			<a class="btn btn-success" href="index.php?module=DataAccess&parent=Settings&view=Index">{\App\Language::translate('NEXT', $QUALIFIED_MODULE)}</a>
			<a class="cancelLink btn btn-warning" href="index.php?module=DataAccess&parent=Settings&view=Index">{\App\Language::translate('CANCEL', $QUALIFIED_MODULE)}</a>
		</div>
	</div>
<div class="clearfix"></div>
