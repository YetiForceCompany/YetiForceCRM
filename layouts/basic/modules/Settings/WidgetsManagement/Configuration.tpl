{strip}
{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="" id="widgetsManagementEditorContainer">
	<input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}" />
	<div class="widget_header row">
		<div class="col-md-9">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			{\App\Language::translate('LBL_WIDGETS_MANAGEMENT_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
		<div class="col-md-3">
			<div class="float-right col-xs-6 col-md-6 paddingLRZero">
				<select class="chzn-select form-control" name="widgetsManagementEditorModules">
					{foreach item=SUPPORTED_MODULE from=$SUPPORTED_MODULES}
						<option value="{$SUPPORTED_MODULE}" {if $SUPPORTED_MODULE eq $SELECTED_MODULE_NAME} selected {/if}>{\App\Language::translate($SUPPORTED_MODULE, $SUPPORTED_MODULE)}</option>
					{/foreach}
				</select>
			</div>
		</div>
	</div>
	<ul class="nav nav-tabs massEditTabs selectDashboard marginBottom10px">
		{foreach from=$DASHBOARD_TYPES item=DASHBOARD}
			<li {if $CURRENT_DASHBOARD eq $DASHBOARD['dashboard_id']}class="active"{/if} data-id="{$DASHBOARD['dashboard_id']}">
				<a data-toggle="tab">
					<strong>{\App\Language::translate($DASHBOARD['name'])}</strong>					
					<button class="btn btn-primary btn-sm fas fa-pencil-alt marginLeft10 editDashboard"></button>
					{if $DASHBOARD['system'] neq 1}
						<button class="btn btn-danger btn-sm fas fa-trash-alt marginLeft10 deleteDashboard"></button>
					{/if}
				</a>
			</li>
		{/foreach}
		<li class="addDashboard">
			<a><strong><span class="fas fa-plus"></span></strong></a>
		</li>
	</ul>
	<div class="contents tabbable">

		<div class="tab-content paddingNoTop10 themeTableColor overflowVisible">

			<div class="tab-pane active" id="layoutDashBoards">
				<div class="btn-toolbar marginBottom10px">
					<button type="button" class="btn btn-success addBlockDashBoard btn-sm"><span class="fas fa-plus"></span>&nbsp;{\App\Language::translate('LBL_ADD_CONDITION', $QUALIFIED_MODULE)}</button>
				</div>

				<div id="moduleBlocks">
					<input type="hidden" name="filter_date" value='{\App\Json::encode($WIDGETS_WITH_FILTER_DATE)}'>
					<input type="hidden" name="filter_users" value='{\App\Json::encode($WIDGETS_WITH_FILTER_USERS)}'>
					<input type="hidden" name="filter_restrict" value='{\App\Json::encode($RESTRICT_FILTER)}'>
					{foreach key=AUTHORIZATION_KEY item=AUTHORIZATION_INFO from=$DASHBOARD_AUTHORIZATION_BLOCKS}
						{assign var=AUTHORIZATION_NAME value=$AUTHORIZATION_INFO.name}
						<div id="block_{$AUTHORIZATION_KEY}" class="editFieldsTable block_{$AUTHORIZATION_KEY} marginBottom10px border1px blockSortable" data-block-id="{$AUTHORIZATION_KEY}" data-sequence="" data-code="{$AUTHORIZATION_INFO.code}" style="border-radius: 4px 4px 0px 0px;background: white;">
							<div class="row layoutBlockHeader no-margin">
								<div class="blockLabel col-sm-5 padding10 ">
									<span class="marginLeft20">
										<strong>{\App\Language::translate($AUTHORIZATION_NAME, $SELECTED_MODULE_NAME)}</strong>
									</span>
								</div>
								<div class="col-sm-7 marginLeftZero float-right">
									<div class="float-right btn-toolbar blockActions" style="margin: 4px;">
										<div class="btn-group">
											<button class="btn btn-success btn-sm addCustomField" type="button"><span class="fas fa-plus"></span>&nbsp;
												<strong>{\App\Language::translate('LBL_ADD_WIDGET', $QUALIFIED_MODULE)}</strong>
											</button>
										</div>
										{if $SPECIAL_WIDGETS['Rss']}
											{assign var=RSS_WIDGET value=$SPECIAL_WIDGETS['Rss']}
											<div class="btn-group">
												<button class="btn btn-success btn-sm addRss" type="button"  data-url="{$RSS_WIDGET->getUrl()}" data-linkid="{$RSS_WIDGET->get('linkid')}" data-name="{$RSS_WIDGET->getName()}" data-width="{$RSS_WIDGET->getWidth()}" data-height="{$RSS_WIDGET->getHeight()}" data-block-id="{$AUTHORIZATION_KEY}"><span class="fas fa-plus"></span>
													<strong>{\App\Language::translate('LBL_ADD_RSS', $QUALIFIED_MODULE)}</strong>
												</button>
											</div>
										{/if}
										{if $SPECIAL_WIDGETS['Mini List']}
											{assign var=MINILISTWIDGET value=$SPECIAL_WIDGETS['Mini List']}
											<div class="btn-group">
												<button class="btn btn-success btn-sm addMiniList" type="button"  data-url="{$MINILISTWIDGET->getUrl()}" data-linkid="{$MINILISTWIDGET->get('linkid')}" data-name="{$MINILISTWIDGET->getName()}" data-width="{$MINILISTWIDGET->getWidth()}" data-height="{$MINILISTWIDGET->getHeight()}" data-block-id="{$AUTHORIZATION_KEY}"><span class="fas fa-plus"></span>
													<strong>{\App\Language::translate('LBL_ADD_MINILIST', $QUALIFIED_MODULE)}</strong>
												</button>
											</div>
										{/if}
										{if $SPECIAL_WIDGETS['ChartFilter']}
											{assign var=CHART_FILTER_WIDGET value=$SPECIAL_WIDGETS['ChartFilter']}
											<div class="btn-group">
												<button class="btn btn-success btn-sm addChartFilter" type="button"  data-url="{$CHART_FILTER_WIDGET->getUrl()}" data-linkid="{$CHART_FILTER_WIDGET->get('linkid')}" data-name="{$CHART_FILTER_WIDGET->getName()}" data-width="{$CHART_FILTER_WIDGET->getWidth()}" data-height="{$CHART_FILTER_WIDGET->getHeight()}" data-block-id="{$AUTHORIZATION_KEY}"><span class="fas fa-plus"></span>&nbsp;
													<strong>{\App\Language::translate('LBL_ADD_CHART_FILTER', $QUALIFIED_MODULE)}</strong>
												</button>
											</div>
										{/if}
										{if $SPECIAL_WIDGETS['Notebook']}
											{assign var=NOTEBOOKWIDGET value=$SPECIAL_WIDGETS['Notebook']}
											<div class="btn-group">
												<button class="btn btn-success btn-sm addNotebook" type="button" data-url="{$NOTEBOOKWIDGET->getUrl()}" data-linkid="{$NOTEBOOKWIDGET->get('linkid')}" data-name="{$NOTEBOOKWIDGET->getName()}" data-width="{$NOTEBOOKWIDGET->getWidth()}" data-height="{$NOTEBOOKWIDGET->getHeight()}" data-block-id="{$AUTHORIZATION_KEY}"><span class="fas fa-plus"></span>
													<strong>{\App\Language::translate('LBL_ADD_NOTEBOOK', $QUALIFIED_MODULE)}</strong>
												</button>
											</div>
										{/if}
										{if $SPECIAL_WIDGETS['Chart']}
											{assign var=CHART_WIDGET value=$SPECIAL_WIDGETS['Chart']}
											<div class="btn-group">
												<button class="btn btn-success btn-sm addCharts" type="button" data-url="{$CHART_WIDGET->getUrl()}" data-linkid="{$CHART_WIDGET->get('linkid')}" data-name="{$CHART_WIDGET->getName()}" data-width="{$CHART_WIDGET->getWidth()}" data-height="{$CHART_WIDGET->getHeight()}" data-block-id="{$AUTHORIZATION_KEY}"><span class="fas fa-plus"></span>
													<strong>{\App\Language::translate('LBL_ADD_WIDGET_CHARTS', $QUALIFIED_MODULE)}</strong>
												</button>
											</div>
										{/if}
										<div class="btn-group actions">
											<a href="javascript:void(0)" class="deleteCustomBlock btn btn-sm btn-danger" >
												<span class="fas fa-trash-alt alignMiddle" title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
											</a>
										</div>
									</div>
								</div>

							</div>
							<div class="blockFieldsList blockFieldsSortable row" style="padding:5px;min-height: 27px">
								<ul name="sortable1" class="connectedSortable col-md-6" style="list-style-type: none; min-height: 1px;padding:2px;">
									{assign var=WIDGETS_AUTHORIZATION value=$WIDGETS_AUTHORIZATION_INFO.$AUTHORIZATION_KEY}
									{foreach item=WIDGET_MODEL from=$WIDGETS_AUTHORIZATION name=fieldlist}
										{if $smarty.foreach.fieldlist.index % 2 eq 0}
											{include file=\App\Layout::getTemplatePath('WidgetConfig.tpl', $QUALIFIED_MODULE)}
										{/if}
									{/foreach}
								</ul>
								<ul name="sortable2" class="connectedSortable col-md-6" style="list-style-type: none; margin: 0; min-height: 1px;padding:2px;">
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
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header contentsBackground">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h3 class="modal-title">{\App\Language::translate('LBL_ADD_DASHBOARD_BLOCK', $QUALIFIED_MODULE)}</h3>
							</div>
							<form class="form-horizontal addBlockDashBoardForm">
								<input type="hidden" name="dashboardId" value="{$CURRENT_DASHBOARD}">
								<div class="modal-body">
									<div class="form-group">
										<div class="col-sm-4 col-form-label">
											<span>{\App\Language::translate('LBL_CHOISE_AUTHORIZED', $QUALIFIED_MODULE)}</span>
											<span class="redColor">*</span>
										</div>
										<div class="col-sm-6 controls">
											<select class="authorized form-control validateForm" name="authorized" style="margin-bottom:0px;" data-validation-engine="validate[required]">
												{foreach from=$ALL_AUTHORIZATION item=AUTHORIZED key=AUTHORIZED_CODE}
													<option value="{$AUTHORIZED_CODE}" data-label="{$AUTHORIZED->get('rolename')}">{\App\Language::translate($AUTHORIZED->get('rolename'),$QUALIFIED_MODULE)}</option>
												{/foreach}
											</select>
										</div>
									</div>
								</div>
								{include file=\App\Layout::getTemplatePath('ModalFooter.tpl', 'Vtiger')}
							</form>
						</div>
					</div>
				</div>

				<div class="newCustomBlockCopy hide marginBottom10px border1px blockSortable " data-block-id="" data-sequence="" style="border-radius: 4px 4px 0px 0px;background: white">
					<div class="row layoutBlockHeader no-margin">
						<div class="blockLabel col-md-5 padding10 ">
							<span class="marginLeft20">

							</span>
						</div>
						<div class="col-md-6 marginLeftZero" style="float:right !important;">

							<div class="float-right btn-toolbar blockActions" style="margin: 4px;">
								<div class="btn-group">
									<button class="btn btn-success btn-sm addCustomField hide" type="button"><span class="fas fa-plus"></span>&nbsp;
										<strong>{\App\Language::translate('LBL_ADD_WIDGET', $QUALIFIED_MODULE)}</strong>
									</button>
								</div>
								{if $SPECIAL_WIDGETS['Rss']}
									{assign var=RSS_WIDGET value=$SPECIAL_WIDGETS['Rss']}
									<div class="btn-group">
										<button class="btn btn-success btn-sm addRss specialWidget" type="button"  data-url="{$RSS_WIDGET->getUrl()}" data-linkid="{$RSS_WIDGET->get('linkid')}" data-name="{$RSS_WIDGET->getName()}" data-width="{$RSS_WIDGET->getWidth()}" data-height="{$RSS_WIDGET->getHeight()}" data-block-id=""><span class="fas fa-plus"></span>
											<strong>{\App\Language::translate('LBL_ADD_RSS', $QUALIFIED_MODULE)}</strong>
										</button>
									</div>
								{/if}
								{if $SPECIAL_WIDGETS['Mini List']}
									{assign var=MINILISTWIDGET value=$SPECIAL_WIDGETS['Mini List']}
									<div class="btn-group">
										<button class="btn btn-success btn-sm addMiniList specialWidget" type="button"  data-url="{$MINILISTWIDGET->getUrl()}" data-linkid="{$MINILISTWIDGET->get('linkid')}" data-name="{$MINILISTWIDGET->getName()}" data-width="{$MINILISTWIDGET->getWidth()}" data-height="{$MINILISTWIDGET->getHeight()}" data-block-id=""><span class="fas fa-plus"></span>&nbsp;
											<strong>{\App\Language::translate('LBL_ADD_MINILIST', $QUALIFIED_MODULE)}</strong>
										</button>
									</div>
								{/if}
								{if $SPECIAL_WIDGETS['ChartFilter']}
									{assign var=CHART_FILTER_WIDGET value=$SPECIAL_WIDGETS['ChartFilter']}
									<div class="btn-group">
										<button class="btn btn-success btn-sm addChartFilter specialWidget" type="button"  data-url="{$CHART_FILTER_WIDGET->getUrl()}" data-linkid="{$CHART_FILTER_WIDGET->get('linkid')}" data-name="{$CHART_FILTER_WIDGET->getName()}" data-width="{$CHART_FILTER_WIDGET->getWidth()}" data-height="{$CHART_FILTER_WIDGET->getHeight()}" data-block-id=""><span class="fas fa-plus"></span>&nbsp;
											<strong>{\App\Language::translate('LBL_ADD_CHART_FILTER', $QUALIFIED_MODULE)}</strong>
										</button>
									</div>
								{/if}
								{if $SPECIAL_WIDGETS['Notebook']}
									{assign var=NOTEBOOKWIDGET value=$SPECIAL_WIDGETS['Notebook']}
									<div class="btn-group">
										<button class="btn btn-success btn-sm addNotebook specialWidget" type="button" data-url="{$NOTEBOOKWIDGET->getUrl()}" data-linkid="{$NOTEBOOKWIDGET->get('linkid')}" data-name="{$NOTEBOOKWIDGET->getName()}" data-width="{$NOTEBOOKWIDGET->getWidth()}" data-height="{$NOTEBOOKWIDGET->getHeight()}" data-block-id=""><span class="fas fa-plus"></span>&nbsp;
											<strong>{\App\Language::translate('LBL_ADD_NOTEBOOK', $QUALIFIED_MODULE)}</strong>
										</button>
									</div>
								{/if}
								{if $SPECIAL_WIDGETS['Chart']}
									{assign var=CHART_WIDGET value=$SPECIAL_WIDGETS['Chart']}
									<div class="btn-group">
										<button class="btn btn-success btn-sm addCharts specialWidget" type="button" data-url="{$CHART_WIDGET->getUrl()}" data-linkid="{$CHART_WIDGET->get('linkid')}" data-name="{$CHART_WIDGET->getName()}" data-width="{$CHART_WIDGET->getWidth()}" data-height="{$CHART_WIDGET->getHeight()}" data-block-id="{$AUTHORIZATION_KEY}"><span class="fas fa-plus"></span>
											<strong>{\App\Language::translate('LBL_ADD_WIDGET_CHARTS', $QUALIFIED_MODULE)}</strong>
										</button>
									</div>
								{/if}
								<div class="btn-group actions">
									<a href="javascript:void(0)" class="deleteCustomBlock btn btn-sm btn-danger" >
										<span class="fas fa-trash-alt alignMiddle" title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
									</a>
								</div>
							</div>
						</div>
					</div>
					<div class="blockFieldsList row blockFieldsSortable" style="padding:5px;min-height: 27px">
						<ul class="connectedSortable col-md-6 ui-sortable" style="list-style-type: none; float: left;min-height:1px;padding:2px;" name="sortable1"></ul>
						<ul class="connectedSortable col-md-6 ui-sortable" style="list-style-type: none; margin: 0;float: left;min-height:1px;padding:2px;" name="sortable2"></ul>
					</div>
				</div>

				<div class="modal createFieldModal fade" tabindex="-1">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header contentsBackground">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h3 class="modal-title">{\App\Language::translate('LBL_CREATE_CUSTOM_FIELD', $QUALIFIED_MODULE)}</h3>
							</div>
							<form class="form-horizontal createCustomFieldForm"  method="POST">
								<div class="modal-body">
									<div class="form-group">
										<div class="col-md-3 col-form-label">
											{\App\Language::translate('LBL_SELECT_WIDGET', $QUALIFIED_MODULE)}
										</div>
										<div class="col-md-8 controls">
											<select class="widgets form-control" name="widgets" data-validation-engine="validate[required]"  >
												{foreach from=$WIDGETS item=WIDGET}
													{if array_key_exists($WIDGET->getTitle(), $SPECIAL_WIDGETS)}
														{continue}
													{/if}
													<option value="{$WIDGET->get('linkid')}" data-name="{$WIDGET->get('linklabel')}">{\App\Language::translate($WIDGET->getTitle(), $QUALIFIED_MODULE)}</option>
												{/foreach}
											</select>
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-3 col-form-label">
											{\App\Language::translate('LBL_WIDTH', $QUALIFIED_MODULE)}
										</div>
										<div class="col-sm-2">
											<select class="width form-control float-left" name="width">
												{foreach from=$SIZE.width item=item}
													<option value="{$item}" {if $DEFAULTVALUES.width eq $item} selected {/if}>{$item}</option>
												{/foreach}
											</select>
										</div>
										<div class="col-sm-3 col-form-label" style="width:135px">
											{\App\Language::translate('LBL_HEIGHT', $QUALIFIED_MODULE)}
										</div>	
										<div class="col-sm-2">
											<select class="height form-control float-left" name="height"  >
												{foreach from=$SIZE.height item=item}
													<option value="{$item}" {if $DEFAULTVALUES.height eq $item} selected {/if}>{$item}</option>
												{/foreach}
											</select>
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-3 col-form-label">
											{\App\Language::translate('LBL_MANDATORY_WIDGET', $QUALIFIED_MODULE)}
										</div>
										<div class="col-sm-2 controls">
											<input type="checkbox" name="isdefault" >
										</div>
									</div>
									<div class="form-group widgetFilter hide">
										<div class="col-sm-3 col-form-label">
											{\App\Language::translate('LBL_DEFAULT_FILTER', $QUALIFIED_MODULE)}
										</div>
										<div class="col-sm-8 controls">
											<select class="form-control" id="owner" disabled name="default_owner">
												{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT_DEFAULT}
													<option value="{$OWNER_ID}">{\App\Language::translate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
												{/foreach}
											</select>
										</div>	
									</div>	
									<div class="form-group widgetFilter hide">
										<div class="col-sm-3 col-form-label">
											{\App\Language::translate('LBL_FILTERS_AVAILABLE', $QUALIFIED_MODULE)}
										</div>
										<div class="col-sm-8 controls">
											<select class="form-control owners_all" multiple="true" disabled name="owners_all" placeholder="{\App\Language::translate('LBL_PLEASE_SELECT_ATLEAST_ONE_OPTION', $QUALIFIED_MODULE)}">
												{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT}
													<option value="{$OWNER_ID}" selected>{\App\Language::translate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
												{/foreach}
											</select>
										</div>	
									</div>
									<div class="form-group widgetFilterDate hide">
										<div class="col-sm-3 col-form-label">
											{\App\Language::translate('LBL_DEFAULT_DATE', $QUALIFIED_MODULE)}
										</div>
										<div class="col-sm-8 controls">
											<select class="form-control" id="date" disabled name="default_date">
												{foreach key=DATE_VALUE item=DATE_TEXT from=$DATE_SELECT_DEFAULT}
													<option value="{$DATE_VALUE}">{\App\Language::translate($DATE_TEXT, $QUALIFIED_MODULE)}</option>
												{/foreach}
											</select>
										</div>	
									</div>
								</div>
								{include file=\App\Layout::getTemplatePath('ModalFooter.tpl', 'Vtiger')}
							</form>
						</div>
					</div>
				</div>

				<li class="newCustomFieldCopy hide col-md-12">
					<div class="marginLeftZero border1px" data-field-id="" data-linkid="" data-sequence="">
						<div class="row padding1per">
							<div class="float-left" style="word-wrap: break-word;">
								<span class="fieldLabel marginLeft20"></span>
							</div>
							<span class="btn-group float-right marginRight20 actions">
								<a href="javascript:void(0)" class="dropdown-toggle editFieldDetails" data-toggle="dropdown">
									<span class="fas fa-pencil-alt alignMiddle" title="{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
								</a>
								<div class="basicFieldOperations hide float-right" style="width: 375px;">
									<form class="form-horizontal fieldDetailsForm" method="POST">
										<div class="modal-header contentsBackground">
										</div>
										<div class="clearfix">
											<div class="row">
												<div class="col-md-3 text-center checkboxForm">
													<input type="checkbox" name="isdefault" >
												</div>	
												<label class="col-md-9 form-control-plaintext float-left" >
													&nbsp;&nbsp;{\App\Language::translate('LBL_MANDATORY_WIDGET', $QUALIFIED_MODULE)}
												</label>
											</div>
											<div class="row">
												<div class="col-md-3 text-center checkboxForm">
													<input type="checkbox" name="cache" >
												</div>	
												<label class="col-md-9 form-control-plaintext float-left" >
													&nbsp;&nbsp;{\App\Language::translate('LBL_CACHE_WIDGET', $QUALIFIED_MODULE)}
												</label>
											</div>
											<div class="row padding1per">
												<div class="col-md-3 text-center">
													<select class="width col-md-1 float-left form-control" name="width" >
														{foreach from=$SIZE.width item=item}
															<option value="{$item}">{$item}</option>
														{/foreach}
													</select>
												</div>	
												<label  class="col-md-9 form-control-plaintext float-left" >
													&nbsp;{\App\Language::translate('LBL_WIDTH', $QUALIFIED_MODULE)}&nbsp;
												</label>
											</div>
											<div class="row padding1per">
												<div class="col-md-3 text-center">
													<select class="height col-md-1 float-left form-control" name="height">
														{foreach from=$SIZE.height item=item}
															<option value="{$item}" >{$item}</option>
														{/foreach}
													</select>
												</div>
												<label class="col-md-9 form-control-plaintext float-left" >
													&nbsp;{\App\Language::translate('LBL_HEIGHT', $QUALIFIED_MODULE)}&nbsp;
												</label>	
											</div>
											<div class="row limit padding1per">
												<div class="col-md-3 text-center" >
													<input type="text" name="limit" class="col-md-1 form-control" value="10" >
												</div>
												<label class="col-md-9 form-control-plaintext float-left" >
													&nbsp;{\App\Language::translate('LBL_NUMBER_OF_RECORDS_DISPLAYED', $QUALIFIED_MODULE)}&nbsp;
												</label>
											</div>
										</div>
										<div class="widgetFilterAll hide">
											<div class="row padding1per">
												<div class="col-md-5">
													<select class="widgetFilter form-control" id="owner" name="default_owner">
														{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT_DEFAULT}
															<option value="{$OWNER_ID}">{\App\Language::translate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
														{/foreach}
													</select>
												</div>
												<label class="col-md-6 form-control-plaintext float-left" >
													{\App\Language::translate('LBL_DEFAULT_FILTER', $QUALIFIED_MODULE)}
												</label>
											</div>	
											<div class="row padding1per">
												<div class="col-md-8">
													<select class="widgetFilter form-control" multiple="true" name="owners_all" placeholder="{\App\Language::translate('LBL_PLEASE_SELECT_ATLEAST_ONE_OPTION', $QUALIFIED_MODULE)}">
														{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT}
															<option value="{$OWNER_ID}" selected>{\App\Language::translate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
														{/foreach}
													</select>
												</div>
												<label class="col-md-3 form-control-plaintext float-left" >
													{\App\Language::translate('LBL_FILTERS_AVAILABLE', $QUALIFIED_MODULE)}
												</label>
											</div>	
											<div class="form-group hide">
												<div class="col-sm-3 col-form-label">
													{\App\Language::translate('LBL_DEFAULT_DATE', $QUALIFIED_MODULE)}
												</div>
												<div class="col-sm-8 controls">
													<select class="widgetFilterDate form-control" id="date" disabled name="default_date">
														{foreach key=DATE_VALUE item=DATE_TEXT from=$DATE_SELECT_DEFAULT}
															<option value="{$DATE_VALUE}">{\App\Language::translate($DATE_TEXT, $QUALIFIED_MODULE)}</option>
														{/foreach}
													</select>
												</div>	
											</div>
										</div>
										<div class="modal-footer">
											<span class="float-right">
												<div class="float-right"><button class='cancel btn btn-warning' type="reset">{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</button></div>
												<button class="btn btn-success saveFieldDetails" data-field-id="" type="submit"><strong>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
											</span>
										</div>
									</form>
								</div>&nbsp;
								<a href="javascript:void(0)" class="deleteCustomField" data-field-id=""><span class="fas fa-trash-alt alignMiddle" title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"></span></a>
							</span>
						</div>
					</div>
				</li>
			</div>
		</div>
	</div>
</div>
{/strip}
