<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">Endpoints</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-8">
                <div class="chart-responsive">
                    <canvas id="endpointsChart" height="150"></canvas>
                </div>
             </div>
            <div class="col-md-4">
                <ul class="chart-legend clearfix">
                    <li><i class="fa fa-circle-o text-red"></i> Failed</li>
                    <li><i class="fa fa-circle-o text-green"></i> Success</li>
                    <li><i class="fa fa-circle-o text-yellow"></i> Not tested</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="box-footer no-padding">
        <ul class="nav nav-pills nav-stacked">
            <li>Total endpoints <span class="pull-right"><?= $total ?></span></li>
            <li>Failed <span class="pull-right text-red"><?= $failed ?></span></li>
            <li>Success <span class="pull-right text-green"><?= $success ?></span></li>
            <li>Not tested <span class="pull-right text-yellow"><?= $not_tested ?></span></li>
        </ul>
    </div>
</div>
<script type="text/javascript">
  var pieChartCanvas = $('#endpointsChart').get(0).getContext('2d');
  var pieChart       = new Chart(pieChartCanvas);
  var PieData        = [
    {
      value    : <?= $failed ?>,
      color    : '#f56954',
      highlight: '#f56954',
      label    : 'Errors'
    },
    {
      value    : <?= $success ?>,
      color    : '#00a65a',
      highlight: '#00a65a',
      label    : 'Success'
    },
    {
      value    : <?= $not_tested ?>,
      color    : '#f39c12',
      highlight: '#f39c12',
      label    : 'Not tested'
    }
  ];
  var pieOptions     = {
    // Boolean - Whether we should show a stroke on each segment
    segmentShowStroke    : true,
    // String - The colour of each segment stroke
    segmentStrokeColor   : '#fff',
    // Number - The width of each segment stroke
    segmentStrokeWidth   : 1,
    // Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    // Number - Amount of animation steps
    animationSteps       : 100,
    // String - Animation easing effect
    animationEasing      : 'easeOutBounce',
    // Boolean - Whether we animate the rotation of the Doughnut
    animateRotate        : false,
    // Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale         : false,
    // Boolean - whether to make the chart responsive to window resizing
    responsive           : true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio  : false,
    // String - A legend template
    legendTemplate       : '<ul class=\'<%=name.toLowerCase()%>-legend\'><% for (var i=0; i<segments.length; i++){%><li><span style=\'background-color:<%=segments[i].fillColor%>\'></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>',
    // String - A tooltip template
    tooltipTemplate      : ''
  };
  // Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  pieChart.Doughnut(PieData, pieOptions)
</script>