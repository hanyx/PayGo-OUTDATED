<?php
if (count($url) == 2 && $url[1] == 'chart') {
    $response = array();

    $response['revenue'] = 0;
    $response['sales'] = 0;
    $response['views'] = 0;

    $response['data'] = array();
    
    if (isset($_POST['tab']) && isset($_POST['period']) && isset($_POST['product'])) {
        $tab = (int) $_POST['tab'];
        $period = (int) $_POST['period'];
        $product = (int) $_POST['product'];

        $days = $period == 0 ? 7 : ($period == 1 ? 31 : 365);

        for ($day = 1; $day <= $days; $day++) {
            $response['data'][] = array($day, 0);
        }

        if ($product != 0) {
            $products = Product::getProduct($product, true);

            if ($products !== false) {
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
                $date = strtotime(date('Y-m-d', strtotime($order->getDate())));

                if ($date > strtotime(date('Y-m-d')) - ($days * 24 * 60 * 60)) {
                    $response['revenue'] += $order->getFiat();

                    if ($tab == 0) {
                        $response['data'][$days - ((strtotime(date('Y-m-d')) / (24 * 60 * 60)) - ($date / (24 * 60 * 60)) + 1)][1] += $order->getFiat();
                    }
                }
            }
        }

        foreach ($products as $product) {
            $orders = $product->getOrders();

            foreach ($orders as $order) {
                $date = strtotime(date('Y-m-d', strtotime($order->getDate())));

                if ($date > strtotime(date('Y-m-d')) - ($days * 24 * 60 * 60)) {
                    $response['sales']++;

                    if ($tab == 1) {
                        $response['data'][$days - ((strtotime(date('Y-m-d')) / (24 * 60 * 60)) - ($date / (24 * 60 * 60)) + 1)][1]++;
                    }
                }
            }
        }

        foreach ($products as $product) {
            $views = $product->getViews();

            foreach ($views as $view) {
                $date = strtotime(date('Y-m-d', strtotime($view->getDate())));

                if ($date > strtotime(date('Y-m-d')) - ($days * 24 * 60 * 60)) {
                    $response['views']++;

                    if ($tab == 2) {
                        $response['data'][$days - ((strtotime(date('Y-m-d')) / (24 * 60 * 60)) - ($date / (24 * 60 * 60)) + 1)][1]++;
                    }
                }
            }
        }
    }

    foreach ($response['data'] as &$point) {
        $point[0] = ((strtotime(date('Y-m-d')) - ($days - ($point[0] * 24 * 60 * 60))) * 1000) - (5 * 60 * 60 * 1000);
    }

    die(json_encode($response));
}

$views = $uas->getUser()->getViews();

$referrers = array();

foreach ($views as $view) {
    if (!isset($referrers[$view->getReferrer()])) {
        $referrers[$view->getReferrer()] = 0;
    }

    $referrers[$view->getReferrer()]++;
}

arsort($referrers);

$x = 0;

$referrerPie = array();

foreach ($referrers as $referrer) {
    $label = array_search($referrer, $referrers);

    if ($label == '') {
        $label = 'Direct';
    }

    $referrerPie[] = array('label' => $label, 'data' => $referrer);

    $x++;
    if ($x >= 10) {
        break;
    }
}

__header('Dashbard');
?>
    <section class="wrapper">
        <div class='clearfix'>
            <?php $uas->printMessages(); ?>
        </div>
        <section class="hbox">
            <div class='row'>
                <div class="col-lg-12">
                    <aside class="bg-white-only">
                        <header class="bg-light">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="" data-toggle="tab" class="tab" tab-id="0">Revenue</a></li>
                                <li><a href="" data-toggle="tab" class="tab" tab-id="1">Sales</a></li>
                                <li><a href="" data-toggle="tab" class="tab" tab-id="2">Views</a></li>
                            </ul>
                        </header>
                        <section class='panel'>
                            <div class='panel-body'>
                                <div data-toggle="buttons" class="btn-group">
                                    <label class="btn btn-sm btn-white active">
                                        <input type="radio" class="period" period-id="0" name="options"> Week
                                    </label>
                                    <label class="btn btn-sm btn-white">
                                        <input type="radio" class="period" period-id="1" name="options"> Month
                                    </label>
                                    <label class="btn btn-sm btn-white">
                                        <input type="radio" class="period" period-id="2" name="options"> Year
                                    </label>
                                </div>
                                <select id="product-select" style="width:260px">
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
                                <div id="chart" style="height:250px"></div>
                            </div>
                            <footer class='panel-footer'>
                                <div class='row text-center'>
                                    <div class='col-xs-4 b-r'>
                                        <p class='h3 font-bold m-t' id="revenue"></p>
                                        <p class='text-muted'>Revenue</p>
                                    </div>
                                    <div class='col-xs-4 b-r'>
                                        <p class='h3 font-bold m-t' id="sales"></p>
                                        <p class='text-muted'>Sales</p>
                                    </div>
                                    <div class='col-xs-4 b-r'>
                                        <p class='h3 font-bold m-t' id="views"></p>
                                        <p class='text-muted'>Views</p>
                                    </div>
                                </div>
                            </footer>
                        </section>
                    </aside>
                </div>
            </div>
            <div class='row'>
                <div class="col-md-6">
                    <div class="panel">
                        <header class="panel-heading font-bold">Referrers</header>
                        <div class="panel-body">
                            <div id="referrers" style="height:250px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <script>
        $(function() {
            $.plot($("#referrers"), <?php echo json_encode($referrerPie); ?>, {
                series: {
                    pie: {
                        combine: {
                            color: "#999",
                            threshold: 0.05
                        },
                        show: true
                    }
                },
                colors: ["#5c677c","#594f8d","#92cf5c","#fb6b5b","#5dcff3"],
                legend: {
                    show: false
                },
                grid: {
                    hoverable: true,
                    clickable: false
                },
                tooltip: true,
                tooltipOpts: {
                    content: "%s: %p.0%"
                }
            });

            var chart = $.plot($("#chart"), [{
                    data: null
                }],
                {
                    series: {
                        lines: {
                            show: true,
                            lineWidth: 2,
                            fill: true,
                            fillColor: {
                                colors: [{
                                    opacity: 0.0
                                }, {
                                    opacity: 0.2
                                }]
                            }
                        },
                        points: {
                            radius: 5,
                            show: true
                        },
                        shadowSize: 2
                    },
                    grid: {
                        color: "#fff",
                        hoverable: true,
                        clickable: true,
                        tickColor: "#f0f0f0",
                        borderWidth: 0
                    },
                    colors: ["#5dcff3"],
                    xaxis: {
                        mode: "time",
                        timeformat: "%m/%d/%y",
                        minTickSize: [1, "day"]
                    },
                    yaxis: {
                        ticks: 5,
                        tickDecimals: 0,
                    },
                    tooltip: true,
                    tooltipOpts: {
                        content: "%x.1 is %y.4",
                        defaultTheme: false,
                        shifts: {
                            x: 0,
                            y: 20
                        }
                    }
                }
            );

            var tab = 0;
            var period = 0;
            var product = 0;

            updateChart(chart, tab, period, product);

            $('.tab').click(function() {
                var id = $(this).attr('tab-id');

                if (tab != id) {
                    tab = id;

                    updateChart(chart, tab, period, product);
                }
            });

            $('.period').change(function() {
                var id = $(this).attr('period-id');

                if (period != id) {
                    period = id;

                    updateChart(chart, tab, period, product);
                }
            });

            $('#product-select').change(function() {
                var id = $(this).val();

                if (product != id) {
                    product = id;

                    updateChart(chart, tab, period, product);
                }
            });

        });

        function updateChart(chart, tab, period, product) {
            $.post('/seller/chart', {'tab': tab, 'period': period, 'product': product}, function(data) {
                var data = $.parseJSON(data);

                chart.setData([data.data]);
                chart.setupGrid();
                chart.draw();

                $('#revenue').html('$' + data.revenue);
                $('#sales').html(data.sales);
                $('#views').html(data.views);
            });
        }
    </script>
<?php
__footer();