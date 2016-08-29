{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}

{strip}
	<div class="contentsDiv" >
		<div id="paymentsOut" style="display:none">{$JSON}</div>
		<div>	  
        <h4>{vtranslate('Summary', $MODULENAME)}</h4>
		</div>
		<div class="">
				<div class="row" >
					<div class="col-md-5" style="text-align:right">
						<b>{vtranslate('Liczba transakcji:', $MODULENAME)}</b>
					</div>
					<div class="col-md-5" >
						<span class="badge">{$COUNT}</span>
					</div>
				</div>
		{for $FREQUENCY = 0 to 1}
			{if $FREQUENCY lt $COUNT}
				<div class="row" >
					<div class="col-md-12">
								{vtranslate('Import', $MODULENAME)} {$FREQUENCY+1}
							{if $PAYMENTSOUT[$FREQUENCY].details.contName neq ''}	
								<div class="row" >
									<div class="col-md-2 " >
										<div>{vtranslate('Kontrahent', $MODULENAME)}</div>
									</div>
									<div class="col-md-10">
										{$PAYMENTSOUT[$FREQUENCY].details.contName}
									</div>
								</div>	
							{/if}
							{if $PAYMENTSOUT[$FREQUENCY].amount neq ''}	
								<div class="row" >
									<div class="col-md-2 " >
										<div>{vtranslate('Amount', $MODULENAME)}</div>
									</div>
									<div class="col-md-10">
										{$PAYMENTSOUT[$FREQUENCY].amount}
									</div>
								</div>	
							{/if}
							{*
							{if $PAYMENTSOUT[$FREQUENCY].third_letter_currency_code neq ''}	
								<div class="row" >	
									<div class="col-md-2 " >
										<div style="margin-left:20px ">{vtranslate('Currency', $MODULENAME)}</div>
									</div>
									<div class="col-md-10">
										{$PAYMENTSOUT[$FREQUENCY].third_letter_currency_code}
									</div>
								</div>
							{/if}
							*}
							{if $PAYMENTSOUT[$FREQUENCY].details.contAccount neq ''}	
								<div class="row" >	
									<div class="col-md-2 " >
										<div>{vtranslate('Account', $MODULENAME)}</div>
									</div>
									<div class="col-md-10">
										{$PAYMENTSOUT[$FREQUENCY].details.contAccount}
									</div>
								</div>
							{/if}
							{if $PAYMENTSOUT[$FREQUENCY].date neq ''}		
								<div class="row" >	
									<div class="col-md-2 " >
										<div>{vtranslate('Date', $MODULENAME)}</div>
									</div>
									<div class="col-md-10">
										{$PAYMENTSOUT[$FREQUENCY].date}
									</div>
								</div>
							{/if}
							{if $PAYMENTSOUT[$FREQUENCY].details.title neq ''}	
								<div class="row" >
									<div class="col-md-2 " >
										<div>{vtranslate('Title', $MODULENAME)}</div>
									</div>
									<div class="col-md-10">
										{$PAYMENTSOUT[$FREQUENCY].details.title}
									</div>
								</div>	
							{/if}
					</div>
				</div>	
			{/if}
		{/for}
		{*
			{if $COUNT gt '2'}
				<div class="row" >
					<div class="col-md-12" style="padding:2px; text-align:center"><b>.</b></div></div>		
				<div class="row" >
					<div class="col-md-12" style="padding:2px; text-align:center"><b>.</b></div></div>	
				<div class="row" >
					<div class="col-md-12" style="padding:2px; text-align:center"><b>.</b></div></div>
			{/if}
		*}
		<div class="pull-right">
						<button class="btn btn-default addButton" id="createRecordButton" onclick="generateRecords();" data-dismiss="modal" aria-hidden="true" >{vtranslate('Create records', $MODULENAME)}</button>
						<a href="index.php?module=PaymentsOut&view=List" id="go" class="btn btn-default addButton hide">{vtranslate('Go to Payments', $MODULENAME)}</a>&nbsp
                        <a href="index.php?module=PaymentsOut&view=PaymentsImport#" class="btn btn-default">{vtranslate('Back', $MODULENAME)}</a>
        </div>
    </div>
</div>
{/strip}

{literal}
<script>
function generateRecords() {
	var area = jQuery('.contentsDiv').html();
 var paymentsOut= jQuery('#paymentsOut').text();

/* var deleteMessage = app.vtranslate('JS_RECORDS_ARE_GETTING_DELETED');
					var progressIndicatorElement = jQuery.progressIndicator({
						'message' : deleteMessage,
						'position' : 'html',
						'blockInfo' : {
							'enabled' : true
						}
					})
					*/

//
	//var progressIndicatorElement = jQuery.progressIndicator({'position' : 'html','blockInfo' : {'enabled' : true}});
	//var element = jQuery('<div></div>');
//	var detailContainer = jQuery('div.contentsDiv');
	/*element.progressIndicator({
		'position':'html',
		'blockInfo' : {
			 'enabled' : true,
			 'elementToBlock' : detailContainer
		}
	});
//*/

            var params = {};
            params.async = false;
            params.dataType = 'json';
            params.data = { 
                'module' : "PaymentsOut",
                'action' : "GenerateRecords",
				'paymentsOut' : paymentsOut
			}
    //var progressIndicatorElement = jQuery.progressIndicator({'position' : 'html','blockInfo' : {'enabled' : false}});

	
	
    AppConnector.request(params).then(
//
        function(data) {
		//	progressIndicatorElement.progressIndicator({'mode' : 'hide'	}),
		//	jQuery('.contentsDiv').html(area);
            var result = data.result;
            
            if ( result.success === true ) {
                var parametry = {
                    text: result.return,
                    type: 'success'
                };
                Vtiger_Helper_Js.showPnotify(parametry);
				jQuery('#createRecordButton').hide();
				jQuery('#go').show();
            }
            else {
                var parametry = {
                    text: result.return,
                    type: 'error'
                };
                Vtiger_Helper_Js.showPnotify(parametry);
            }
        },
        function(data,err){
            var parametry = {
                text: app.vtranslate('JS_ERROR_CONNECTING'),
                type: 'error'
            };
            Vtiger_Helper_Js.showPnotify(parametry);
        }
    );
	//

	/*	var params = {};
		params['module'] = 'PaymentsOut';
		params['view'] = 'step1';

		AppConnector.request(params).then(
			function(data) {
		jQuery('.contentsDiv').html(data);
			}
		);*/

    //  progressIndicatorElement.progressIndicator({'mode': 'hide'});  
  //
    return false;
}

</script>
{/literal}
