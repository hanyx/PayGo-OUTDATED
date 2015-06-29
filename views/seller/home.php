<?php
$cstOffset = (5 * 60 * 60);

if (count($url) == 2 && $url[1] == 'chart') {
    $response = array();

    $response['revenue'] = 0;
    $response['sales'] = 0;
    $response['views'] = 0;

    $response['data'] = array(array(array(), array()), array(array()));

    if (isset($_POST['product'])) {
        $product = (int)$_POST['product'];

        for ($x = 0; $x < 12; $x++) {
            $response['data'][0][0][] = array(((strtotime(date('Y-m', strtotime('first day of ' . -(11 - $x) . ' month')))) - $cstOffset) * 1000, 0);
            $response['data'][0][1][] = array(((strtotime(date('Y-m', strtotime('first day of ' . -(11 - $x) . ' month')))) - $cstOffset) * 1000, 0);
            $response['data'][1][0][] = array(((strtotime(date('Y-m', strtotime('first day of ' . -(11 - $x) . ' month')))) - $cstOffset) * 1000, 0);
        }

        if ($product != 0) {
            $products = Product::getProduct($product, true);

            if ($products !== false) {
                foreach ($products as $p) {
                    if ($product->getSellerId() == $uas->getUser()->getId()) {
                        $products[] = $p;
                    }
                }
                $products = array($products);
            } else {
                $products = $uas->getUser()->getProducts(true);
            }
        } else {
            $products = $uas->getUser()->getProducts(true);
        }

        foreach ($products as $product) {
            $orders = $product->getOrders();

            foreach ($orders as $order) {
                $date = strtotime(date('Y-m', strtotime($order->getDate())));

                if ($date > strtotime('-1 year')) {
                    $response['revenue'] += $order->getFiat();

                    $response['data'][0][0][12 - (date('m') - (date('m', strtotime($order->getDate())) - 1))][1] += $order->getFiat();
                }
            }
        }

        foreach ($products as $product) {
            $orders = $product->getOrders();

            foreach ($orders as $order) {
                $date = strtotime(date('Y-m', strtotime($order->getDate())));

                if ($date > strtotime('-1 year')) {
                    $response['sales']++;

                    $response['data'][0][1][12 - (date('m') - (date('m', strtotime($order->getDate())) - 1))][1]++;
                }
            }
        }

        foreach ($products as $product) {
            $views = $product->getViews();

            foreach ($views as $view) {
                $date = strtotime(date('Y-m', strtotime($order->getDate())));

                if ($date > strtotime('-1 year')) {
                    $response['views']++;

                    $response['data'][1][0][12 - (date('m') - (date('m', strtotime($order->getDate())) - 1))][1]++;
                }
            }
        }

        die(json_encode($response));

    }
}

__header();
?>
    <div class="graphs">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#tab-0" data-toggle="tab" tab-id="0" class="tab">Earnings</a>
            </li>
            <li role="presentation">
                <a href="#tab-1" data-toggle="tab" tab-id="1" class="tab">Views</a>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div class="graph-selects">
                <select class="selectize col-sm-12 col-md-2 graph-select" id="product-select" name="field" >
                    <option value="0">All Products</option>
                    <optgroup label="Products">
                        <?php
                        $products = $uas->getUser()->getProducts(true);
                        foreach ($products as $product) {
                            echo '<option value="' . $product->getId() . '">' . $product->getTitle() . '</option>';
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
            <div role="tabpanel" class="tab-pane active" id="tab-0">
                <div class='graph' id="graph-0" style="width: 100%; height: 400px;"></div>
            </div>
            <div role="tabpanel" class="tab-pane active" id="tab-1" style="visibility: hidden;">
                <div class='graph' id="graph-1" style="width: 100%; height: 400px;"></div>
            </div>
        </div>
        <section class="earnings-block">
            <div class="col-md-3 stat-block">
                <span class="big" id="revenue"></span>
                <span class="small">Revenue</span>
                <a class="stat-link" href="#">View Reports</a>
            </div>
            <div class="col-md-3 stat-block">
                <span class="big" id="sales"></span>
                <span class="small">Sales</span>
                <a class="stat-link" href="#">View Reports</a>
            </div>
            <div class="col-md-3 stat-block">
                <span class="big" id="views"></span>
                <span class="small">Views</span>
                <a class="stat-link" href="#">View Reports</a>
            </div>
            <div class="col-md-3 stat-block">
                <span class="big">
                    <?php
                    $x = 0;
                    $orders = Order::getOrdersByUser($uas->getUser()->getId());
                    foreach ($orders as $order) {
                        if (date('Y-m-d', strtotime($order->getDate())) == date('Y-m-d')) {
                            $x += $order->getFiat();
                        }
                    }
                    echo '$' . number_format($x, 2);
                    ?>
                </span>
                <span class="small">Revenue Today</span>
                <a class="stat-link" href="#">View Reports</a>
            </div>
            <div class="clearfix"></div>
        </section>
        <section>
            <div class="panel col-md-6">
                <canvas id="donut"></canvas>
            </div>
        </section>
    </div>

    <script>
        $(function() {
            var chart0 = $.plot($("#graph-0"), [{
                    data: null
                }, {
                    data: null
                }],
                {
                    xaxis: {
                        min: <?php echo (strtotime(date('Y-m', strtotime('12 months ago'))) + (15 * 24 * 60 * 60) - $cstOffset) * 1000; ?>,
                        max: <?php echo (strtotime(date('Y-m')) + (15 * 24 * 60 * 60) - $cstOffset) * 1000; ?>,
                        mode: "time",
                        timeformat: "%b",
                        tickSize: [1, "month"],
                        tickLength: 0,
                        axisLabel: "Months",
                        axisLabelUseCanvas: true,
                        axisLabelFontSizePixels: 12,
                        axisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
                        axisLabelPadding: 5
                    },
                    yaxes: [{
                        returntickFormatter: function (val, axis) {
                            return val + "mm";
                        },
                        returnaxisLabelFontSizePixels: 12,
                        returnaxisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif"
                    }, {
                        returntickFormatter: function (val, axis) {
                            return val + "mm";
                        },
                        returnaxisLabelFontSizePixels: 12,
                        returnaxisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
                        position: 'right'
                    }],
                    grid: {
                        color: '#646464',
                        borderColor: 'transparent',
                        borderWidth: 20,
                        hoverable: true
                    },
                    legend: {
                        labelBoxBorderColor: "none",
                        position: "right"
                    }
                }
            );

            var chart1 = $.plot($("#graph-1"), [{
                    data: null
                }],
                {
                    xaxis: {
                        min: <?php echo (strtotime(date('Y-m', strtotime('12 months ago'))) + (15 * 24 * 60 * 60) - $cstOffset) * 1000; ?>,
                        max: <?php echo (strtotime(date('Y-m')) + (15 * 24 * 60 * 60) - $cstOffset) * 1000; ?>,
                        mode: "time",
                        timeformat: "%b",
                        tickSize: [1, "month"],
                        tickLength: 0,
                        axisLabel: "Months",
                        axisLabelUseCanvas: true,
                        axisLabelFontSizePixels: 12,
                        axisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif",
                        axisLabelPadding: 5
                    },
                    yaxes: [{
                        returntickFormatter: function (val, axis) {
                            return val + "mm";
                        },
                        axisLabel: 'views',
                        returnaxisLabelFontSizePixels: 12,
                        returnaxisLabelFontFamily: "Verdana, Arial, Helvetica, Tahoma, sans-serif"
                    }],
                    grid: {
                        color: '#646464',
                        borderColor: 'transparent',
                        borderWidth: 20,
                        hoverable: true
                    },
                    legend: {
                        labelBoxBorderColor: "none",
                        position: "right"
                    }
                }
            );

            $('#tab-1').removeClass('active').attr('style', '');

            function showTooltip(x, y, contents) {
                $('<div id="tooltip">' + contents + '</div>').css({
                    top: y - 16,
                    left: x + 20
                }).appendTo('body').fadeIn();
            }

            var previousPoint = null;

            $('.graph').each(function() {
                $(this).bind('plothover', function (event, pos, item) {
                    if (item) {
                        if (previousPoint != item.dataIndex) {
                            previousPoint = item.dataIndex;
                            $('#tooltip').remove();
                            var x = item.datapoint[0],
                                y = item.datapoint[1];
                            var monthNames = [
                                "January", "February", "March",
                                "April", "May", "June", "July",
                                "August", "September", "October",
                                "November", "December"
                            ];

                            var date = new Date(x);
                            var day = date.getDate();
                            var monthIndex = date.getMonth();
                            var year = date.getFullYear();


                            showTooltip(item.pageX, item.pageY, y + ' at ' + day + ' ' + monthNames[monthIndex] + ' ' + year);
                        }
                    } else {
                        $('#tooltip').remove();
                        previousPoint = null;
                    }
                });
            });

            var product = 0;

            $('#product-select').change(function() {
                var id = $(this).val();

                if (product != id) {
                    product = id;

                    updateChart(chart0, chart1, product);
                }
            });

            updateChart(chart0, chart1, product);

        });

        function updateChart(chart0, chart1, product) {
            $.post('/seller/chart', {'product': product}, function(data) {
                var data = $.parseJSON(data);

                chart0.setData([{
                    label: 'Sales',
                    data: data.data[0][1],
                    bars: {
                        show: true,
                        align: "center",
                        barWidth: 12*24*60*60*1000,
                        fill: true,
                        lineWidth: 2

                    },
                    color: "#71c73e",
                    shadowSize: 0
                }, {
                    label: 'Revenue',
                    data: data.data[0][0],
                    lines: {
                        show: true,
                        fill: false
                    },
                    points: {
                        show: true,
                        fillColor: 'white',
                        radius: 5

                    },
                    color: '#77b7c5',
                    shadowSize: 0,
                    yaxis: 2
                }]);
                chart0.setupGrid();
                chart0.draw();

                chart1.setData([{
                    label: 'Views',
                    data: data.data[1][0],
                    bars: {
                        show: true,
                        align: "center",
                        barWidth: 12*24*60*60*1000,
                        fill: true,
                        lineWidth: 2

                    },
                    color: "#71c73e",
                    shadowSize: 0
                }]);
                chart1.setupGrid();
                chart1.draw();

                $('#revenue').html('$' + data.revenue);
                $('#sales').html(data.sales);
                $('#views').html(data.views);
            });
        }
    </script>
<?php
__footer();