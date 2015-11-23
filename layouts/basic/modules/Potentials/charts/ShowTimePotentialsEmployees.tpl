{if count($DATA) }
    <div style="width: 40%; margin: auto; text-align: center; float: left">
		{vtranslate('LBL_TOTAL_TIME')}<br/>
		{vtranslate('LBL_EMPLOYEE')}<br/>
        <canvas id="sPTE" height="350" width="400"></canvas>
    </div>
    <script>
        var barChartData2 = {
        labels : [{foreach from=$DATA item=record key=key name=dataloop}"{$record['name']}"{if !$smarty.foreach.dataloop.last},{/if}{/foreach}],                	
                datasets : [
                {       fillColor : "rgba(220,220,220,0.5)", strokeColor : "rgba(220,220,220,0.8)", highlightFill: "rgba(220,220,220,0.75)", highlightStroke: "rgba(220,220,220,1)",
                        data : [{foreach from=$DATA item=record key=key name=dataloop2}"{$record['time']}"{if !$smarty.foreach.dataloop2.last},{/if}{/foreach}]
                },
                ]
        };
        	$(document).ready(function(){
                var ctx2 = document.getElementById("sPTE").getContext("2d");
                        window.myBar2 = new Chart(ctx2).Bar(barChartData2);
            });
    </script>
{/if}



