{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-EventHandler-Index -->
	<div>
		{assign var=HANDLER_TYPES value=\App\EventHandler::HANDLER_TYPES}
		<div class="o-breadcrumb widget_header row mb-2">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div>
			<ul id="tabs" class="nav nav-tabs my-2 mr-0" data-tabs="tabs">
				{foreach key=INDEX item=ITEM  from=$HANDLER_TYPES}
					{if !isset($HANDLERS[$INDEX])} {continue} {/if}
					<li class="nav-item">
						<a class="nav-link {if $ACTIVE_TAB eq $INDEX} active{/if}" href="#{$INDEX}" data-toggle="tab">
							{if isset($ITEM['icon'])}
								<span class="{$ITEM['icon']} mr-2"></span>
							{/if}
							{\App\Language::translate($ITEM['label'], $QUALIFIED_MODULE)}
						</a>
					</li>
				{/foreach}
			</ul>
		</div>
		<div id="my-tab-content" class="tab-content">
			{foreach key=INDEX item=ITEM  from=$HANDLER_TYPES}
				{if !isset($HANDLERS[$INDEX])} {continue} {/if}
				<div class="js-tab tab-pane {if $ACTIVE_TAB eq $INDEX}active{/if}" id="{$INDEX}" data-name="{$INDEX}" data-js="data">
					<form class="js-validation-form">
						<div class="js-config-table table-responsive" data-js="container">
							<table class="table table-bordered">
								<thead>
									<tr>
										{foreach key=$NAME item=ITEM  from=$HANDLER_TYPES[$INDEX]['columns']}
											<th data-name="{$NAME}" class="text-center" scope="col">{\App\Language::translate($ITEM['label'], $QUALIFIED_MODULE)}</th>
										{/foreach}
									</tr>
								</thead>
								<tbody>
									{foreach from=$HANDLERS[$INDEX] item=ITEM}
										<tr>
											<th scope="row">{\App\Language::translate(strtoupper($ITEM['handler_class']), 'Other.EventHandler')}</th>
											<td>{\App\Language::translate(strtoupper("{$ITEM['handler_class']}_DESC"), 'Other.EventHandler')}</td>
											<td>
												{foreach from=explode(',',$ITEM['include_modules']) item=VALUE name=LIST}
													{\App\Language::translate($VALUE, $VALUE)}
													{if not $smarty.foreach.LIST.last}, {/if}
												{/foreach}
											</td>
											<td>
												{foreach from=explode(',',$ITEM['exclude_modules']) item=VALUE name=LIST}
													{\App\Language::translate($VALUE, $VALUE)}
													{if not $smarty.foreach.LIST.last}, {/if}
												{/foreach}
											</td>
											<td class="text-center">
												<input name="{$ITEM['handler_class']}" {if $ITEM['privileges'] == \App\EventHandler::SYSTEM} disabled {/if}value="1" type="checkbox" {if $ITEM['is_active'] eq 1}checked{/if}>
											</td>
										</tr>
									{/foreach}
								</tbody>
							</table>
						</div>
					</form>
				</div>
			{/foreach}
		</div>
	</div>
	<!-- /tpl-Settings-EventHandler-Index -->
{/strip}
