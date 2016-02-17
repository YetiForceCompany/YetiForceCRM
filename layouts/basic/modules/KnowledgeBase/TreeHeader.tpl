{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="widget_header row marginBottom10px">
		<div class="col-sm-8 col-xs-12">
			<div class="btn-group paddingRight10">
				{if count($QUICK_LINKS['SIDEBARLINK']) gt 0}
					<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						<span class="glyphicon glyphicon-list" aria-hidden="true"></span>
						&nbsp;&nbsp;<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						{foreach item=SIDEBARLINK from=$QUICK_LINKS['SIDEBARLINK']}
							{assign var=SIDE_LINK_URL value=decode_html($SIDEBARLINK->getUrl())}
							{assign var="EXPLODED_PARSE_URL" value=explode('?',$SIDE_LINK_URL)}
							{assign var="COUNT_OF_EXPLODED_URL" value=count($EXPLODED_PARSE_URL)}
							{if $COUNT_OF_EXPLODED_URL gt 1}
								{assign var="EXPLODED_URL" value=$EXPLODED_PARSE_URL[$COUNT_OF_EXPLODED_URL-1]}
							{/if}
							{assign var="PARSE_URL" value=explode('&',$EXPLODED_URL)}
							{assign var="CURRENT_LINK_VIEW" value='view='|cat:$CURRENT_VIEW}
							{assign var="LINK_LIST_VIEW" value=in_array($CURRENT_LINK_VIEW,$PARSE_URL)}
							{assign var="CURRENT_MODULE_NAME" value='module='|cat:$MODULE}
							{assign var="IS_LINK_MODULE_NAME" value=in_array($CURRENT_MODULE_NAME,$PARSE_URL)}
							<li>
								<a class="quickLinks" href="{$SIDEBARLINK->getUrl()}">
									{vtranslate($SIDEBARLINK->getLabel(), $MODULE)}
								</a>
							</li>
						{/foreach}
					</ul>
				{/if}
			</div>
			<div class="btn-group">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			</div>
		</div>
		<div class="col-sm-4 col-xs-12">
			<div class="pull-right">
				<button class="btn btn-success addRecord">{vtranslate('LBL_ADD', $MODULE_NAME)}</button>
			</div>
		</div>
	</div>
	<div class="col-sm-3 col-xs-12 panelTree">
		<div class="col-xs-12 treeContainer paddingLRZero">
			<div class="input-group paddingBottom10">
				<input id="valueSearchTree" type="text" class="form-control" placeholder="{vtranslate('LBL_SEARCH', $MODULE_NAME)} ..." >
				<span class="input-group-btn">
					<button id="btnSearchTree" class="btn btn-danger" type="button">{vtranslate('LBL_SEARCH', $MODULE_NAME)}</button>
				</span>
			</div>
			<div id="treeContent">
			</div>
		</div>
	</div>	
	<div class="col-sm-9 col-xs-12 contentOfData">
	</div>
{/strip}
