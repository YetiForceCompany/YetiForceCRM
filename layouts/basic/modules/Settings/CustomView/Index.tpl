{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="customViewList">
		<div class="o-breadcrumb widget_header row mb-2">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading mb-2">
				<div class="row">
					<div class="col-md-4 col-sm-4 col-6">
						<select class="select2 js-module-filter" data-js="change" name="moduleFilter">
							{foreach item=SUPPORTED_MODULE_NAME from=$SUPPORTED_MODULE_MODELS}
								<option {if $SOURCE_MODULE eq $SUPPORTED_MODULE_NAME} selected="" {/if} value="{$SUPPORTED_MODULE_NAME}">
									{App\Language::translate($SUPPORTED_MODULE_NAME, $SUPPORTED_MODULE_NAME)}
								</option>
							{/foreach}
						</select>
					</div>
					<div class="col-md-8 col-sm-8 col-6">
						<button class="btn btn-success float-right js-create-filter" data-js="click" type="button" data-editurl="{$MODULE_MODEL->getCreateFilterUrl($SOURCE_MODULE_ID)}"><span class="fas fa-plus"></span> {App\Language::translate('LBL_ADD_FILTER',$QUALIFIED_MODULE)}</button>
					</div>

				</div>
			</div>
			<div class="panel-body">
				<div class="indexContents">
					{include file=\App\Layout::getTemplatePath('IndexContents.tpl', $QUALIFIED_MODULE)}
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
{/strip}
