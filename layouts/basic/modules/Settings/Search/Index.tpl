{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="MODULESENTITY" value=Settings_Search_Module_Model::getModulesEntity(false, true)}
	{assign var="FIELDS_MODULES" value=Settings_Search_Module_Model::getFieldFromModule()}
	<div class="tpl-Settings-Search-Index SearchFieldsEdit">
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="btn-toolbar">
			<span class="float-right group-desc">
				<button class="btn btn-success saveModuleSequence d-none mt-2" type="button">
					<strong>{\App\Language::translate('LBL_SAVE_MODULE_SEQUENCE', $QUALIFIED_MODULE)}</strong>
				</button>
			</span>
			<div class="clearfix"></div>
		</div>
		<div class="contents tabbable table-responsive">
			<table class="table table-responsive table-bordered table-sm listViewEntriesTable my-2" id="modulesEntity">
				<thead>
					<tr class="blockHeader">
						<th class="noWrap">{\App\Language::translate('Module',$QUALIFIED_MODULE)}</th>
						<th data-hide="phone">{\App\Language::translate('LabelFields',$QUALIFIED_MODULE)}</th>
						<th data-hide="phone">{\App\Language::translate('SearchFields',$QUALIFIED_MODULE)}</th>
						<th data-hide="tablet" colspan="3">{\App\Language::translate('Tools',$QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$MODULESENTITY item=item key=KEY}
						{assign var="BLOCKS" value=$FIELDS_MODULES[$KEY]}
						<tr data-tabid="{$KEY}">
							<td class="alignMiddle widthMin noWrap"><span>&nbsp;
									<a>
										<img src="{\Vtiger_Theme::getImagePath('drag.png')}" alt="{\App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}" />
									</a>&nbsp;
								</span>
								<strong>{\App\Language::translate($item['modulename'],$item['modulename'])}</strong>
							</td>
							<td class="alignMiddle">
								<div class="elementLabels{$KEY} paddingLR5">
									{assign var="VALUE" value=explode(',',$item['fieldname'])}
									{foreach from=$VALUE item=NAME name=valueLoop}
										{foreach key=BLOCK_NAME item=FIELDS from=$BLOCKS}
											{if isset($FIELDS[$NAME])}
												{\App\Language::translate($FIELDS[$NAME]['fieldlabel'],$item['modulename'])}
											{/if}
										{/foreach}
										{if !$smarty.foreach.valueLoop.last},&nbsp;{/if}
									{/foreach}
								</div>
								<div class="d-none elementEdit{$KEY}">
									<select multiple class="form-control fieldname" data-select-cb="registerSelectSortable" data-js="sortable | select2" name="fieldname" data-tabid="{$KEY}">
										{foreach key=BLOCK_NAME item=FIELDS from=$BLOCKS}
											<optgroup label="{\App\Language::translate($BLOCK_NAME, $KEY)}">
												{foreach from=$FIELDS item=fieldTab}
													<option value="{$fieldTab['columnname']}" {if in_array($fieldTab['columnname'],$VALUE)}selected{/if}>
														{\App\Language::translate($fieldTab['fieldlabel'],$item['modulename'])}
													</option>
												{/foreach}
											</optgroup>
										{/foreach}
									</select>
								</div>
							</td>
							<td class="alignMiddle">
								<div class="elementLabels{$KEY} paddingLR5">
									{assign var="VALUE" value=explode(',',$item['searchcolumn'])}
									{foreach from=$VALUE item=NAME name=valueLoop}
										{foreach key=BLOCK_NAME item=FIELDS from=$BLOCKS}
											{if isset($FIELDS[$NAME])}
												{\App\Language::translate($FIELDS[$NAME]['fieldlabel'],$item['modulename'])}
											{/if}
										{/foreach}
										{if !$smarty.foreach.valueLoop.last},&nbsp;{/if}
									{/foreach}
								</div>
								<div class="d-none elementEdit{$KEY}">
									<select multiple class="form-control searchcolumn" data-select-cb="registerSelectSortable"
										data-js="sortable | select2" name="searchcolumn" data-tabid="{$KEY}">
										{foreach key=BLOCK_NAME item=FIELDS from=$BLOCKS}
											<optgroup label="{\App\Language::translate($BLOCK_NAME, $KEY)}">
												{foreach from=$FIELDS item=fieldTab}
													<option value="{$fieldTab['columnname']}" {if in_array($fieldTab['columnname'],$VALUE)}selected{/if}>
														{\App\Language::translate($fieldTab['fieldlabel'],$item['modulename'])}
													</option>
												{/foreach}
											</optgroup>
										{/foreach}
									</select>
								</div>
							</td>
							<td class="alignMiddle widthMin">
								<button class="btn editLabels btn-info noWrap" data-tabid="{$KEY}">
									<span class="fa fa-edit u-mr-5px"></span>{\App\Language::translate('LBL_EDIT',$QUALIFIED_MODULE)}
								</button>
							</td>
							<td class="alignMiddle widthMin">
								<button class="btn updateLabels btn-primary noWrap" data-tabid="{$KEY}"><span
										class="fas fa-exchange-alt u-mr-5px"></span>{\App\Language::translate('Update labels',$QUALIFIED_MODULE)}
								</button>
							</td>
							<td class="alignMiddle widthMin">
								<button name="turn_off" class="noWrap btn turn_off {if $item['turn_off'] eq 1}btn-danger{else}btn-success{/if}" value="{$item['turn_off']}" data-tabid="{$KEY}">
									<span class="fas fa-power-off u-mr-5px"></span>{if $item['turn_off'] eq 1}
									{\App\Language::translate('LBL_TURN_OFF',$QUALIFIED_MODULE)}{else}{\App\Language::translate('LBL_TURN_ON',$QUALIFIED_MODULE)}
									{/if}
								</button>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		<div class="clearfix"></div>
{/strip}
