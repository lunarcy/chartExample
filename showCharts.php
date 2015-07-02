<?php
/*
 * showCharts.php
 * Retrive trades data in JSON format,
 * then Render them in jqPlot Charts.
 *
 * @author bjiang 10/09/2014
 * 
 */

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Show Trades Charts</title>

        <script type="text/javascript" src="../lib/jqplot/jquery.min.js"></script>
        <script type="text/javascript" src="../lib/jqplot/jquery.jqplot.min.js"></script>
        <script type="text/javascript" src="../lib/jqplot/plugins/jqplot.cursor.min.js"></script>        
        <script type="text/javascript" src="../lib/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>
        <script type="text/javascript" src="../lib/jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>
        <script type="text/javascript" src="../lib/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>
        <script type="text/javascript" src="../lib/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
        <script type="text/javascript" src="../lib/jqplot/plugins/jqplot.barRenderer.min.js"></script>
        <script type="text/javascript" src="../lib/jqplot/plugins/jqplot.highlighter.min.js"></script>
        <script type="text/javascript" src="../lib/jqplot/plugins/jqplot.bubbleRenderer.min.js"></script>
        <script type="text/javascript" src="../lib/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js"></script>
        <script type="text/javascript" src="../share/js/utils.js"></script>
        
        <link rel="stylesheet" type="text/css" href="../lib/jqplot/jquery.jqplot.min.css" />
        <link rel="stylesheet" type="text/css" href="../share/css/style.css" />
        <link rel="stylesheet" type="text/css" href="../lib/jquery-ui-1.10.4.custom/css/ui-lightness/jquery-ui-1.10.4.custom.min.css" />
    </head>
    <body>
    <?php
        $startdate = $_REQUEST["startdate"];
        $enddate = $_REQUEST["enddate"];
        
        $cStr = $_REQUEST["chart"];
    ?>

<?php if ($cStr != "line"): ?>
<script type="text/javascript">
$(document).ready(function(){
    // Enable plugins like highlighter and cursor by default.
    // Otherwise, must specify show: true option for those plugins.
    $.jqplot.config.enablePlugins = true;

    //Chart1, Line and Bar, dual Charts for Trade Price and Notional.
    $.getJSON('../src/getTrades.php?param=tradePrice&startdate=<?php echo $startdate; ?>&enddate=<?php echo $enddate; ?>&instr=MA Class I RECs&vintage=2013',function (data, textStatus, jqXHR) { //console.log(data);
        //console.warn(data);
        var plot1 = $.jqplot('lineChart', [data.price, data.volume], { 
            title:'Trade Price Line Chart',
            // Turns on animatino for all series in this plot.
            animate: true,
            // Will animate plot on calls to plot1.replot({resetAxes:true})
            animateReplot: true,
            grid: {
                        drawBorder: false, 
                        drawGridlines: true,
                        background:'#000',
                        shadow:true
            },
            //seriesDefaults:{ seriesColors: ["#F3CBBF", "#BFDDE5", "#8ae234"] },
            axesDefaults: {
                tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
                tickOptions: {
                  angle: -40
                },
            },
            axes:{
                xaxis:{
                    renderer:$.jqplot.DateAxisRenderer, 
                    rendererOptions:{ tickRenderer:$.jqplot.CanvasAxisTickRenderer },
                    tickOptions:{ 
                        fontSize:'10pt', 
                        fontFamily:'Tahoma', 
                        angle:-40,
                        showGridline: false
                    }
                },
                x2axis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                    tickOptions:{ 
                        showGridline: false
                    }
                },
                yaxis:{
                    rendererOptions:{ tickRenderer:$.jqplot.CanvasAxisTickRenderer },
                    tickOptions:{
                        fontSize:'10pt', 
                        fontFamily:'Tahoma', 
                        formatString:'$%.2f',
                        angle:30,
                        showGridline: false
                    },
                    autoscale:true
                },
                y2axis: {
                  autoscale:true
                }
            },
            series:[{ 
                        lineWidth:4, 
                        label:'Price',
                        markerOptions:{ style:'square' },
                        rendererOptions: { smooth: true }
                    }, 
                   {
                       label:'Volume',
                       renderer:$.jqplot.BarRenderer,
                       rendererOptions: {  
                            animation: {
                                speed: 2500
                            },
                            highlightMouseOver: true,
                            barWidth :20,
                            barPadding: -15,
                            barMargin: 0,
                        },
                       xaxis:'x2axis', 
                       yaxis:'y2axis'
                   }
            ],
            seriesColors: ["#76b4ff", "#eb8f00"],
            legend: {
                show: true,
                location: 'ne',     // compass direction, nw, n, ne, e, se, s, sw, w.
                xoffset: 12,        // pixel offset of the legend box from the x (or x2) axis.
                yoffset: 12,        // pixel offset of the legend box from the y (or y2) axis.
            },
            cursor:{
                zoom:true,
                looseZoom: true,
                followMouse: false
            },
            highlighter: {
                show: true,
                showMarker:false,
                tooltipAxes: 'xy',
                yvalues: 5,  // notify how many row of data to be shown in each highlight tooltips, after the Date.
                formatString:'<table class="jqplot-highlighter"> \
                <tr><td>date:</td><td>%s</td></tr> \
                <tr><td>Traded:</td><td>%s</td></tr> \
                <tr><td>Instrument:</td><td>%s</td></tr> \
                <tr><td>Vintage:</td><td>%s</td></tr> \
                <tr><td>Quantity:</td><td>%s</td></tr> \
                <tr><td>Notional:</td><td>$%s</td></tr></table>'
            }
        });
        
        $("#resizable1").resizable({delay:5, helper:'ui-resizable-helper'});
        
        //Resize the Chart Dynamically.
        $('#resizable1').bind('resize', function(event, ui) {
            // pass in resetAxes: true option to get rid of old ticks and axis properties
            // which should be recomputed based on new plot size.
            plot1.replot( { resetAxes: true } );
        });
    });
    
    // Bubble Charts, Avg Price, Volume, Commission based on Notional 2%.
    $.getJSON('../src/getTrades.php?param=bubble',function (data, textStatus, jqXHR) { //console.log(data);
        var plot2 = $.jqplot('bubbleChart',[data.bubble],{
            title: 'Price, Volume, Commission Bubble Chart',
             // Turns on animatino for all series in this plot.
            animate: true,
            // Will animate plot on calls to plot1.replot({resetAxes:true})
            animateReplot: true,
            seriesDefaults:{
                renderer: $.jqplot.BubbleRenderer,
                rendererOptions: {
                    bubbleGradients: false,
                    bubbleAlpha: 0.6,
                    highlightAlpha: 0.8,
                    showLabels: false,
                    animation: {
                                speed: 2500
                            },
                },
                shadow: true,
                shadowAlpha: 0.05,
                
            },
            grid: {
                drawBorder: false, 
                drawGridlines: true,
                background:'#fff',
                //background: "-webkit-gradient(left top, right top, color-stop(0%, rgba(73,155,234,1)),color-stop(40%, rgba(73,155,234,1)), color-stop(57%, rgba(32,124,229,1)), color-stop(100%, rgba(32,124,229,1)))",
                shadow:true
            },
            cursor:{
                zoom:true,
                looseZoom: true,
                followMouse: false
            },
            legend: {
                show: true,
                location: 'ne',     // compass direction, nw, n, ne, e, se, s, sw, w.
                xoffset: 12,        // pixel offset of the legend box from the x (or x2) axis.
                yoffset: 12,        // pixel offset of the legend box from the y (or y2) axis.
            },
            highlighter: {
                show: false,
                showMarker:true,
                tooltipAxes: 'xy',
                yvalues: 5,  // notify how many row of data to be shown in each highlight tooltips, after the Date.
                formatString:'<table class="jqplot-highlighter2"> \
                <tr><td>Average price:</td><td>$%s</td></tr> \
                <tr><td>Total Quantity:</td><td>%s (lots)</td></tr> \
                <tr><td>Commission:</td><td>$%s</td></tr> \
                <tr><td>Vintage:</td><td>%s</td></tr> \ </table>'
	    },
            series:[{
                rendererOptions: {
                    animation: { speed: 2500 },
                },
                smooth: true,
                label:data.bubble[0][3],
            }]
        });
        
        $("#resizable2").resizable({delay:20, helper:'ui-resizable-helper'});
        
        //Resize the Chart Dynamically.
        $('#resizable2').bind('resize', function(event, ui) {
            // pass in resetAxes: true option to get rid of old ticks and axis properties
            // which should be recomputed based on new plot size.
            plot2.replot( { resetAxes: true } );
        });
        
        // Legend is a simple table in the html.
        // Dynamically populate it with the labels from each data value.
        $.each(data.bubble, function(index, val) {
            $('#legend1b').append('<tr><td>'+val[3]+'</td><td class="formatNum">$'+addCommas(val[2])+'</td></tr>');
        });

        // Now bind function to the highlight event to show the tooltip
        // and highlight the row in the legend. 
        $('#bubbleChart').bind('jqplotDataHighlight', function (ev, seriesIndex, pointIndex, data, radius) {
            var chart_left = $('#bubbleChart').offset().left,
            chart_top = $('#bubbleChart').offset().top,
            x = plot2.axes.xaxis.u2p(data[0]),  // convert x axis unita to pixels
            y = plot2.axes.yaxis.u2p(data[1]);  // convert y axis units to pixels
            var color = '#2aabd2';
            // Self Defined Tooltips.
            $('#tooltip1b').css({left:chart_left+x+radius+5, top:chart_top+y});
            $('#tooltip1b').html('<span style="font-size:14px;font-weight:bold;color:' + 
            color + ';">' + data[3] + '</span><br />' + 'Avg Price: $' + data[0].toFixed(2) + //Float format 2 digit after '.'
            '<br />' + 'Total Qty: ' + data[1] + '(lots)<br />' + 'Commission: <span class="formatNum">$' + addCommas(data[2])+'</span>');
            $('#tooltip1b').show();
            // Self Defined Legend.
            $('#legend1b tr').css('background-color', '#ffffff');
            $('#legend1b tr').eq(pointIndex+1).css('background-color', color);
        });

        // Bind a function to the unhighlight event to clean up after highlighting.
        $('#bubbleChart').bind('jqplotDataUnhighlight', function (ev, seriesIndex, pointIndex, data) {
              $('#tooltip1b').empty();
              $('#tooltip1b').hide();
              $('#legend1b tr').css('background-color', '#ffffff');
         });        

    }); //=== End of JqPlot call with JSON.
    

    // Click Button to change the Charts Color Scheme.
    $('button.orangeBtn').click(function(){
        $('#lineChart').removeClass('gradientBlue').addClass('gradientOrange');
        $('#bubbleChart').removeClass('gradientBlue').addClass('gradientOrange');
    });

    $('button.blueBtn').click(function(){
        $('#lineChart').removeClass('gradientOrange').addClass('gradientBlue');
        $('#bubbleChart').removeClass('gradientOrange').addClass('gradientBlue');
    });
    
    //Refresh/Reser the Chart page.
    $( "#refresh" ).click(function() {
         location.reload(true);
    });

});
</script>
    <div id="resizable1" class="ui-widget-content">
        <div id="lineChart" class='gradientBlue' style="height:96%; width:96%; position: relative; background-color: #76b4ff;"></div>
    </div>

    <div class="styleChange">
        <button title="Reload the Charts/Graphs" id="refresh" type="button" class="blueBtn">Reset</button>
        <button title="Gradient Color Scheme" type="button" class="orangeBtn">Orange Juice</button>
        <button title="Gradient Color Scheme" type="button" class="blueBtn">Blue Sky</button>
    </div>

    <div style="position:absolute;z-index:99;display:none;" id="tooltip1b"></div>
    <table>
      <tr>
        <td>
            <div id="resizable2" class="ui-widget-content">
                <div id="bubbleChart" class='gradientBlue' style="height:96%; width:96%; position: relative;"></div>
            </div>
        </td>
        <td>
            <div style="height:340px;"><table id="legend1b"><tr><th>Company</th><th>Commission</th></tr></table></div>
        </td>
      </tr>
    </table>

<?php elseif($cStr == "bar"): //=0, not submitted. ?>
        <!-- for some other type of jqPlot Chart. -->
<?php endif; ?>
    </body>
</html>
<?php  

?>


