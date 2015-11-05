{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
<div class="" id="salesProcessesContainer">
 	{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
	{vtranslate('LBL_SALES_PROCESSES_DESCRIPTION', $QUALIFIED_MODULE)}
	<hr>
	<ul id="tabs" class="nav nav-tabs layoutTabs massEditTabs" data-tabs="tabs">
		<li class="active"><a href="#Potentials" data-toggle="tab">{vtranslate('LBL_POTENTIALS', $QUALIFIED_MODULE)} </a></li>
		<li><a href="#qe" data-toggle="tab">{vtranslate('LBL_SQUOTEENQUIRIES', $QUALIFIED_MODULE)} </a></li>
		<li><a href="#rc" data-toggle="tab">{vtranslate('LBL_SREQUIREMENTSCARD', $QUALIFIED_MODULE)} </a></li>
		<li><a href="#calculations" data-toggle="tab">{vtranslate('LBL_SCALCULATIONS', $QUALIFIED_MODULE)} </a></li>
		<li><a href="#quotes" data-toggle="tab">{vtranslate('LBL_SQUOTES', $QUALIFIED_MODULE)} </a></li>
		<li><a href="#so" data-toggle="tab">{vtranslate('LBL_SSALESORDER', $QUALIFIED_MODULE)} </a></li>
		<li><a href="#Assets" data-toggle="tab">{vtranslate('LBL_ASSETS', $QUALIFIED_MODULE)} </a></li>
		<li><a href="#popup" data-toggle="tab">{vtranslate('LBL_PRODUCTS_AND_SERVICES_POPUP', $QUALIFIED_MODULE)} </a></li>
	</ul>
	{assign var=CONFIG value=$MODULE_MODEL->getConfig()}
	<div class="tab-content layoutContent">
		<div class="tab-pane" id="popup">
			{assign var=POPUP value=$CONFIG['popup']}
			<div class="row">
				<div class="col-md-1 col-sm-1 col-xs-2 textAlignCenter"><input class="configField" type="checkbox" data-type="popup" name="limit_product_service" id="limit_product_service" value="1"  {if $POPUP['limit_product_service']=='true'}checked=""{/if} /></div>
				<div class="col-md-11 col-sm-11 col-xs-10"><label for="limit_product_service">{vtranslate('LBL_LIMIT_PRODUCT_AND_SERVICE',$QUALIFIED_MODULE)}</label></div>
			</div>
			<div class="row">
				<div class="col-md-1 col-sm-1 col-xs-2 textAlignCenter"><input class="configField" type="checkbox" data-type="popup" name="update_shared_permissions" id="update_shared_permissions" value="1"  {if $POPUP['update_shared_permissions']=='true'}checked=""{/if} /></div>
				<div class="col-md-11 col-sm-11 col-xs-10"><label for="update_shared_permissions">{vtranslate('LBL_UPDATE_SHARED_PERMISSIONS',$QUALIFIED_MODULE)}</label></div>
			</div>
		</div>
		<div class="tab-pane" id="calculations">
			{assign var=SCALCULATIONS value=$CONFIG['scalculations']}
			<div class="form-horizonta">
				<div class="form-group col-md-5">
					<label class='control-label'>{vtranslate('LBL_STATUSES_CLOSED_CALCULATION', $QUALIFIED_MODULE)}</label>
					<div class="">
						<select class="selectize configField" multiple data-type="scalculations" name="statuses_close" placeholder='{vtranslate('LBL_SELECT_SOME_OPTIONS')}'>
							{foreach  item=ITEM from=Vtiger_Util_Helper::getPickListValues('calculationsstatus')}
								<option value="{$ITEM}" {if in_array($ITEM, $SCALCULATIONS['statuses_close'])} selected {/if}>{vtranslate($ITEM,'Calculations')}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-pane active" id="Potentials">
			{assign var=POTENTIALS value=$CONFIG['potential']}
			<table class="table table-bordered table-condensed themeTableColor userTable listViewEntries">
				<tbody>
					<tr>
						<td><label for="add_potential">{vtranslate('LBL_CREATE_POTENTIALS',$QUALIFIED_MODULE)}</label></td>
						<td><input class="configField" type="checkbox" data-type="potential" name="add_potential" id="add_potential" value="1"  {if $POTENTIALS['add_potential']=='true'}checked=""{/if} /></td>
					</tr>
					<tr>
						<td><label>{vtranslate('LBL_STATUSES_CLOSED_POTENTIAL', $QUALIFIED_MODULE)}</label></td>
						<td class="col-xs-6">
							<select class="chzn-select col-md-8 configField" multiple data-type="potential" name="salesstage">
								{foreach  item=ITEM from=Vtiger_Util_Helper::getPickListValues('sales_stage')}
									<option value="{$ITEM}" {if in_array($ITEM, $POTENTIALS['salesstage'])} selected {/if}  >{vtranslate($ITEM,'Potentials')}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="tab-pane" id="Assets">
			{assign var=ASSETS value=$CONFIG['asset']}
			<table class="table table-bordered table-condensed themeTableColor userTable listViewEntries">
				<tbody>
					<tr>
						<td><label>{vtranslate('LBL_STATUSES_CLOSED_ASSETS', $QUALIFIED_MODULE)}</label></td>
						<td class="col-xs-6">
							<select class="chzn-select col-md-8 configField" multiple data-type="asset" name="assetstatus">
								{foreach  item=ITEM from=Vtiger_Util_Helper::getPickListValues('assetstatus')}
									<option value="{$ITEM}" {if in_array($ITEM, $ASSETS['assetstatus'])} selected {/if}>{vtranslate($ITEM,'Assets')}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="tab-pane" id="qe">
			{assign var=SQUOTEENQUIRIES value=$CONFIG['squoteenquiries']}
			<div class="form-horizonta">
				<div class="form-group col-md-5 ">
					<label class='control-label'>{vtranslate('LBL_STATUSES_CLOSED_SQUOTEENQUIRIES', $QUALIFIED_MODULE)}</label>
					<div class="">
						<select class="selectize configField" multiple data-type="squoteenquiries" name="statuses_close"  placeholder='{vtranslate('LBL_SELECT_SOME_OPTIONS')}'>
							{foreach  item=ITEM from=Vtiger_Util_Helper::getPickListValues('quotesenquires_stage')}
								<option value="{$ITEM}" {if in_array($ITEM, $SQUOTEENQUIRIES['statuses_close'])} selected {/if}>{vtranslate($ITEM,'QuotesEnquires')}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-pane" id="rc">
			{assign var=SREQUIREMENTSCARD value=$CONFIG['srequirementscard']}
			<div class="form-horizonta">
				<div class="form-group col-md-5">
					<label class='control-label'>{vtranslate('LBL_STATUSES_CLOSED_SREQUIREMENTSCARD', $QUALIFIED_MODULE)}</label>
					<div class="">
						<select class="selectize configField" multiple data-type="srequirementscard" name="statuses_close"  placeholder='{vtranslate('LBL_SELECT_SOME_OPTIONS')}'>
							{foreach  item=ITEM from=Vtiger_Util_Helper::getPickListValues('requirementcards_status')}
								<option value="{$ITEM}" {if in_array($ITEM, $SREQUIREMENTSCARD['statuses_close'])} selected {/if}>{vtranslate($ITEM,'RequirementCards')}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-pane" id="quotes">
			{assign var=SQUOTES value=$CONFIG['squotes']}
			<div class="form-horizonta">
				<div class="form-group col-md-5">
					<label class='control-label'>{vtranslate('LBL_STATUSES_CLOSED_SQUOTES', $QUALIFIED_MODULE)}</label>
					<div class="">
						<select class="selectize configField" multiple data-type="squotes" name="statuses_close"  placeholder='{vtranslate('LBL_SELECT_SOME_OPTIONS')}'>
							{foreach  item=ITEM from=Vtiger_Util_Helper::getPickListValues('quotestage')}
								<option value="{$ITEM}" {if in_array($ITEM, $SQUOTES['statuses_close'])} selected {/if}>{vtranslate($ITEM,'Quotes')}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-pane" id="so">
			{assign var=SSALESORDER value=$CONFIG['ssalesorder']}
			<div class="form-horizonta">
				<div class="form-group col-md-5">
					<label class='control-label'>{vtranslate('LBL_STATUSES_CLOSED_SSALESORDER', $QUALIFIED_MODULE)}</label>
					<div class="">
						<select class="selectize configField" multiple data-type="ssalesorder" name="statuses_close"  placeholder='{vtranslate('LBL_SELECT_SOME_OPTIONS')}'>
							{foreach  item=ITEM from=Vtiger_Util_Helper::getPickListValues('sostatus')}
								<option value="{$ITEM}" {if in_array($ITEM, $SSALESORDER['statuses_close'])} selected {/if}>{vtranslate($ITEM,'SalesOrder')}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{/strip}
