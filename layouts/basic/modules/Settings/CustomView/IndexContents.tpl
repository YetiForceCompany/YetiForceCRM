{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<input id="addFilterUrl" type="hidden" value="{$MODULE_MODEL->getCreateFilterUrl($SOURCE_MODULE_ID)}"/>
	<div class="table-responsive">
		<table class="table table-striped table-bordered table-condensed listViewEntriesTable">
			<thead>
				<tr class="blockHeader">
					<th></th>
					<th><strong>{vtranslate('ViewName',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('SetDefault',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('Privileges',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('LBL_FEATURED_LABELS',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('LBL_SORTING',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('LBL_CREATED_BY',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('Actions',$QUALIFIED_MODULE)}</strong></th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$MODULE_MODEL->getCustomViews($SOURCE_MODULE_ID) item=item key=key}
					<tr data-cvid="{$key}" data-mod="{$item['entitytype']}">
						<td>
							<img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
						</td>
						{if $item['viewname'] eq 'All'}
							<td>{vtranslate('All',$item['entitytype'])}</td>
						{else}
							<td>{$item['viewname']}</td>
						{/if}
						<td>
							<input class="switchBtn updateField" type="checkbox" name="setdefault" {if $item['setdefault']}checked disabled="disabled"{/if} data-size="small" data-label-width="5" data-on-text="{vtranslate('LBL_YES')}" data-off-text="{vtranslate('LBL_NO')}" value="1">
							&nbsp;&nbsp;
							<button type="button" class="btn btn-default btn-sm showModal" data-url="{$MODULE_MODEL->getUrlDefaultUsers($SOURCE_MODULE_ID,$key, $item['setdefault'])}"><span class="glyphicon glyphicon-user"></span></button>
						</td>
						<td>
							<input class="switchBtn updateField" type="checkbox" name="privileges" {if $item['privileges']}checked{/if} data-size="small" data-label-width="5" data-on-text="{vtranslate('LBL_YES')}" data-off-text="{vtranslate('LBL_NO')}" value="1">
						</td>
						<td>
							<input class="switchBtn updateField" type="checkbox" name="featured" {if $item['featured']}checked{/if} data-size="small" data-label-width="5" data-on-text="{vtranslate('LBL_YES')}" data-off-text="{vtranslate('LBL_NO')}" value="1">
							&nbsp;&nbsp;
							<button type="button" class="btn btn-default btn-sm showModal" data-url="{$MODULE_MODEL->getFeaturedFilterUrl($SOURCE_MODULE_ID,$key)}"><span class="glyphicon glyphicon-user"></span></button>
						</td>
						<td>
							<button type="button" id="sort" name="sort" class="btn btn-default btn-sm showModal" data-url="{$MODULE_MODEL->getSortingFilterUrl($SOURCE_MODULE_ID,$key)}"><span class="glyphicon glyphicon-sort"></span></button>
						</td>
						<td>{vtlib\Functions::getOwnerRecordLabel($item['userid'])}</td>
						<td>
							<button class="btn btn-primary marginLeftZero btn-sm update" data-cvid="{$key}" data-editurl="{$MODULE_MODEL->GetUrlToEdit($item['entitytype'],$key)}">{vtranslate('Edit',$QUALIFIED_MODULE)}</button>
							{if $item['presence'] eq 1}
								<button class="btn btn-danger marginLeftZero btn-sm delete marginRight10" data-cvid="{$key}">{vtranslate('Delete',$QUALIFIED_MODULE)}</button>
							{/if}
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{/strip}
