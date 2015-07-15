<?php
function __header($title = 'Dashboard', $description = 'Sell Digital Products and Services. Accept PayPal, Bitcoin, and Altcoins.') {
    global $uas, $pageManager, $url;
    ?>
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="utf-8"/>
            <title>PayIvy | <?php echo $title; ?></title>

            <meta name="description" content="<?php echo $description; ?>">
            <meta name="keywords" content="payivy, virtual marketplace, sell online, online shop, online selling">

            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
            <link rel="stylesheet" href="/themes/seller/css/bootstrap.css" type="text/css"/>
            <link rel="stylesheet" href="/themes/seller/css/animate.css" type="text/css"/>
            <link rel="stylesheet" href="/themes/seller/css/font-awesome.min.css" type="text/css"/>
            <link rel="stylesheet" href="/themes/seller/css/font.css" type="text/css" cache="false"/>
            <link rel="stylesheet" href="/themes/seller/css/plugin.css" type="text/css"/>
            <link rel="stylesheet" href="/themes/seller/css/app.css" type="text/css"/>
            <link type="text/css" href="/themes/seller/js/datatables/datatables.css" rel="stylesheet">
            <link rel="stylesheet" href="/themes/seller/js/fuelux/fuelux.css" type="text/css"/>
            <link rel="stylesheet" href="/themes/seller/js/datepicker/datepicker.css" type="text/css"/>
            <link rel="stylesheet" href="/themes/seller/js/select2/select2.css" type="text/css"/>
            <link rel="stylesheet" href="/themes/seller/js/wysihtml5/wysihtml5.css" type="text/css"/>
            <link rel="stylesheet" href="/themes/seller/js/dropzone/dropzone.css" type="text/css"/>
            <link rel="stylesheet" href="/themes/seller/css/bootstrap-tagsinput.css" type="text/css"/>


            <!--[if lt IE 9]>
            <script src="/themes/seller/js/ie/respond.min.js" cache="false"></script>
            <script src="/themes/seller/js/ie/html5.js" cache="false"></script>
            <script src="/themes/seller/js/ie/fix.js" cache="false"></script>
            <![endif]-->

            <script src="/themes/seller/js/jquery.min.js"></script>
            <script src="/themes/seller/js/bootstrap.js"></script>
            <script src='//www.google.com/recaptcha/api.js'></script>
            <script src="/themes/seller/js/datatables/jquery.dataTables.min.js"></script>
            <script src="/themes/seller/js/charts/morris/raphael-min.js" cache="false"></script>
            <script src="/themes/seller/js/charts/morris/morris.min.js" cache="false"></script>
            <script src="/themes/seller/js/charts/sparkline/jquery.sparkline.min.js"></script>
            <script src="/themes/seller/js/datepicker/bootstrap-datepicker.js"></script>
            <script src="/themes/seller/js/libs/moment.min.js"></script>
            <script src="/themes/seller/js/combodate/combodate.js" cache="false"></script>
            <script src="/themes/seller/js/slider/bootstrap-slider.js"></script>
            <script src="/themes/seller/js/select2/select2.min.js" cache="false"></script>
            <script src="/themes/seller/js/charts/flot/jquery.flot.min.js" cache="false"></script>
            <script src="/themes/seller/js/charts/flot/jquery.flot.resize.js" cache="false"></script>
            <script src="/themes/seller/js/charts/flot/jquery.flot.orderBars.js" cache="false"></script>
            <script src="/themes/seller/js/charts/flot/jquery.flot.pie.min.js" cache="false"></script>
            <script src="/themes/seller/js/charts/flot/jquery.flot.time.js" cache="false"></script>
            <script src="/themes/seller/js/charts/flot/jquery.flot.tooltip.min.js" cache="false"></script>
            <script src="/themes/seller/js/wysiwyg/jquery.hotkeys.js" cache="false"></script>
            <script src="/themes/seller/js/wysihtml5/wysihtml5.js" cache="false"></script>
            <script src="/themes/seller/js/dropzone/dropzone.js" cache="false"></script>
            <script src="/themes/seller/js/bootstrap-tagsinput.min.js" cache="false"></script>
            <script src="/themes/seller/js/charts/easypiechart/jquery.easy-pie-chart.js"></script>

            <script src="/themes/seller/js/app.js"></script>
            <script src="/themes/seller/js/app.plugin.js"></script>
            <script src="/themes/seller/js/app.data.js"></script>

            <script src="/themes/seller/js/seller.js"></script>

            <script>
                function toggleSidebar() {
                    $.post("/seller/settings", {switch_toggle: "true"});
                }
            </script>

            <style>
                .tooltip-inner {
                    max-width: 250px;
                }
            </style>
        </head>
    <body>
    <section class="hbox stretch">
        <?php if ($uas->isAuthenticated() && !$pageManager->getCurrentPage()->noAuth()) { ?>
        <!-- .aside -->
        <aside class="bg-success dk aside-sm <?php echo $uas->getUser()->getBigSizeBar() && false ? "" : "nav-vertical"; ?> only-icon"
               id="nav">
            <section class="vbox">
                <header class="nav-bar">
                    <a class="btn btn-link visible-xs" data-toggle="class:nav-off-screen" data-target="#nav">
                        <i class="fa fa-bars"></i>
                    </a>
                    <a href="/seller/" class="nav-brand">PI</a>
                </header>
                <section class="w-f">
                    <nav class="nav-primary hidden-xs">
                        <ul class="nav">
                            <?php
                            foreach ($pageManager->getCategories() as $category) {
                                if (!$category->isHidden() && $category->checkAuth($url, $uas)) {
                                    $pages = $category->getPages();
                                    $alerts = 0;
                                    $link = '#';

                                    foreach ($pages as $page) {
                                        $alerts += $page->getAlerts($uas->getUser());
                                        if ($link == '#') {
                                            $link = $page->getLink();
                                        }
                                    }

                                    if (count($pages) > 1) {
                                        echo '<li data-toggle="tooltip" title="' . $category->getName() . '" data-placement="bottom" class=\'dropdown-submenu ' . ($category->isCurrent() ? 'active' : '') . '\'>';
                                        echo '<a href=\'' . $link . '\'>' . (($alerts > 0) ? ('<b class=\'badge bg-danger pull-right\'>' . $alerts . '</b>') : '') . '<i class=\'fa ' . $category->getIcon() . '\'></i><span>' . $category->getName() . '</span></a>';
                                        echo '<ul class=\'dropdown-menu\'>';

                                        foreach ($pages as $page) {
                                            echo '<li><a href=\'' . $page->getLink() . '\'>' . $page->getName() . '</a></li>';
                                        }

                                        echo '</ul>';
                                        echo '</li>';
                                    } else {
                                        echo '<li data-toggle="tooltip" title="' . $category->getName() . '" data-placement="bottom" class=\'' . ($category->isCurrent() ? 'active' : '') . '\'>';
                                        echo '<a href=\'' . $link . '\'>' . (($alerts > 0) ? ('<b class=\'badge bg-primary pull-right\'>' . $alerts . '</b>') : '') . '<i class=\'fa ' . $category->getIcon() . '\'></i><span>' . $category->getName() . '</span></a>';
                                        echo '</li>';
                                    }
                                }
                            }
                            ?>
                        </ul>
                    </nav>
                </section>
                <footer class="footer bg-gradient hidden-xs text-center">
                    <a class="btn btn-sm btn-link m-r-n-xs" old="pull-right" href="/seller/logout">
                        <i class="fa fa-power-off"></i>
                    </a>
                    <!--<a class="btn btn-sm btn-link m-l-n-sm" data-toggle="class:nav-vertical" href="#nav"
                       onclick="toggleSidebar();">
                        <i class="fa fa-bars"></i>
                    </a>-->
                </footer>
            </section>
        </aside>
        <!-- /.aside -->
    <?php } ?>
        <!-- .vbox -->
        <section id="content">
        <?php if ($uas->isAuthenticated() && !$pageManager->getCurrentPage()->noAuth()) { ?>
            <div class="header b-b bg-white-only">
                <div class="row">
                    <div class="col-sm-4">
                        <h4 class="m-t m-b-none"><?php echo $pageManager->getCurrentPage()->getName(); ?></h4>
                    </div>
                </div>
            </div>
            <div class="wrapper bg-light font-bold">
                <a class="m-r" href="/seller/settings/payments"><i class="fa fa-cog fa-2x icon-muted v-middle"></i> Payment Settings</a>
                <a class="m-r" href="/seller/products/create"><i class="fa fa-plus fa-2x icon-muted  v-middle"></i> Create Product</a>
                <a class="m-r" href="/seller/products/view"><i class="fa fa-wrench fa-2x icon-muted  v-middle"></i> Manage Products</a>
                <a class="m-r" href="/seller/products/orders/"><i class="fa fa-shopping-cart fa-2x icon-muted  v-middle"></i> Orders</a>
                <a href="/seller/products/files"><i class="fa fa-upload fa-2x icon-muted  v-middle"></i> Upload File</a>
            </div>
            <?php }
}
?>