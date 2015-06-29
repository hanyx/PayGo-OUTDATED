<?php
function __header($title = false) {
    global $uas, $pageManager, $url;

    if ($title === false) {
        $title = $pageManager->getCurrentPage()->getName();
    }
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

        <meta name="description" content="PayIvy is an online marketplace for all types of online products. If you want to sell your virtual items now, PayIvy is your one stop.">
        <meta name="keywords" content="payivy, virtual marketplace, sell online, online shop, online selling">

        <title>PayIvy | <?php echo $title; ?></title>

        <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="/seller-theme/css/selectize.css">
        <link rel="stylesheet" type="text/css" href="/seller-theme/css/selectize.default.css">
        <link rel="stylesheet" type="text/css" href="/seller-theme/css/style.css">
        <link rel="stylesheet" type="text/css" href="/css/datatables.css">
        <link rel="stylesheet" type="text/css" href="/css/dropzone.css">
        <link href='//fonts.googleapis.com/css?family=Open+Sans:600italic,400,300,600,700' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="//brianreavis.github.io/selectize.js/js/selectize.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.2/raphael-min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/Chart.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/Chart-patched.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/excanvas.min.js"></script>

        <script src='//www.google.com/recaptcha/api.js'></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.colorhelpers.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.canvas.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.categories.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.crosshair.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.errorbars.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.fillbetween.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.image.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.navigate.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.pie.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.resize.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.selection.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.stack.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.symbol.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.threshold.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/jquery.flot.time.min.js"></script>
        <script type="text/javascript" src="/seller-theme/js/morris.min.js"></script>
        <script type="text/javascript" src="/js/jquery.datatables.min.js"></script>
        <script type="text/javascript" src="/js/dropzone.js"></script>

        <style>
            .tooltip-inner {
                max-width: 250px;
            }

            .dataTables_wrapper .table {
                border: 0;
            }
        </style>

        <script>
            $(function() {
                $('.selectize').selectize();

                $('.selectize-multiple').selectize({
                    maxItems: 100
                });
            });
        </script>
    </head>

    <body>

    <?php
    if ($uas->isAuthenticated()) {
        $navCategory = false;

        foreach ($pageManager->getCategories() as $category) {
            foreach ($category->getPages() as $page) {
                if ($page->isCurrentPage()) {
                    $navCategory = $category;
                    break 2;
                }
            }
        }
        ?>
        <section class="sidebar">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">


                <nav class="nav sidebar-nav">

                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle page-toggle" data-toggle="dropdown" role="button"
                           aria-haspopup="true" aria-expanded="false">
                            <img class="logo" src="/seller-theme/img/payivy-white.png"></img>
                            <?php echo $navCategory !== false ? $navCategory->getName() : ''; ?> <i class="tssts fa fa-angle-down"></i>
                        </a>
                        <ul class="dropdown-menu page-dropdown">
                            <?php
                            foreach ($pageManager->getCategories() as $category) {
                                if (!$category->isHidden() && $category->checkAuth($url, $uas, true)) {
                                    $pages = $category->getPages();
                                    if (count($pages) >= 1) {
                                        $link = $pages[0]->getLink();
                                    } else {
                                        $link = '#';
                                    }

                                    echo '<li><a href="' . $link . '">' . $category->getName() . '</a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </li>
                    <div class="clearfix"></div>
                    <?php
                    if ($navCategory != false) {
                        foreach ($navCategory->getPages() as $page) {
                            $classes = array();

                            if ($page->isCurrentPage()) {
                                $classes[] = 'active';
                            }

                            if ($page->getPrimaryAction()) {
                                $classes[] = 'primary-action';
                            }

                            echo '<li><a ' . (count($classes) != 0 ? ('class=\'' . implode(' ', $classes) . '\'') : '') . ' href="' . $page->getLink() . '">' . $page->getName() . '</a></li>';
                        }
                    }
                    ?>
                </nav>
            </div>
        </section>



        <nav class="nav top-nav">

            <li class="dropdown">
                <a href="#" class="dropdown-toggle " data-toggle="dropdown" role="button" aria-haspopup="true"
                   aria-expanded="false">
                    <?php echo $uas->getUser()->getUsername(); ?> <i class="tssts fa fa-angle-down"></i>
                </a>
                <ul class="dropdown-menu pull-right top-nav-drop" style="">
                    <li><a href="/seller">Dashboard</a>
                    </li>
                    <li><a href="/seller/settings">Settings</a>
                    </li>
                    <li><a href="/seller/logout">Log Out</a>
                    </li>
                </ul>
            </li>
        </nav>

    <?php
    }
    ?>

    <section class="page">

        <h5 class="page-title"><?php echo $title ?></h5>
        <h5 class="page-subtitle"><?php echo $pageManager->getCurrentPage()->getSubtext(); ?></h5>

<?php
}