<?php
/*
 * showTrades.php
 * Retrive trades data in JSON format,
 * then Render them in DataTables and jqPlot Charts.
 *
 * @author bjiang 10/03/2014
 * 
 */

//require_once 'data/daoREC.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Show Trades Table</title>

        <script type="text/javascript" src="../lib/jquery/jquery-1.11.1.min.js"></script>
        <script type="text/javascript" src="../lib/DataTables-1.10.2/media/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="../lib/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js"></script>
        <script type="text/javascript" src="../share/js/utils.js"></script>
        
        <link rel="stylesheet" type="text/css" href="../lib/DataTables-1.10.2/media/css/jquery.dataTables.min.css" />
        <link rel="stylesheet" type="text/css" href="../lib/jquery-ui-1.10.4.custom/css/ui-lightness/jquery-ui-1.10.4.custom.min.css" />
        <link rel="stylesheet" type="text/css" href="../share/css/style.css" />
    </head>
    <body>
    <?php
        $pStr = $_REQUEST["param1"];
        $cStr = $_REQUEST["chart"];
    ?>
    <script type="text/javascript">
    $(document).ready(function() {
        
        // Set Date format.
        $(function() {
            $( "#start, #end" ).datepicker({
                "dateFormat":"yy-mm-dd"
            });
        });
        
        // Setup - add a text input to each footer cell
        $('#dtTable tfoot th').each( function () {
            var title = $('#dtTable thead th').eq( $(this).index() ).text();
            $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
        } );
        
        $('#dtTable').dataTable( {
            "ajax": '../src/getTrades.php',
            "columns": [
                { "data": "id" },
                { "data": "buyer" },
                { "data": "seller" },
                { "data": "instrument" },
                { "data": "vintage" },
                { "data": "quantity" },
                { "data": "lotsize" },
                { "data": "price" },
                { "data": "ante" },
                { "data": "timestamp" },
                { "data": "notional" }
            ],
            "oLanguage": {
                "sSearch": "Search all columns:"
            },
            "iDisplayLength": 10,
            "bJQueryUI": false,
            "sPaginationType": "full_numbers",
            "bFilter": true,
            "footerCallback": function ( row, data, start, end, display ) { // the Function to show aggregate Total and Ave info for Notional and price.
                var api = this.api(), data;
                var colNum = 10;

                // Total over all pages
                data = api.column( colNum ).data();
                total = data.length ?
                    data.reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                    } ) :
                    0;

                // Total over this page
                data = api.column( colNum, { page: 'current'} ).data();
                pageTotal = data.length ?
                    data.reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                    } ) :
                    0;

                // Update footer
                $( api.column( colNum ).footer() ).html(
                    '<span class="rowsTotal">Total</span>: $<span class="totalNum">'+pageTotal +'</span><br/>($<span class="totalNum">'+ total +'</span>)'
                );

                $("span.totalNum").digits(); // Format int numbers.
                
                //================================
                var pageAve,allAve = 0.00;
                // Average over all pages
                data = api.column( 7 ).data();
                aTotal = data.length ?
                    data.reduce( function (a, b) {
                            return parseFloat(a) + parseFloat(b);
                    } ) :
                    0;
                if(data.length) { allAve = (aTotal / data.length).toFixed(2);}

                // Average over this page
                data = api.column( 7, { page: 'current'} ).data();
                pTotal = data.length ?
                    data.reduce( function (a, b) {
                            return parseFloat(a) + parseFloat(b);
                    } ) :
                    0;
                if(data.length) { pageAve = (pTotal / data.length).toFixed(2);}
                // Update footer
                $( api.column( 7 ).footer() ).html(
                    '<span class="rowsAve">Ave</span>: $'+pageAve +'<br/>($'+ allAve +')'
                );
            },
            initComplete: function () { //Put the Inline Filters on Header.
                var api = this.api();
                
                api.columns().indexes().flatten().each( function ( i ) {
                  var title = $('#dtTable thead th').eq( i ).text();
                  if ( i === 3) {
                    var column = api.column( i );
                    var select = $('<select><option value="">'+title+'s</option></select>')
                        .appendTo( $(column.header()).empty() )
                        .on( 'change', function () {
                            var val = $(this).val();

                            column
                                .search( val ? '^'+val+'$' : '', true, false )
                                .draw();
                         } );

                    column.data().unique().sort().each( function ( d, j ) {
                        select.append( '<option value="'+d+'">'+d+'</option>' )
                    } );
                  }
                } );
            }
        } );    //=== End of DataTable Rendering call. 

    oTable = $('#dtTable').DataTable();
    // Filter Vintages outside of the Table box, choosing the Vintage.
    $('select#mySelect').on('change',function(){
        oTable
            .columns(4)
            .search(this.value)
            .draw();
    });

// Event listener to the two range filtering inputs to redraw on input, when Keyup
$('#min, #max, #start, #end').keyup( function() {
    oTable.draw();
    
} );

// Event Listener for Start/End Date field, on change().
$('#start, #end').change( function() {
    oTable.draw();
    
    //When the Time changes, also update the Start/End Time for Charts.
    var ifrmUrl = "../view/showCharts.php?startdate="+$('#start').val()+"&enddate="+$('#end').val();
    $('#chartView').attr("src", ifrmUrl);
} );

/* Custom filtering function which will search data in column four between two values, 
 * Chain the Functions. */
$.fn.dataTable.ext.search.push(
    //Filtering by the Notional Value.  
    function( settings, data, dataIndex ) {
        var min = parseInt( $('#min').val(), 10 );
        var max = parseInt( $('#max').val(), 10 );
        var notional = intVal( data[10] ) || 0; // use data for the notional column, remove the ',' from formmated number.

        if ( ( isNaN( min ) && isNaN( max ) ) ||
             ( isNaN( min ) && notional <= max ) ||
             ( min <= notional   && isNaN( max ) ) ||
             ( min <= notional   && notional <= max ) )
        {
            return true;
        }
        return false;
    },
    
    //Filtering by the Start/End Date.
    function( settings, data, dataIndex ) {
        var startDate = Date.parse( $('#start').val(), 10 );
        var endDate = Date.parse( $('#end').val(), 10 );
        var ts = Date.parse( data[9] ) || 0; // use data for the notional column.
        //console.log(startDate);
        if ( ( isNaN( startDate ) && isNaN( endDate ) ) ||
             ( isNaN( startDate ) && ts <= endDate ) ||
             ( startDate <= ts   && isNaN( endDate ) ) ||
             ( startDate <= ts   && ts <= endDate ) )
        {
            return true;
        }
        return false;
    }    
);


        //Assign DataTable obj to 'table1'. Search box in each footers.
        var table1 = $('#dtTable').DataTable();
        // Apply the search box for each Footer.
        table1.columns().eq( 0 ).each( function ( colIdx ) {
            $( 'input', table1.column( colIdx ).footer() ).on( 'keyup change', function () {
                table1
                    .column( colIdx )
                    .search( this.value )
                    .draw();
            } );
        } );

    } );    //=== Endof Document Ready.

    </script>
    <h2>1. DataTables</h2>
    <table border="0" cellspacing="5" cellpadding="5">
        <tbody><tr>
            <td>Start Date:</td>
            <td><input type="text" id="start" name="start"></td>
            <td>End Date:</td>
            <td><input type="text" id="end" name="end"></td>
        </tr>
        <tr>
            <td>Min Notional:</td>
            <td><input size="5" type="text" id="min" name="min">K</td>
            <td>Max Notional:</td>
            <td><input size="5" type="text" id="max" name="max">K</td>
        </tr></tbody>
    </table>

    <div align="center" style="color:#0088cc;">Vintages Filter: 
        <select id="mySelect">
            <option value="">All</option>
            <option value="2013">2013</option>
            <option value="2014">2014</option>
            <option value="2015">2015</option>
            <option value="2016">2016</option>
            <option value="1 June 14 v14">June 14 v14</option>
            <option value="2 Dec 14 v14">Dec 14 v14</option>
        </select>
    </div>
    
    <!-- ===== DataTables HTML ===== -->
    <table id="dtTable" class="display gradientOrange" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>TradeID</th>
                <th>Seller</th>
                <th>Buyer</th>
                <th>Instrument</th>
                <th>Vintage</th>
                <th>Quantity</th>
                <th>Lotsize</th>
                <th>Price</th>
                <th>Ante</th>
                <th>Timestamp</th>
                <th>Notional</th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <th>TradeID</th>
                <th>Seller</th>
                <th>Buyer</th>
                <th>Instrument</th>
                <th>Vintage</th>
                <th>Quantity</th>
                <th>Lotsize</th>
                <th width="80">Price</th>
                <th>Ante</th>
                <th>Timestamp</th>
                <th width="135">Notional</th>
            </tr>
        </tfoot>
    </table>

<h2>2. Charts</h2>
    <iframe name="chartView" id="chartView" src="../view/showCharts.php" width=100% height=1380px scrolling="no"></iframe>
    </body>
</html>
<?php  

?>


