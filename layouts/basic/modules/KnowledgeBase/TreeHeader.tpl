{strip}
	<div class="widget_header row marginBottom10px">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
	<div class="col-xs-12 treeContainer">
		<div class="input-group paddingBottom10">
			<input id="valueSearchTree" type="text" class="form-control" placeholder="{vtranslate('LBL_SEARCH', $MODULE_NAME)} ..." >
			<span class="input-group-btn">
				<button id="btnSearchTree" class="btn btn-danger" type="button">{vtranslate('LBL_SEARCH', $MODULE_NAME)}</button>
			</span>
		</div>
		<div id="treeContent">		
		</div>
	</div>
{/strip}
