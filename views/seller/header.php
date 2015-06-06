<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>PayIvy | Dashboard</title>
    <meta name="description" content="app, web app, responsive, admin dashboard, admin, flat, flat ui, ui kit, off screen nav" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="stylesheet" href="/seller-theme/css/bootstrap.css" type="text/css" />
    <link rel="stylesheet" href="/seller-theme/css/animate.css" type="text/css" />
    <link rel="stylesheet" href="/seller-theme/css/font-awesome.min.css" type="text/css" />
    <link rel="stylesheet" href="/seller-theme/css/font.css" type="text/css" cache="false" />
    <link rel="stylesheet" href="/seller-theme/css/plugin.css" type="text/css" />
    <link rel="stylesheet" href="/seller-theme/css/app.css" type="text/css" />
    <link type="text/css" href="/seller-theme/js/datatables/datatables.css" rel="stylesheet">
    <link rel="stylesheet" href="/seller-theme/js/fuelux/fuelux.css" type="text/css" />
    <link rel="stylesheet" href="/seller-theme/js/datepicker/datepicker.css" type="text/css" />
    <link rel="stylesheet" href="/seller-theme/js/select2/select2.css" type="text/css" />
    <link rel="stylesheet" href="/seller-theme/js/wysihtml5/wysihtml5.css" type="text/css" />
    <link rel="stylesheet" href="/seller-theme/js/dropzone/dropzone.css" type="text/css" />
    <link rel="stylesheet" href="/seller-theme/css/bootstrap-tagsinput.css" type="text/css" />


    <!--[if lt IE 9]>
    <script src="/seller-theme/js/ie/respond.min.js" cache="false"></script>
    <script src="/seller-theme/js/ie/html5.js" cache="false"></script>
    <script src="/seller-theme/js/ie/fix.js" cache="false"></script>
    <![endif]-->

    <script src="/seller-theme/js/jquery.min.js"></script>
    <script src="/seller-theme/js/bootstrap.js"></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script src="/seller-theme/js/datatables/jquery.dataTables.min.js"></script>
    <script src="/seller-theme/js/charts/morris/raphael-min.js" cache="false"></script>
    <script src="/seller-theme/js/charts/morris/morris.min.js" cache="false"></script>
    <script src="/seller-theme/js/charts/sparkline/jquery.sparkline.min.js"></script>
    <script src="/seller-theme/js/datepicker/bootstrap-datepicker.js"></script>
    <script src="/seller-theme/js/libs/moment.min.js"></script>
    <script src="/seller-theme/js/combodate/combodate.js" cache="false"></script>
    <script src="/seller-theme/js/slider/bootstrap-slider.js"></script>
    <script src="/seller-theme/js/select2/select2.min.js" cache="false"></script>
    <script src="/seller-theme/js/charts/flot/jquery.flot.min.js" cache="false"></script>
    <script src="/seller-theme/js/charts/flot/jquery.flot.tooltip.min.js" cache="false"></script>
    <script src="/seller-theme/js/charts/flot/jquery.flot.resize.js" cache="false"></script>
    <script src="/seller-theme/js/charts/flot/jquery.flot.orderBars.js" cache="false"></script>
    <script src="/seller-theme/js/charts/flot/jquery.flot.pie.min.js" cache="false"></script>
    <script src="/seller-theme/js/charts/flot/jquery.flot.time.js" cache="false"></script>
    <script src="/seller-theme/js/wysiwyg/jquery.hotkeys.js" cache="false"></script>
    <script src="/seller-theme/js/wysihtml5/wysihtml5.js" cache="false"></script>
    <script src="/seller-theme/js/dropzone/dropzone.js" cache="false"></script>
    <script src="/seller-theme/js/bootstrap-tagsinput.min.js" cache="false"></script>

    <script src="/seller-theme/js/app.js"></script>
    <script src="/seller-theme/js/app.plugin.js"></script>
    <script src="/seller-theme/js/app.data.js"></script>

    <script src="/js/seller.js"></script>

    <script>
        function toggleSidebar(){
            $.post("/seller/settings", {switch_toggle: "true"});
        }
    </script>
</head>
<body>
<section class="hbox stretch">
    <?php if ($uas->isAuthenticated() && !$pageManager->getCurrentPage()->noAuth()) { ?>
    <!-- .aside -->
    <aside class="bg-success dk aside-sm <?php echo $uas->getUser()->getBigSizeBar() ? "" : "nav-vertical"; ?>" id="nav">
        <section class="vbox">
            <header class="nav-bar">
                <a class="btn btn-link visible-xs" data-toggle="class:nav-off-screen" data-target="#nav">
                    <i class="fa fa-bars"></i>
                </a>
                <a href="#" class="nav-brand" data-toggle="fullscreen">payivy</a>
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
                                    echo '<li class=\'dropdown-submenu ' . ($category->isCurrent() ? 'active' : '') . '\'>';
                                    echo '<a href=\'' . $link . '\'>' . (($alerts > 0) ? ('<b class=\'badge bg-danger pull-right\'>' . $alerts . '</b>') : '') . '<i class=\'fa ' . $category->getIcon() . '\'></i><span>' . $category->getName() . '</span></a>';
                                    echo '<ul class=\'dropdown-menu\'>';

                                    foreach ($pages as $page) {
                                        echo '<li><a href=\'' . $page->getLink() . '\'>' . $page->getName() . '</a></li>';
                                    }

                                    echo '</ul>';
                                    echo '</li>';
                                } else {
                                    echo '<li class=\'' . ($category->isCurrent() ? 'active' : '') . '\'>';
                                    echo '<a href=\'' . $link . '\'>' . (($alerts > 0) ? ('<b class=\'badge bg-primary pull-right\'>' . $alerts . '</b>') : '') . '<i class=\'fa ' . $category->getIcon() . '\'></i><span>' . $category->getName() . '</span></a>';
                                    echo '</li>';
                                }
                            }
                        }
                        ?>
                    </ul>
                </nav>
            </section>
            <footer class="footer bg-gradient hidden-xs">
                <a class="btn btn-sm btn-link m-l-n-sm" data-toggle="class:nav-vertical" href="#nav" onclick="toggleSidebar();">
                    <i class="fa fa-bars"></i>
                </a>
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
        <?php } ?>