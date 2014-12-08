{if $DATA}
    <div style="width: 80%; margin: auto; text-align: center;">{vtranslate('OSSTimeControl','OSSTimeControl')}: {vtranslate('Employees')}<br/>
        <canvas id="sPTE" height="350" width="800"></canvas>
    </div>
    <script>
        var barChartData2 = {
        labels : [{foreach from=$DATA item=record key=key name=dataloop}"{strip_tags($record['name'])}"{if !$smarty.foreach.dataloop.last},{/if}{/foreach}],
                datasets : [
                {       fillColor : "rgba(220,220,220,0.5)", strokeColor : "rgba(220,220,220,0.8)", highlightFill: "rgba(220,220,220,0.75)", highlightStroke: "rgba(220,220,220,1)",
                        data : [{foreach from=$DATA item=record key=key}"{$record['time']}"{if !$smarty.foreach.dataloop.last},{/if}{/foreach}]
                },
                ]
        };
        	$(document).ready(function(){
                var ctx2 = document.getElementById("sPTE").getContext("2d");
                        window.myBar2 = new Chart(ctx2).Bar(barChartData2);
            });
    </script>
{else}
<div class="alert alert-error">
	{vtranslate('LBL_RECORDS_NO_FOUND')}
</div>	
{/if}
    