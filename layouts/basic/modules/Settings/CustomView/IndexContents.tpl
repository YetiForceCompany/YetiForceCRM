{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-CustomView-IndexContents">
		<input id="js-add-filter-url" type="hidden" data-js="value"
			   value="{$MODULE_MODEL->getCreateFilterUrl($SOURCE_MODULE_ID)}"/>
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm listViewEntriesTable">
				<thead>
				<tr class="blockHeader">
					<th></th>
					<th>{\App\Language::translate('ViewName',$QUALIFIED_MODULE)}</th>
					<th>{\App\Language::translate('SetDefault',$QUALIFIED_MODULE)}</th>
					<th>{\App\Language::translate('Privileges',$QUALIFIED_MODULE)}</th>
					<th>{\App\Language::translate('LBL_FEATURED_LABELS',$QUALIFIED_MODULE)}</th>
					<th>{\App\Language::translate('LBL_SORTING',$QUALIFIED_MODULE)}</th>
					<th>{\App\Language::translate('LBL_CREATED_BY',$QUALIFIED_MODULE)}</th>
					<th>{\App\Language::translate('Actions',$QUALIFIED_MODULE)}</th>
				</tr>
				</thead>
				<tbody>
				{foreach from=$MODULE_MODEL->getCustomViews($SOURCE_MODULE_ID) item=item key=key}
					<tr class="js-filter-row" data-js="data" data-cvid="{$key}" data-mod="{$item['entitytype']}">
						<td>
							<img src="{\App\Layout::getImagePath('drag.png')}"
								 title="{\App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
						</td>
						{if $item['viewname'] eq 'All'}
							<td>{\App\Language::translate('All',$item['entitytype'])}</td>
						{else}
							<td>{$item['viewname']}</td>
						{/if}
						<td>
							<div class="btn-group btn-group-toggle {if $item['setdefault']} u-disabled{/if}"
								 data-toggle="buttons">
								<label class="btn btn-sm btn-outline-primary {if $item['setdefault']} active{/if}">
									<input class="js-update-field" type="radio" name="setdefault"
										   id="setdefault1" autocomplete="off" value="1"
										   {if $item['setdefault']}checked{/if}
									> {\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
								</label>
								<label class="btn btn-sm btn-outline-primary {if !$item['setdefault']} active {/if}">
									<input class="js-update-field" type="radio" name="setdefault"
										   id="setdefault2" autocomplete="off" value="0"
										   {if !$item['setdefault']}checked{/if}
									> {\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
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
										   {if $item['privileges']}checked{/if}
									> {\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
								</label>
								<label class="btn btn-sm btn-outline-primary {if !$item['privileges']} active {/if}">
									<input class="js-switch js-update-field" type="radio" name="privileges"
										   data-js="change"
										   id="privileges2" autocomplete="off" value="0"
										   {if !$item['privileges']}checked{/if}
									> {\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
								</label>
							</div>
						</td>
						<td>
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-sm btn-outline-primary {if $item['featured']} active{/if}">
									<input class="js-update-field" data-js="change" type="radio" name="featured"
										   id="featured1" autocomplete="off" value="1"
										   {if $item['featured']}checked{/if}
									> {\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
								</label>
								<label class="btn btn-sm btn-outline-primary {if !$item['featured']} active {/if}">
									<input class="js-update-field" data-js="change" type="radio" name="featured"
										   id="featured2" autocomplete="off" value="0"
										   {if !$item['featured']}checked{/if}
									> {\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
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
