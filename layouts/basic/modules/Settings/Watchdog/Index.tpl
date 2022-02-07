{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Watchdog-Index -->
	<div class="pt-md-0 pt-1">
		<div class="o-breadcrumb widget_header mb-2 d-flex flex-nowrap flex-md-wrap justify-content-between px-2 row">
			<div class="o-breadcrumb__container">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="js-watchdog-container container" data-js="container">
			<div class="row mb-2">
				<div class="alert alert-success" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="alert-heading">
						<span class="fas fa-info-circle mr-1"></span>
						{\App\Language::translate('LBL_YETIFORCE_WATCHDOG_HEADER',$QUALIFIED_MODULE)}
					</h4>
					<p>{\App\Language::translate('LBL_YETIFORCE_WATCHDOG_FULL_DESC',$QUALIFIED_MODULE)}</p>
				</div>
			</div>
			<div class="row mb-2">
				<div class="col-lg-4">
					<strong>{\App\Language::translate('LBL_PARAM_NAME',$QUALIFIED_MODULE)}</strong>
				</div>
				<div class="col-lg-8">
					<strong>{\App\Language::translate('LBL_PARAM_VAL',$QUALIFIED_MODULE)}</strong>
				</div>
			</div>
			{foreach $ALL_PARAMS as $CONF_FLAG}
				<div class="row mb-2">
					<div class="col-lg-4">{\App\Language::translate($CONF_FLAG['label'],$QUALIFIED_MODULE)}</div>
					<div class="col-lg-8" align="right">
						{if $CONF_FLAG['type'] === 'bool'}
							<div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary{if $CONF_FLAG['value']} active{/if}">
									<input
										name="YF_status_flag[{$CONF_FLAG['name']}]"
										value="1"
										type="radio"
										class="js-vars"
										data-js="change|value"
										data-flag="{$CONF_FLAG['name']}"
										data-type="{$CONF_FLAG['type']}"
										autocomplete="off"
										{if $CONF_FLAG['value']} checked{/if}>
									{\App\Language::translate('LBL_YES',$QUALIFIED_MODULE)}
								</label>
								<label class="btn btn-secondary{if !$CONF_FLAG['value']} active{/if}">
									<input
										name="YF_status_flag[{$CONF_FLAG['name']}]"
										value="0"
										type="radio"
										class="js-vars"
										data-js="change|value"
										data-flag="{$CONF_FLAG['name']}"
										data-type="{$CONF_FLAG['type']}"
										autocomplete="off"
										{if !$CONF_FLAG['value']} checked{/if}> {\App\Language::translate('LBL_NO',$QUALIFIED_MODULE)}
								</label>
							</div>
						{else}
							<input
								value="{\App\Purifier::encodeHTML($CONF_FLAG['value'])}"
								type="text"
								class="form-control js-vars"
								data-js="change|value"
								data-type="{$CONF_FLAG['type']}"
								data-flag="{$CONF_FLAG['name']}" />
						{/if}
					</div>
				</div>
			{/foreach}
		</div>
	</div>
	<!-- /tpl-Settings-Watchdog-Index -->
{/strip}
