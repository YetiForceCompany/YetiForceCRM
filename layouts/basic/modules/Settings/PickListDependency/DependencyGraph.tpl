{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <div class="accordion paddingTop20">
        <span><i class="glyphicon glyphicon-info-sign alignMiddle"></i>&nbsp;{vtranslate('LBL_CONFIGURE_DEPENDENCY_INFO', $QUALIFIED_MODULE)}&nbsp;&nbsp;</span>
        <a class="cursorPointer accordion-heading accordion-toggle" data-toggle="collapse" data-target="#dependencyHelp">{vtranslate('LBL_MORE', $QUALIFIED_MODULE)}..</a>
        <div id="dependencyHelp" class="accordion-body collapse">
            <ul><br><li>{vtranslate('LBL_CONFIGURE_DEPENDENCY_HELP_1', $QUALIFIED_MODULE)}</li><br>
                <li>{vtranslate('LBL_CONFIGURE_DEPENDENCY_HELP_2', $QUALIFIED_MODULE)}</li><br>
                <li>{vtranslate('LBL_CONFIGURE_DEPENDENCY_HELP_3', $QUALIFIED_MODULE)}&nbsp;
                    <span class="selectedCell" style="padding: 4px;">{vtranslate('Selected Values', $QUALIFIED_MODULE)}</span></li>
            </ul>
        </div>
    </div>
    <div class="">
        <span class="btn-toolbar">
            <button class="btn sourceValues btn-default" type="button"><strong>{vtranslate('LBL_SELECT_SOURCE_VALUES', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
			<button class="btn unmarkAll btn-default" type="button"><strong>{vtranslate('LBL_UNMARK_ALL', $QUALIFIED_MODULE)}</strong></button>
        </span>
    </div>
	<br>
    {assign var=SELECTED_MODULE value=$RECORD_MODEL->get('sourceModule')}
    {assign var=SOURCE_FIELD value=$RECORD_MODEL->get('sourcefield')}
    {assign var=MAPPED_SOURCE_PICKLIST_VALUES value=array()}
    {assign var=MAPPED_TARGET_PICKLIST_VALUES value=[]}
    {foreach item=MAPPING from=$MAPPED_VALUES}
        {assign var=value value=array_push($MAPPED_SOURCE_PICKLIST_VALUES, $MAPPING['sourcevalue'])}
        {$MAPPED_TARGET_PICKLIST_VALUES[$MAPPING['sourcevalue']] = $MAPPING['targetvalues']}
    {/foreach}
    <input type="hidden" class="allSourceValues" value='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($SOURCE_PICKLIST_VALUES))}' />

    <div class="row depandencyTable no-margin">
        <div class="col-md-2 col-sm-2 col-xs-2 paddingRightZero">
            <table class="table-condensed themeTableColor" width="100%">
                <thead>
                    <tr class="blockHeader"><th>{$RECORD_MODEL->getSourceFieldLabel()}</th></tr>
                </thead>
                <tbody>
                    {foreach item=TARGET_VALUE from=$TARGET_PICKLIST_VALUES name=targetValuesLoop}
				{if $smarty.foreach.targetValuesLoop.index eq 0}
					<tr>
						<td class="tableHeading">
							{$RECORD_MODEL->getTargetFieldLabel()}
						</td>
					</tr>
				{/if}
		    {/foreach}
		</tbody>
            </table>
        </div>
        <div class="col-md-10 col-sm-10 col-xs-10 paddingLRZero marginLeftZero dependencyMapping">
            <table class="table-bordered table-condensed themeTableColor pickListDependencyTable">
                <thead><tr class="blockHeader">
                        {foreach item=SOURCE_PICKLIST_VALUE from=$SOURCE_PICKLIST_VALUES}
                            <th data-source-value="{Vtiger_Util_Helper::toSafeHTML($SOURCE_PICKLIST_VALUE)}" style="
								{if !empty($MAPPED_VALUES) && !in_array($SOURCE_PICKLIST_VALUE, array_map('decode_html', $MAPPED_SOURCE_PICKLIST_VALUES))}display: none;{/if}">
								{vtranslate($SOURCE_PICKLIST_VALUE, $SELECTED_MODULE)}</th>
						{/foreach}</tr>
				</thead>
				<tbody>
					{foreach key=TARGET_INDEX item=TARGET_VALUE from=$TARGET_PICKLIST_VALUES name=targetValuesLoop}
						<tr>
							{foreach item=SOURCE_PICKLIST_VALUE from=$SOURCE_PICKLIST_VALUES}
								{assign var=targetValues value=$MAPPED_TARGET_PICKLIST_VALUES[Vtiger_Util_Helper::toSafeHTML($SOURCE_PICKLIST_VALUE)]}

								{assign var=SOURCE_INDEX value=$smarty.foreach.mappingIndex.index}
								{assign var=IS_SELECTED value=false}

								{if empty($targetValues) || in_array($TARGET_VALUE, array_map('decode_html',$targetValues))}
									{assign var=IS_SELECTED value=true}
								{/if}
								<td	data-source-value='{Vtiger_Util_Helper::toSafeHTML($SOURCE_PICKLIST_VALUE)}' data-target-value='{Vtiger_Util_Helper::toSafeHTML($TARGET_VALUE)}'
									class="{if $IS_SELECTED}selectedCell {else}unselectedCell {/if} targetValue picklistValueMapping cursorPointer"
									{if !empty($MAPPED_VALUES) && !in_array($SOURCE_PICKLIST_VALUE, array_map('decode_html', $MAPPED_SOURCE_PICKLIST_VALUES))}style="display: none;" {/if}>
									{if $IS_SELECTED}
										<i class="glyphicon glyphicon-ok pull-left"></i>
									{/if}
									{vtranslate($TARGET_VALUE, $SELECTED_MODULE)}
								</td>
							{/foreach}
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
	<div class="modal sourcePicklistValuesModal modalCloneCopy fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 class="modal-title">{vtranslate('LBL_SELECT_SOURCE_PICKLIST_VALUES', $QUALIFIED_MODULE)}</h3>
				</div>
				<div class="modal-body">
					<div class="row no-margin">
						<table class="col-md-12" cellspacing="0" cellpadding="5">
							<tr>
								{foreach key=SOURCE_INDEX item=SOURCE_VALUE from=$SOURCE_PICKLIST_VALUES name=sourceValuesLoop}
									{if $smarty.foreach.sourceValuesLoop.index % 3 == 0}
									</tr><tr>
									{/if}
									<td>
										<div class="form-group">
											<div class="controls checkbox">
												<label class=""><input type="checkbox" class="sourceValue {Vtiger_Util_Helper::toSafeHTML($SOURCE_VALUE)}"
																	   data-source-value="{Vtiger_Util_Helper::toSafeHTML($SOURCE_VALUE)}" value="{Vtiger_Util_Helper::toSafeHTML($SOURCE_VALUE)}" 
																	   {if empty($MAPPED_VALUES) || in_array($SOURCE_VALUE, array_map('decode_html', $MAPPED_SOURCE_PICKLIST_VALUES))} checked {/if}/>
													&nbsp;{vtranslate($SOURCE_VALUE, $SELECTED_MODULE)}</label>
											</div>
										</div>
									</td>
								{/foreach}
							</tr>
						</table>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
			</div>
		</div>
	</div>
	<div class="padding1per">
		<div class="btn-toolbar  pull-right">
			<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
			<a type="reset" class="cancelLink cancelDependency btn btn-warning" title="{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
		</div>
		<br><br>
	</div>
{/strip}
