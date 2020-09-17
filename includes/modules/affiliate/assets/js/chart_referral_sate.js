window.onload = function() {

    var data_rate = new Array();
    var i = 1;

    $('#edd_tax_rates tr td input').each(function() {
        data_rate.push({y: parseInt($(this).val()), label: "Lever "+i})
        i++
    });

    var chart = new CanvasJS.Chart("chartContainer", {
        animationEnabled: true,
        title: {
            text: "Referral rate"
        },
        backgroundColor: "#f1f1f1",
        data: [{
            type: "pie",
            // showInLegend: true,
            startAngle: 40,
            toolTipContent: "<b>{label}</b>: {y}%",
            yValueFormatString: ': '+"##0\"%\"",
            indexLabelFontSize: 16,
            legendText: "{label}",
            indexLabel: "{label} {y}",
            dataPoints: data_rate
        }],
    });
    if( isNaN(data_rate[0].y) ){
        return;
    }

    chart.render();

    $('.canvasjs-chart-credit').hide();
}