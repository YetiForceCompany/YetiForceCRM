{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-CustomView-IndexContents">
		<input id="js-add-filter-url" type="hidden" data-js="value"
			value="{$MODULE_MODEL->getCreateFilterUrl($SOURCE_MODULE_ID)}" />
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm listViewEntriesTable">
				<thead>
					<tr class="blockHeader">
						<th></th>
						<th>{\App\Language::translate('ViewName',$QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('SetDefault',$QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_PRIVILEGES_TO_EDIT',$QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_PRIVILEGES_TO_VIEW',$QUALIFIED_MODULE)}
							<a href="#" class="js-popover-tooltip ml-2" data-placement="top" data-content="{\App\Language::translate('LBL_PRIVILEGES_TO_VIEW_DESC', $QUALIFIED_MODULE)}">
								<i class="fas fa-info-circle"></i>
							</a>
						</th>
						<th>{\App\Language::translate('LBL_FEATURED_LABELS',$QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_SORTING',$QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_CREATED_BY',$QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('Actions',$QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=\App\CustomView::getFiltersByModule($SOURCE_MODULE) item=item key=key}
						{if $item['presence'] === 2}{continue}{/if}
						<tr class="js-filter-row" data-js="data" data-cvid="{$key}" data-mod="{$SOURCE_MODULE}">
							<td>
								<img src="{\App\Layout::getImagePath('drag.png')}"
									title="{\App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}" />
							</td>
							{if $item['viewname'] eq 'All'}
								<td>{\App\Language::translate('All', $SOURCE_MODULE)}</td>
							{else}
								<td>{$item['viewname']}</td>
							{/if}
							<td>
								<div class="btn-group btn-group-toggle {if $item['setdefault']} u-disabled{/if}"
									data-toggle="buttons">
									<label class="btn btn-sm btn-outline-primary {if $item['setdefault']} active{/if}">
										<input class="js-update-field" type="radio" name="setdefault"
											id="setdefault1" autocomplete="off" value="1"
											{if $item['setdefault']}checked{/if}> {\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
									</label>
									<label class="btn btn-sm btn-outline-primary {if !$item['setdefault']} active {/if}">
										<input class="js-update-field" type="radio" name="setdefault"
											id="setdefault2" autocomplete="off" value="0"
											{if !$item['setdefault']}checked{/if}> {\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
									</label>
								</div>
								<button type="button" class="btn btn-light btn-sm showModal"
									data-url="{$MODULE_MODEL->getUrlDefaultUsers($SOURCE_MODULE_ID,$key, $item['setdefault'])}">
									<span class="fas fa-user"></span></button>
							</td>
							<td>
								<div class="btn-group btn-group-toggle"
									data-toggle="buttons">
									<label class="btn btn-sm btn-outline-primary {if $item['privileges']} active{/if}">
										<input class="js-switch js-update-field" type="radio" name="privileges"
											data-js="change"
											id="privileges1" autocomplete="off" value="1"
											{if $item['privileges']}checked{/if}> {\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
									</label>
									<label class="btn btn-sm btn-outline-primary {if !$item['privileges']} active {/if}">
										<input class="js-switch js-update-field" type="radio" name="privileges"
											data-js="change"
											id="privileges2" autocomplete="off" value="0"
											{if !$item['privileges']}checked{/if}> {\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
									</label>
								</div>
							</td>
							<td>
								{assign 'IS_PRIVATE'  $item['status'] === \App\CustomView::CV_STATUS_PRIVATE}
								{assign 'IS_PUBLIC'  $item['status'] === \App\CustomView::CV_STATUS_PUBLIC || $item['presence'] === 0}
								<div class="btn-group btn-group-toggle {if $item['presence'] === 0} u-disabled{/if}"
									data-toggle="buttons">
									<label class="btn btn-sm btn-outline-primary {if $IS_PUBLIC} active{/if}">
										<input class="js-update-field" type="radio" name="status"
											id="status1" autocomplete="off" value="{\App\CustomView::CV_STATUS_PUBLIC}"
											{if $IS_PUBLIC}checked{/if}> {\App\Language::translate('LBL_PUBLIC', $QUALIFIED_MODULE)}
									</label>
									<label class="btn btn-sm btn-outline-primary {if $IS_PRIVATE} active {/if}">
										<input class="js-update-field" type="radio" name="status"
											id="status2" autocomplete="off" value="{\App\CustomView::CV_STATUS_PRIVATE}"
											{if $IS_PRIVATE}checked{/if}> {\App\Language::translate('LBL_PRIVATE', $QUALIFIED_MODULE)}
									</label>
								</div>
								{if $IS_PRIVATE}
									<button type="button" id="permissions" name="permissions" class="btn btn-light btn-sm js-show-modal"
										data-url="{$MODULE_MODEL->getPrivilegesUrl($SOURCE_MODULE_ID, $key)}">
										<span class="fas fa-user"></span>
									</button>
								{/if}
							</td>
							<td>
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-sm btn-outline-primary {if $item['featured']} active{/if}">
										<input class="js-update-field" data-js="change" type="radio" name="featured"
											id="featured1" autocomplete="off" value="1"
											{if $item['featured']}checked{/if}> {\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
									</label>
									<label class="btn btn-sm btn-outline-primary {if !$item['featured']} active {/if}">
										<input class="js-update-field" data-js="change" type="radio" name="featured"
											id="featured2" autocomplete="off" value="0"
											{if !$item['featured']}checked{/if}> {\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
									</label>
								</div>
								<button type="button" class="btn btn-light btn-sm showModal"
									data-url="{$MODULE_MODEL->getFeaturedFilterUrl($SOURCE_MODULE_ID,$key)}"><span
										class="fas fa-user"></span></button>
							</td>
							<td>
								<button type="button" id="sort" name="sort" class="btn btn-light btn-sm showModal"
									data-url="{$MODULE_MODEL->getSortingFilterUrl($SOURCE_MODULE_ID,$key)}"><span
										class="fas fa-sort"></span></button>
							</td>
							<td>{\App\Fields\Owner::getLabel($item['userid'])}</td>
							<td>
								<button class="btn btn-primary btn-sm js-update mr-1" data-js="click" data-cvid="{$key}"
									data-editurl="{$MODULE_MODEL->getUrlToEdit($item['entitytype'],$key)}">
									<span class="fa fa-edit u-mr-5px"></span>{\App\Language::translate('Edit',$QUALIFIED_MODULE)}
								</button>
								{if $item['presence'] eq 1}
									<button class="btn btn-danger btn-sm mr-2 js-delete-filter" data-js="click"
										data-cvid="{$key}">
										<span class="fa fa-trash u-mr-5px"></span>{\App\Language::translate('Delete',$QUALIFIED_MODULE)}
									</button>
								{/if}
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
{/strip}
