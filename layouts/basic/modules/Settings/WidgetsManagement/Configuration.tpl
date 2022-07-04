{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-WidgetsManagement-Configuration" id="widgetsManagementEditorContainer">
		<input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}" />
		<div class="o-breadcrumb widget_header row align-items-lg-center">
			<div class="col-md-9">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-3">
				<div class="float-right col-6 col-md-6 px-0">
					<select class="select2 form-control" name="widgetsManagementEditorModules">
						{foreach item=SUPPORTED_MODULE from=$SUPPORTED_MODULES}
							<option value="{$SUPPORTED_MODULE}" {if $SUPPORTED_MODULE eq $SELECTED_MODULE_NAME} selected {/if}>{\App\Language::translate($SUPPORTED_MODULE, $SUPPORTED_MODULE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
		<ul class="nav nav-tabs massEditTabs selectDashboard mb-2">
			{foreach from=$DASHBOARD_TYPES key=DASHBOARD_ID item=DASHBOARD}
				<li class="nav-item" data-id="{$DASHBOARD['dashboard_id']}">
					<a class="nav-link{if $CURRENT_DASHBOARD eq $DASHBOARD['dashboard_id']} active{/if}"
						data-toggle="tab">
						<strong>{\App\Language::translate($DASHBOARD['name'])}</strong>
						<button class="btn btn-primary btn-xs ml-2 editDashboard"><span class="yfi yfi-full-editing-view"></span>
						</button>
						{if $DASHBOARD['system'] neq 1}
							<button class="btn btn-danger btn-xs ml-2 deleteDashboard"><span
									class="fas fa-trash-alt"></span></button>
						{/if}
					</a>
				</li>
			{/foreach}
			<li class="nav-item addDashboard">
				<a class="nav-link"><strong><span class="fas fa-plus"></span></strong></a>
			</li>
		</ul>
		<div class="contents tabbable">
			<div class="tab-content themeTableColor overflowVisible">
				<div class="tab-pane active" id="layoutDashBoards">
					<div class="btn-toolbar mb-2">
						<button type="button" class="btn btn-success addBlockDashBoard btn-sm"><span
								class="fas fa-plus"></span>&nbsp;{\App\Language::translate('LBL_ADD_CONDITION', $QUALIFIED_MODULE)}
						</button>
					</div>
					<div id="moduleBlocks">
						{foreach key=AUTHORIZATION_KEY item=AUTHORIZATION_INFO from=$DASHBOARD_AUTHORIZATION_BLOCKS}
							{if isset($AUTHORIZATION_INFO['name'])}
								{assign var=AUTHORIZATION_NAME value=$AUTHORIZATION_INFO['name']}
							{else}
								{assign var=AUTHORIZATION_NAME value=''}
							{/if}
							<div id="block_{$AUTHORIZATION_KEY}"
								class="editFieldsTable block_{$AUTHORIZATION_KEY} mb-2 border1px blockSortable bg-white"
								data-block-id="{$AUTHORIZATION_KEY}" data-sequence=""
								data-code="{if isset($AUTHORIZATION_INFO['code'])}{$AUTHORIZATION_INFO['code']}{/if}"
								style="border-radius: 4px 4px 0px 0px;">
								<div class="row layoutBlockHeader m-0">
									<div class="blockLabel col-sm-5 p-2 ">
										<span class="ml-3">
											<strong>{\App\Language::translate($AUTHORIZATION_NAME, $SELECTED_MODULE_NAME)}</strong>
										</span>
									</div>
									<div class="col-sm-7 ml-0 float-right">
										<div class="float-right btn-toolbar blockActions m-1">
											<div class="btn-group">
												<button class="btn btn-success btn-sm js-add-widget" data-url="index.php?parent=Settings&module=WidgetsManagement&view=WidgetListModal&blockId={$AUTHORIZATION_KEY}&dashboardId={$DASHBOARD_ID}" type="button">
													<span class="fas fa-plus mr-2"></span>
													<strong>{\App\Language::translate('LBL_ADD_WIDGET', $QUALIFIED_MODULE)}</strong>
												</button>
											</div>
											{if isset($SPECIAL_WIDGETS['Rss'])}
												{assign var=RSS_WIDGET value=$SPECIAL_WIDGETS['Rss']}
												<div class="btn-group ml-1">
													<button class="btn btn-success btn-sm addRss" type="button"
														data-url="{$RSS_WIDGET->getUrl()}"
														data-linkid="{$RSS_WIDGET->get('linkid')}"
														data-name="{$RSS_WIDGET->getName()}"
														data-width="{$RSS_WIDGET->getWidth()}"
														data-height="{$RSS_WIDGET->getHeight()}"
														data-block-id="{$AUTHORIZATION_KEY}"><span
															class="fas fa-plus mr-2"></span>
														<strong>{\App\Language::translate('LBL_ADD_RSS', $QUALIFIED_MODULE)}</strong>
													</button>
												</div>
											{/if}
											{if isset($SPECIAL_WIDGETS['Mini List'])}
												{assign var=MINILISTWIDGET value=$SPECIAL_WIDGETS['Mini List']}
												<div class="btn-group ml-1">
													<button class="btn btn-success btn-sm addMiniList" type="button"
														data-url="index.php?module=Home&view=MiniListWizard&step=step1&linkId={$MINILISTWIDGET->get('linkid')}"
														data-linkid="{$MINILISTWIDGET->get('linkid')}"
														data-name="{$MINILISTWIDGET->getName()}"
														data-width="{$MINILISTWIDGET->getWidth()}"
														data-height="{$MINILISTWIDGET->getHeight()}"
														data-block-id="{$AUTHORIZATION_KEY}"><span
															class="fas fa-plus mr-2"></span>
														<strong>{\App\Language::translate('LBL_ADD_MINILIST', $QUALIFIED_MODULE)}</strong>
													</button>
												</div>
											{/if}
											{if isset($SPECIAL_WIDGETS['ChartFilter'])}
												{assign var=CHART_FILTER_WIDGET value=$SPECIAL_WIDGETS['ChartFilter']}
												<div class="btn-group ml-1">
													<button class="btn btn-success btn-sm js-show-modal" type="button"
														data-url="index.php?module={$SELECTED_MODULE_NAME}&view=ChartFilter&step=step1&linkId={$CHART_FILTER_WIDGET->get('linkid')}"
														data-linkid="{$CHART_FILTER_WIDGET->get('linkid')}"
														data-name="{$CHART_FILTER_WIDGET->getName()}"
														data-width="{$CHART_FILTER_WIDGET->getWidth()}"
														data-height="{$CHART_FILTER_WIDGET->getHeight()}"
														data-block-id="{$AUTHORIZATION_KEY}"
														data-module="{$SELECTED_MODULE_NAME}"
														data-modalId="{\App\Layout::getUniqueId('ChartFilter')}"><span
															class="fas fa-plus mr-2"></span>&nbsp;
														<strong>{\App\Language::translate('LBL_ADD_CHART_FILTER', $QUALIFIED_MODULE)}</strong>
													</button>
												</div>
											{/if}
											{if isset($SPECIAL_WIDGETS['Notebook']) && $SPECIAL_WIDGETS['Notebook']}
												{assign var=NOTEBOOKWIDGET value=$SPECIAL_WIDGETS['Notebook']}
												<div class="btn-group ml-1">
													<button class="btn btn-success btn-sm addNotebook" type="button"
														data-url="{$NOTEBOOKWIDGET->getUrl()}"
														data-linkid="{$NOTEBOOKWIDGET->get('linkid')}"
														data-name="{$NOTEBOOKWIDGET->getName()}"
														data-width="{$NOTEBOOKWIDGET->getWidth()}"
														data-height="{$NOTEBOOKWIDGET->getHeight()}"
														data-block-id="{$AUTHORIZATION_KEY}"><span
															class="fas fa-plus mr-2"></span>
														<strong>{\App\Language::translate('LBL_ADD_NOTEBOOK', $QUALIFIED_MODULE)}</strong>
													</button>
												</div>
											{/if}
											<div class="btn-group actions ml-1">
												<a href="javascript:void(0)"
													class="js-delete-custom-block-btn btn btn-sm btn-danger"
													data-js="click">
													<span class="fas fa-trash-alt"
														title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
												</a>
											</div>
										</div>
									</div>
								</div>
								<div class="blockFieldsList blockFieldsSortable row p-1" style="min-height: 27px">
									<ul name="sortable1" class="connectedSortable col-md-6 p-1"
										style="list-style-type: none; min-height: 1px;">
										{if empty($WIDGETS_AUTHORIZATION_INFO[$AUTHORIZATION_KEY])}
											{assign var=WIDGETS_AUTHORIZATION value=[]}
										{else}
											{assign var=WIDGETS_AUTHORIZATION value=$WIDGETS_AUTHORIZATION_INFO[$AUTHORIZATION_KEY]}
										{/if}
										{foreach item=WIDGET_MODEL from=$WIDGETS_AUTHORIZATION name=fieldlist}
											{if $smarty.foreach.fieldlist.index % 2 eq 0}
												{include file=\App\Layout::getTemplatePath('WidgetConfig.tpl', $QUALIFIED_MODULE)}
											{/if}
										{/foreach}
									</ul>
									<ul name="sortable2" class="connectedSortable col-md-6 m-0 p-1"
										style="list-style-type: none; min-height: 1px;">
										{foreach item=WIDGET_MODEL from=$WIDGETS_AUTHORIZATION name=fieldlist1}
											{if $smarty.foreach.fieldlist1.index % 2 neq 0}
												{include file=\App\Layout::getTemplatePath('WidgetConfig.tpl', $QUALIFIED_MODULE)}
											{/if}
										{/foreach}
									</ul>
								</div>
							</div>
						{/foreach}
					</div>
					{* copy elements hide *}
					<div class="modal addBlockDashBoardModal fade">
						<div class="modal-dialog modal-lg">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title">
										<span class="fas fa-plus mr-1"></span>
										{\App\Language::translate('LBL_ADD_DASHBOARD_BLOCK', $QUALIFIED_MODULE)}
									</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<form class="form-horizontal addBlockDashBoardForm">
									<input type="hidden" name="dashboardId" value="{$CURRENT_DASHBOARD}">
									<div class="modal-body">
										<div class="form-group row">
											<div class="col-sm-4 col-form-label text-right">
												<span>{\App\Language::translate('LBL_CHOISE_AUTHORIZED', $QUALIFIED_MODULE)}</span>
												<span class="redColor">*</span>
											</div>
											<div class="col-sm-6 controls">
												<select class="authorized form-control validateForm mb-0 js-authorized" name="authorized" data-validation-engine="validate[required]">
													<optgroup label="{\App\Language::translate('LBL_ROLES', $QUALIFIED_MODULE)}">
														{foreach from=$ALL_AUTHORIZATION item=AUTHORIZED key=AUTHORIZED_CODE}
															<option value="{$AUTHORIZED_CODE}"
																data-label="{$AUTHORIZED->get('rolename')}">{\App\Language::translate($AUTHORIZED->get('rolename'),$QUALIFIED_MODULE)}</option>
														{/foreach}
													</optgroup>
													{if count($ALL_SERVERS)}
														<optgroup label="{\App\Language::translate('WebserviceApps', 'Settings:WebserviceApps')}">
															{foreach from=$ALL_SERVERS item=SERVER key=ID}
																<option value="{$ID}">{\App\Purifier::encodeHTML($SERVER['name'])}</option>
															{/foreach}
														</optgroup>
													{/if}
												</select>
											</div>
										</div>
									</div>
									{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', 'Vtiger') BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
								</form>
							</div>
						</div>
					</div>

					<div class="newCustomBlockCopy d-none mb-2 border1px blockSortable bg-white" data-block-id=""
						data-sequence="" style="border-radius: 4px 4px 0px 0px;">
						<div class="row layoutBlockHeader m-0">
							<div class="blockLabel col-md-5 p-2 ">
								<span class="ml-3">

								</span>
							</div>
							<div class="col-md-6 ml-0 float-right">

								<div class="float-right btn-toolbar blockActions m-1">
									<div class="btn-group">
										<button class="btn btn-success btn-sm addCustomField d-none" type="button"><span
												class="fas fa-plus"></span>&nbsp;
											<strong>{\App\Language::translate('LBL_ADD_WIDGET', $QUALIFIED_MODULE)}</strong>
										</button>
									</div>
									{if isset($SPECIAL_WIDGETS['Rss'])}
										{assign var=RSS_WIDGET value=$SPECIAL_WIDGETS['Rss']}
										<div class="btn-group">
											<button class="btn btn-success btn-sm addRss specialWidget" type="button"
												data-url="{$RSS_WIDGET->getUrl()}"
												data-linkid="{$RSS_WIDGET->get('linkid')}"
												data-name="{$RSS_WIDGET->getName()}"
												data-width="{$RSS_WIDGET->getWidth()}"
												data-height="{$RSS_WIDGET->getHeight()}" data-block-id=""><span
													class="fas fa-plus"></span>
												<strong>{\App\Language::translate('LBL_ADD_RSS', $QUALIFIED_MODULE)}</strong>
											</button>
										</div>
									{/if}
									{if isset($SPECIAL_WIDGETS['Mini List'])}
										{assign var=MINILISTWIDGET value=$SPECIAL_WIDGETS['Mini List']}
										<div class="btn-group">
											<button class="btn btn-success btn-sm addMiniList specialWidget"
												type="button"
												data-url="index.php?module=Home&view=MiniListWizard&step=step1&linkId={$MINILISTWIDGET->get('linkid')}"
												data-linkid="{$MINILISTWIDGET->get('linkid')}"
												data-name="{$MINILISTWIDGET->getName()}"
												data-width="{$MINILISTWIDGET->getWidth()}"
												data-height="{$MINILISTWIDGET->getHeight()}" data-block-id=""><span
													class="fas fa-plus"></span>&nbsp;
												<strong>{\App\Language::translate('LBL_ADD_MINILIST', $QUALIFIED_MODULE)}</strong>
											</button>
										</div>
									{/if}
									{if isset($SPECIAL_WIDGETS['ChartFilter'])}
										{assign var=CHART_FILTER_WIDGET value=$SPECIAL_WIDGETS['ChartFilter']}
										<div class="btn-group ml-1">
											<button class="btn btn-success btn-sm js-show-modal" type="button"
												data-url="index.php?module={$SELECTED_MODULE_NAME}&view=ChartFilter&step=step1&linkId={$CHART_FILTER_WIDGET->get('linkid')}"
												data-linkid="{$CHART_FILTER_WIDGET->get('linkid')}"
												data-name="{$CHART_FILTER_WIDGET->getName()}"
												data-width="{$CHART_FILTER_WIDGET->getWidth()}"
												data-height="{$CHART_FILTER_WIDGET->getHeight()}"
												data-module="{$SELECTED_MODULE_NAME}"
												data-modalId="{\App\Layout::getUniqueId('ChartFilter')}"><span
													class="fas fa-plus"></span>&nbsp;
												<strong>{\App\Language::translate('LBL_ADD_CHART_FILTER', $QUALIFIED_MODULE)}</strong>
											</button>
										</div>
									{/if}
									{if isset($SPECIAL_WIDGETS['Notebook']) && $SPECIAL_WIDGETS['Notebook']}
										{assign var=NOTEBOOKWIDGET value=$SPECIAL_WIDGETS['Notebook']}
										<div class="btn-group">
											<button class="btn btn-success btn-sm addNotebook specialWidget"
												type="button" data-url="{$NOTEBOOKWIDGET->getUrl()}"
												data-linkid="{$NOTEBOOKWIDGET->get('linkid')}"
												data-name="{$NOTEBOOKWIDGET->getName()}"
												data-width="{$NOTEBOOKWIDGET->getWidth()}"
												data-height="{$NOTEBOOKWIDGET->getHeight()}" data-block-id=""><span
													class="fas fa-plus"></span>&nbsp;
												<strong>{\App\Language::translate('LBL_ADD_NOTEBOOK', $QUALIFIED_MODULE)}</strong>
											</button>
										</div>
									{/if}
									<div class="btn-group actions">
										<a href="javascript:void(0)"
											class="js-delete-custom-block-btn btn btn-sm btn-danger" data-js="click">
											<span class="fas fa-trash-alt"
												title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="blockFieldsList row blockFieldsSortable p-1" style="min-height: 27px">
							<ul class="connectedSortable col-md-6 ui-sortable float-left p-1"
								style="list-style-type: none; min-height:1px;" name="sortable1"></ul>
							<ul class="connectedSortable col-md-6 ui-sortable m-0 float-left p-1"
								style="list-style-type: none; min-height:1px;" name="sortable2"></ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
