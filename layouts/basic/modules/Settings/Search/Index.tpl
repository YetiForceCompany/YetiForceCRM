{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="MODULESENTITY" value=Settings_Search_Module_Model::getModulesEntity(false, true)}
	{assign var="FIELDS_MODULES" value=Settings_Search_Module_Model::getFieldFromModule()}
	<div class="tpl-Settings-Search-Index SearchFieldsEdit">
		<div class="widget_header row">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				{\App\Language::translate('LBL_Module_desc', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="btn-toolbar">
			<span class="float-right group-desc ">
				<button class="btn btn-success saveModuleSequence d-none" type="button">
					<strong>{\App\Language::translate('LBL_SAVE_MODULE_SEQUENCE', $QUALIFIED_MODULE)}</strong>
				</button>
			</span>
			<div class="clearfix"></div>
		</div>
		<div class="contents tabbable table-responsive">
			<table class="table customTableRWD table-bordered table-sm listViewEntriesTable" id="modulesEntity">
				<thead>
					<tr class="blockHeader">
						<th class="noWrap"><strong>{\App\Language::translate('Module',$QUALIFIED_MODULE)}</strong></th>
						<th data-hide='phone' class="noWrap"><strong>{\App\Language::translate('LabelFields',$QUALIFIED_MODULE)}</strong></th>
						<th data-hide='phone' class="noWrap"><strong>{\App\Language::translate('SearchFields',$QUALIFIED_MODULE)}</strong></th>
						<th data-hide='tablet' colspan="3" class="noWrap"><strong>{\App\Language::translate('Tools',$QUALIFIED_MODULE)}</strong></th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$MODULESENTITY item=item key=KEY}
						{assign var="FIELDS" value=$FIELDS_MODULES[$KEY]}
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
										{\App\Language::translate($FIELDS[$NAME]['fieldlabel'],$item['modulename'])}
										{if !$smarty.foreach.valueLoop.last},&nbsp;{/if}
									{/foreach}
								</div>
								<div class="d-none elementEdit{$KEY}">
									<select multiple class="form-control fieldname" name="fieldname" data-tabid="{$KEY}">
										{foreach from=$FIELDS item=fieldTab}
											<option value="{$fieldTab['columnname']}" {if in_array($fieldTab['columnname'],$VALUE)}selected{/if}>
												{\App\Language::translate($fieldTab['fieldlabel'],$item['modulename'])}
											</option>
										{/foreach}
									</select>
								</div>
							</td>
							<td class="alignMiddle">
								<div class="elementLabels{$KEY} paddingLR5">
									{assign var="VALUE" value=explode(',',$item['searchcolumn'])}
									{foreach from=$VALUE item=NAME name=valueLoop}
										{\App\Language::translate($FIELDS[$NAME]['fieldlabel'],$item['modulename'])}
										{if !$smarty.foreach.valueLoop.last},&nbsp;{/if}
									{/foreach}
								</div>
								<div class="d-none elementEdit{$KEY}">
									<select multiple class="form-control searchcolumn" name="searchcolumn" data-tabid="{$KEY}">
										{foreach from=$FIELDS item=fieldTab }
											<option value="{$fieldTab['columnname']}" {if in_array($fieldTab['columnname'],$VALUE)}selected{/if}>
												{\App\Language::translate($fieldTab['fieldlabel'],$item['modulename'])}
											</option>
										{/foreach}
									</select>
								</div>
							</td>
							<td class="alignMiddle widthMin">
								<button class="btn editLabels btn-info" data-tabid="{$KEY}"><span class="fa fa-edit u-mr-5px"></span>{\App\Language::translate('LBL_EDIT',$QUALIFIED_MODULE)}</button>
							</td>
							<td class="alignMiddle widthMin">
								<button class="btn updateLabels btn-primary noWrap" data-tabid="{$KEY}"><span
											class="fas fa-exchange-alt u-mr-5px"></span>{\App\Language::translate('Update labels',$QUALIFIED_MODULE)}</button>
							</td>
							<td class="alignMiddle widthMin">
								<button name="turn_off" class="noWrap btn turn_off {if $item['turn_off'] eq 1}btn-danger{else}btn-success{/if}" value="{$item['turn_off']}" data-tabid="{$KEY}">
									<span class="fas fa-power-off u-mr-5px"></span>{if $item['turn_off'] eq 1}
										{\App\Language::translate('LBL_TURN_OFF',$QUALIFIED_MODULE)}{else}{\App\Language::translate('LBL_TURN_ON',$QUALIFIED_MODULE)}{/if}</button>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		<div class="clearfix"></div>
	{/strip}
