{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-EventHandler-Index -->
	<div>
		<div class="o-breadcrumb widget_header row mb-2">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div>
			<ul id="tabs" class="nav nav-tabs my-2 mr-0" data-tabs="tabs">
				<li class="nav-item">
					<a class="nav-link {if $ACTIVE_TAB eq 'EditViewPreSave'}active{/if}" href="#EditViewPreSave" data-toggle="tab">
						<span class="mdi mdi-content-save-settings mr-2"></span>{\App\Language::translate('LBL_EDIT_VIEW_PRESAVE', $QUALIFIED_MODULE)}
					</a>
				</li>
			</ul>
		</div>
		<div id="my-tab-content" class="tab-content">
			<div class="js-tab tab-pane {if $ACTIVE_TAB eq 'EditViewPreSave'}active{/if}" id="EditViewPreSave" data-name="EditViewPreSave" data-js="data">
				<form class="js-validation-form">
					<div class="js-config-table table-responsive" data-js="container">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th class="text-center" scope="col">{\App\Language::translate('LBL_EVENT_NAME', $QUALIFIED_MODULE)}</th>
									<th class="text-center" scope="col">{\App\Language::translate('LBL_EVENT_DESC', $QUALIFIED_MODULE)}</th>
									<th class="text-center" scope="col">{\App\Language::translate('LBL_INCLUDE_MODULES', $QUALIFIED_MODULE)}</th>
									<th class="text-center" scope="col">{\App\Language::translate('LBL_EXCLUDE_MODULES', $QUALIFIED_MODULE)}</th>
									<th class="text-center" scope="col">{\App\Language::translate('LBL_EVENT_IS_ACTIVE', $QUALIFIED_MODULE)}</th>
								</tr>
							</thead>
							<tbody>
								{foreach from=\App\EventHandler::getByType(\App\EventHandler::EDIT_VIEW_PRE_SAVE, '', false) item=ITEM}
									<tr>
										<th scope="row">{\App\Language::translate(strtoupper($ITEM['handler_class']), 'Other.EventHandler')}</th>
										<td>{\App\Language::translate(strtoupper("{$ITEM['handler_class']}_DESC"), 'Other.EventHandler')}</td>
										<td>
											{foreach from=explode(',',$ITEM['include_modules']) item=VALUE name=LIST}
												{\App\Language::translate($VALUE, $VALUE)}
												{if not $smarty.foreach.LIST.last},{/if}
											{/foreach}
										</td>
										<td>
											{foreach from=explode(',',$ITEM['exclude_modules']) item=VALUE name=LIST}
												{\App\Language::translate($VALUE, $VALUE)}
												{if not $smarty.foreach.LIST.last}, {/if}
											{/foreach}
										</td>
										<td class="text-center">
											<input name="{$ITEM['handler_class']}" value="1" type="checkbox" {if $ITEM['is_active'] eq 1}checked{/if}>
										</td>
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-EventHandler-Index -->
{/strip}
