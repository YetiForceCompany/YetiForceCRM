{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-ConfigEditor-Detail" id="ConfigEditorDetails">
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-4">
				<div class="float-right">
					<button class="btn btn-success editButton mt-2" data-url='{$MODEL->getEditViewUrl()}' type="button"
							title="{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"><span
								class="fa fa-edit u-mr-5px"></span><strong>{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}</strong>
					</button>
				</div>
			</div>
		</div>
		<hr>
		<div class="contents">
			<table class="table tableRWD table-bordered table-sm themeTableColor">
				<thead>
				<tr class="blockHeader">
					<th colspan="2" class="{$WIDTHTYPE}">
						<span class="alignMiddle">{\App\Language::translate('LBL_MAIN_CONFIG', $QUALIFIED_MODULE)}</span>
					</th>
				</tr>
				</thead>
				<tbody>
				{foreach key=FIELD_NAME item=FIELD_LABEL from=$MODEL->listFields}
					{assign var="FIELD_MODEL" value=$MODEL->getFieldInstanceByName($FIELD_NAME)->set('fieldvalue',$MODEL->get($FIELD_NAME))}
					<tr>
						<td width="30%" class="{$WIDTHTYPE} textAlignRight">
							<label class="muted marginRight10px">
								{\App\Language::translate($FIELD_LABEL, $QUALIFIED_MODULE)}
							</label>
						</td>
						<td style="border-left: none;" class="{$WIDTHTYPE}">
							{$MODEL->getDisplayValue($FIELD_NAME)}
						</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
	</div>
{/strip}
