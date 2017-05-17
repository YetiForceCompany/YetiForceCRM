{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} --!>*}
{strip}
<div class="">
	<div class="clearfix treeView">
		<form id="PassForm" class="form-horizontal">
			<div class="widget_header row">
				<div class="col-md-12">
				    {include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				    &nbsp;{vtranslate('LBL_PASSWORD_DESCRIPTION', $QUALIFIED_MODULE)}</div>
			</div>
			<table class="table table-bordered table-condensed themeTableColor">
				<thead>
					<tr class="blockHeader"><th colspan="2" class="mediumWidthType">{vtranslate('LBL_Password_Header', $QUALIFIED_MODULE)}</th></tr>
				</thead>
				<tbody>
					<tr>
						<td width="30%"><label class="muted pull-right marginRight10px">{vtranslate('Minimum password length', $QUALIFIED_MODULE)}</label></td>
						<td style="border-left: none;">
							<div class="col-xs-5">
								<input class="form-control" type="text" name="min_length" id="min_length"  title="{vtranslate('Minimum password length', $QUALIFIED_MODULE)}" value="{$DETAIL['min_length']}" />
							</div>
						</td>
					</tr>
					<tr>
						<td width="30%"><label class="muted pull-right marginRight10px">{vtranslate('Maximum password length', $QUALIFIED_MODULE)}</label></td>
						<td style="border-left: none;">
							<div class="col-xs-5">
								<input class="form-control" type="text" name="max_length" id="max_length" title="{vtranslate('Maximum password length', $QUALIFIED_MODULE)}" value="{$DETAIL['max_length']}" />
							</div>
						</td>
					</tr>
					<tr>
						<td width="30%"><label class="muted pull-right marginRight10px">{vtranslate('Uppercase letters from A to Z', $QUALIFIED_MODULE)}</label></td>
						<td style="border-left: none;">
							<div class="col-xs-5">	
								<input type="checkbox" name="big_letters" title="{vtranslate('Uppercase letters from A to Z', $QUALIFIED_MODULE)}" id="big_letters" {if $DETAIL['big_letters'] == 'true' }checked{/if} />
							</div>
						</td>
					</tr>
					<tr>
						<td width="30%"><label class="muted pull-right marginRight10px">{vtranslate('Lowercase letters a to z', $QUALIFIED_MODULE)}</label></td>
						<td style="border-left: none;">
							<div class="col-xs-5">
								<input type="checkbox" name="small_letters" title="{vtranslate('Lowercase letters a to z', $QUALIFIED_MODULE)}" id="small_letters" {if $DETAIL['small_letters'] == 'true'}checked{/if} />
							</div>
						</td>
					</tr>
					<tr>
						<td width="30%"><label class="muted pull-right marginRight10px">{vtranslate('Password should contain numbers', $QUALIFIED_MODULE)}</label></td>
						<td style="border-left: none;">
							<div class="col-xs-5">
								<input type="checkbox" name="numbers" title="{vtranslate('Password should contain numbers', $QUALIFIED_MODULE)}" id="numbers" {if $DETAIL['numbers'] == 'true'}checked{/if} />
							</div>
						</td>
					</tr>
					<tr>
						<td width="30%"><label class="muted pull-right marginRight10px">{vtranslate('Password should contain special characters', $QUALIFIED_MODULE)}</label></td>
						<td style="border-left: none;">
							<div class="col-xs-5">
								<input type="checkbox" name="special" title="{vtranslate('Password should contain special characters', $QUALIFIED_MODULE)}" id="special"  {if $DETAIL['special'] == 'true'}checked{/if} />
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
{/strip}
