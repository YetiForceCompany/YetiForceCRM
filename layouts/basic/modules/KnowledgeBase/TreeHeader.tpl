{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<div class="widget_header row marginBottom10px">
		<div class="col-sm-8 col-xs-12">
			{include file='ButtonViewLinks.tpl'|@vtemplate_path LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS='paddingRight10'}
			<div class="btn-group">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			</div>
		</div>
		<div class="col-sm-4 col-xs-12">
			<div class="pull-right">
				<button class="btn btn-success addRecord">{\App\Language::translate('LBL_ADD', $MODULE_NAME)}</button>
			</div>
		</div>
	</div>
	<div class="col-sm-3 col-xs-12 panelTree">
		<div class="col-xs-12 treeContainer paddingLRZero">
			<div class="input-group paddingBottom10">
				<input id="valueSearchTree" type="text" class="form-control" placeholder="{\App\Language::translate('LBL_SEARCH', $MODULE_NAME)} ..." >
				<span class="input-group-btn">
					<button id="btnSearchTree" class="btn btn-danger" type="button">{\App\Language::translate('LBL_SEARCH', $MODULE_NAME)}</button>
				</span>
			</div>
			<div id="treeContent">
			</div>
		</div>
	</div>	
	<div class="col-sm-9 col-xs-12 contentOfData">
	</div>
{/strip}
