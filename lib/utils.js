
/*
 * utils.js
 * Collect the self Written JS/JQuery Functions here,
 * be shared and reusable for other files/projects.
 *
 * @author bjiang 10/21/2014
 * 
 */


// Add the the Format for Digit Number. -JQuery
$.fn.digits = function(){ 
    return this.each(function(){ 
        $(this).text( $(this).text().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,") );
    })
};

// Remove the formatting to get integer data for summation.
var intVal = function ( i ) {
    return typeof i === 'string' ?
        i.replace(/[\$,]/g, '')*1 :
        typeof i === 'number' ?
            i : 0;
};

// Add the the Format for Digit Number. -JS
function addCommas(nStr){
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

// Format Float number to fixed 2 bit after '.'  ex: 123.45  
function floatVal(num) {
    return parseFloat(Math.round(num * 100) / 100).toFixed(2)
}