var PHRAGILE = PHRAGILE || {};

(function (PHRAGILE) {
    var $chartData = $('#chart-data');
    PHRAGILE.chartData.init(
        $.parseJSON($chartData.text())
    );

    var showGraphs = function (cssIDs) {
        $('.graph, .graph-area, .data-point').hide();
        cssIDs.map(function (id) {
            $('.graph.' + id
                + ', #' + id + '-data-points .data-point'
                + ', .graph-area.' + id).show();
        });
    };

    PHRAGILE.coordinateSystem.init(PHRAGILE.chartData.getDaysInSprint(), PHRAGILE.chartData.getMaxPoints());
    PHRAGILE.coordinateSystem.addGraphs({
        burnup: new PHRAGILE.ProgressGraph(PHRAGILE.chartData.getBurnupData(), 'burnup', 'Completed'),
        scope: new PHRAGILE.Graph(PHRAGILE.chartData.getScopeLine(), 'scope', 'Scope'),
        burndown: new PHRAGILE.ProgressGraph(PHRAGILE.chartData.getBurndownData(), 'burndown', 'Remaining '),
        ideal: new PHRAGILE.Graph(PHRAGILE.chartData.getIdealGraphData(), 'ideal', 'Ideal')
    });
    PHRAGILE.coordinateSystem.addBarCharts({
        closedPerDay: new PHRAGILE.BarChart(PHRAGILE.chartData.getPointsClosedPerDay(), 'daily-points', 'Closed')
    });
    PHRAGILE.coordinateSystem.render(
        '#burndown',
        {
            height: 400,
            width: 600,

            margin: { top: 10, right: 10, bottom: 50, left: 40 }
        }
    );

    if (window.location.hash === '#!burnup') {
        showGraphs(['burnup', 'scope']);
        $('#pick-chart li:last').addClass('active');
    } else {
        showGraphs(['burndown', 'ideal']);
        $('#pick-chart li:first').addClass('active');
    }


    var $chartButtons = $('#pick-chart li');
    $chartButtons.click(function () {
        var $button = $(this);

        $chartButtons.removeClass('active');
        $button.addClass('active');
        showGraphs($button.data('graphs').split(' '));
        window.location.hash = $button.find('a').attr('href');
    });
})(PHRAGILE);
