{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="filterAll" value='{\App\Purifier::encodeHtml($FILTERS)}'>
	<input type="hidden" id="checkboxAll" value='{\App\Purifier::encodeHtml($CHECKBOXS)}'>
	<input type="hidden" id="switchHeaderAll" value='{\App\Purifier::encodeHtml($SWITCHES_HEADER)}'>
	<input type="hidden" id="customView" value='{\App\Purifier::encodeHtml($CUSTOM_VIEW)}'>
	<div class="WidgetsManage">
		<input type="hidden" name="tabid" value="{$SOURCE}">
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
			<div class="float-right col-md-4 mt-2">
				<select class="select2 col-md-3 form-control js-module__list" data-js="change|value" name="ModulesList">
					{foreach from=$MODULE_MODEL->getModulesList() item=item key=key}
						<option value="{$key}" {if $SOURCE eq $key}selected{/if}>{\App\Language::translate($item['tablabel'], $item['name'])}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row my-2">
			<div class="col-md-8">
				<h4>{\App\Language::translate('List of widgets for the module', $QUALIFIED_MODULE)}
					: {\App\Language::translate($SOURCEMODULE, $SOURCEMODULE)}</h4>
			</div>
			<div class="col-md-4">
				<button class="btn btn-success js-widget__add float-md-right float-left" data-js="click" type="button">
					<i class="fas fa-plus mr-1"></i><strong>{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}</strong>
				</button>
			</div>
			<div class="clearfix"></div>
		</div>
		<div class="blocks-content padding1per">
			<div class="row">
				{foreach from=$WIDGETS item=WIDGETCOL key=column}
					<div class="blocksSortable col-md-4" data-column="{$column}">
						{foreach from=$WIDGETCOL item=WIDGET key=key}
							<div class="blockSortable" data-id="{$key}">
								<div class="padding1per border1px">
									<div class="row">
										<div class="col-md-4">
											<img class="alignMiddle" src="{\App\Layout::getImagePath('drag.png')}" /> &nbsp;&nbsp;{\App\Language::translate($WIDGET['type'], $QUALIFIED_MODULE)}
										</div>
										<div class="col-md-4">
											{if $WIDGET['label'] eq '' && isset($WIDGET['data']['relatedmodule'])}
												{\App\Language::translate(\App\Module::getModuleName($WIDGET['data']['relatedmodule']),\App\Module::getModuleName($WIDGET['data']['relatedmodule']))}
											{elseif $WIDGET['label']}
												{\App\Language::translate($WIDGET['label'], $SOURCEMODULE)}&nbsp;
											{/if}
										</div>
										<div class="col-md-4">
											<span class="float-right">
												<button class="btn btn-sm btn-primary js-widget__edit mr-1" data-js="click">
													<i class="u-cursor-pointer yfi yfi-full-editing-view" title="{\App\Language::translate('Edit', $QUALIFIED_MODULE)}"></i>
												</button>
												<button class="btn btn-sm btn-danger js-widget__remove" data-js="click">
													<i class="u-cursor-pointer fas fa-times" title="{\App\Language::translate('Remove', $QUALIFIED_MODULE)}"></i>
												</button>
											</span>
										</div>
									</div>
								</div>
							</div>
						{/foreach}
					</div>
				{/foreach}
			</div>
		</div>
		<div class="clearfix"></div>
{/strip}
