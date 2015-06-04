<?php
if (isset($_GET['getdata'])) {
    $data = array();

    $views = View::getViewsByUser($uas->getUser()->getId());

    foreach ($views as $view) {
        $product = new Product();

        $product->read($view->getProductId());

        $data[] = array(
            'product' => '<a href=\'' . $product->getUrl() . '\'>' . $product->getTitle() . '</a>',
            'date' => $view->getDate(),
            'ip' => $view->getIp(),
            'referrer' => $view->getReferrer()
        );
    }

    echo json_encode(array('aaData' => $data));
    die();
}

$todayIps = array();
$monthIps = array();
$weekIps = array();

$todayViews = 0;
$todayVisitors = 0;
$monthViews = 0;
$monthVisitors = 0;

$view = $views = View::getViewsByUser($uas->getUser()->getId());

$viewsChart = array();
$viewersChart = array();

for ($x = 0; $x <= 7; $x++) {
    $date = strtotime(date("Y-m-d")) - (86400 * $x);
    $viewsChart[] = array(($date - 21600) * 1000, 0);
    $viewersChart[] = array(($date - 21600) * 1000, 0);
}

foreach ($views as $view) {
    if (strtotime($view->getDate()) >= time() - (60 * 60 * 24)) {
        $todayViews++;

        if (!in_array($view->getIp(), $todayIps)) {
            $todayIps[] = $view->getIp();
            $todayVisitors++;
        }
    }

    if (strtotime($view->getDate()) >= time() - (60 * 60 * 24 * 31)) {
        $monthViews++;

        if (!in_array($view->getIp(), $monthIps)) {
            $monthIps[] = $view->getIp();
            $monthVisitors++;
        }
    }

    $date = strtotime(date("Y-m-d", strtotime($view->getDate())));

    if ($date > strtotime(date("Y-m-d")) - (86400 * 7)) {
        $offset = (strtotime(date("Y-m-d")) - $date) / 86400;
        $viewsChart[$offset][1]++;

        if (!in_array($view->getIp(), $weekIps)) {
            $weekIps[] = $view->getIp();
            $viewersChart[$offset][1]++;
        }
    }
}

$chartStart = date("Y", strtotime(date("Y-m-d")) - 518400) . ", " . (date("m", strtotime(date("Y-m-d")) - 518400) - 1) . ", " . date("d", strtotime(date("Y-m-d")) - 518400);
$chartEnd = date("Y", strtotime(date("Y-m-d"))) . ", " . (date("m", strtotime(date("Y-m-d"))) - 1) . ", " . date("d", strtotime(date("Y-m-d")));


include_once('header.php');
?>
    <section class="wrapper">
        <div class='clearfix'>
            <?php $uas->printMessages(); ?>
        </div>
        <section class='panel'>
            <header class='panel-heading font-bold'>Weekly Site Statistics</header>
            <div class='panel-body'>
                <div id='views-chart' style='height:240px'></div>
            </div>
            <footer class='panel-footer'>
                <div class='row text-center'>
                    <div class='col-xs-3 b-r'>
                        <p class='h3 font-bold m-t'><?php echo $todayViews; ?></p>
                        <p class='text-muted'><?php echo formatS($todayViews, 'Pageview'); ?> Today</p>
                    </div>
                    <div class='col-xs-3 b-r'>
                        <p class='h3 font-bold m-t'><?php echo $todayVisitors; ?></p>
                        <p class='text-muted'>Unique <?php echo formatS($todayVisitors, 'Visitor'); ?> Today</p>
                    </div>
                    <div class='col-xs-3 b-r'>
                        <p class='h3 font-bold m-t'><?php echo $monthViews; ?></p>
                        <p class='text-muted'><?php echo formatS($monthViews, 'Pageview'); ?> this Month</p>
                    </div>
                    <div class='col-xs-3'>
                        <p class='h3 font-bold m-t'><?php echo $monthVisitors; ?></p>
                        <p class='text-muted'>Unique <?php echo formatS($monthVisitors, 'Visitor'); ?> this Month</p>
                    </div>
                </div>
            </footer>
        </section>
        <div class='row'>
            <div class='col-lg-12'>
                <section class='panel'>
                    <header class='panel-heading font-bold'>
                        Page Views
                    </header>
                    <table class='table table-striped m-b-none' data-ride='views'>
                        <thead>
                        <tr>
                            <th>Product</th>
                            <th>Date</th>
                            <th>IP</th>
                            <th>Referring URL</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </section>
            </div>
        </div>
    </section>
    <script>
        $('[data-ride=\'views\']').dataTable( {
            'bProcessing': true,
            'sAjaxSource': '/seller/tracking?getdata=true',
            'sDom': '<\'row\'<\'col-sm-6\'l><\'col-sm-6\'f>r>t<\'row\'<\'col-sm-6\'i><\'col col-sm-6\'p>>',
            'sPaginationType': 'full_numbers',
            'aoColumns': [
                { 'mData': 'product' },
                { 'mData': 'date' },
                { 'mData': 'ip' },
                { 'mData': 'referrer' },
            ]
        } );
    </script>
    <script>
        $.plot($('#views-chart'), [{
                data: <?php echo json_encode($viewsChart); ?>,
                label: 'Views'
            }, {
                data: <?php echo json_encode($viewersChart); ?>,
                label: 'Viewers'
            }],
            {
                series: {
                    lines: {
                        show: true,
                        lineWidth: 1,
                        fill: true,
                        fillColor: {
                            colors: [{
                                opacity: 0.2
                            }, {
                                opacity: 0.1
                            }]
                        }
                    },
                    points: {
                        show: true
                    },
                    shadowSize: 2
                },
                grid: {
                    hoverable: true,
                    clickable: true,
                    tickColor: '#f0f0f0',
                    borderWidth: 0
                },
                colors: ['#5191d1'],
                xaxis: {
                    mode: 'time',
                    minTickSize: [1, 'day'],
                    min: Date.UTC(<?php echo $chartStart; ?>),
                    max: Date.UTC(<?php echo $chartEnd; ?>)
                },
                yaxis: {
                    ticks: 10,
                    tickDecimals: 0
                },
                tooltip: true,
                tooltipOpts: {
                    content: '%y.4 %s on %x.1',
                    defaultTheme: false,
                    shifts: {
                        x: 0,
                        y: 20
                    }
                }
            }
        );
    </script>
<?php
include_once('footer.php');