{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="row padding0">
		<div class="col-md-8 rowContent">
			<div class="widget_header row paddingTop10">
				<div class="pull-left paddingLeftMd">
					<div class="btn-toolbar">
						<div class="btn-group">
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
					</div>
				</div>
				<div class="col-md-11">
					{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				</div>
			</div>
			<div class="row">
				<div class="col-md-12" id="recordsListContents">
				{/strip}
