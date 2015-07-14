<?php
function ___header($title = '', $fixedfooter = false, $productOrUserPage = false) {
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

        <meta name='description'
              content='PayIvy is an online marketplace for all types of online products. If you want to sell your virtual items now, PayIvy is your one stop.'>
        <meta name='keywords' content='payivy, virtual marketplace, sell online, online shop, online selling'>
        <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1'>

        <meta name="google-site-verification" content="NZDqac_qv_UnuCL-BDk9UH4Nz9spOTRg4-8xQ2llTgk" />

        <title>PayIvy <?php if($title != '') { echo ' | ' . $title; } ?></title>
        <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="/themes/home/css/style.css">
        <link rel="stylesheet" type="text/css" href="/themes/home/css/selectize.bootstrap3.css">
        <link rel="stylesheet" type="text/css" href="/themes/home/css/selectize.default.css">
        <link rel="stylesheet" type="text/css" href="/themes/home/css/selectize.css">
        <link rel="stylesheet" type="text/css" href="/css/lightbox.css">

        <link href='//fonts.googleapis.com/css?family=Open+Sans:600italic,400,300,600,700' rel='stylesheet'
              type='text/css'>

        <!-- fontawesome, should be downloaded and stored locally in production -->
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/js/bootstrap.min.js"></script>
        <script src='//www.google.com/recaptcha/api.js'></script>

        <script>
            $(function() {
                $('.notification').click(function () {
                    $(this).remove();
                });

                setTimeout(function () {
                    $('.notification').fadeOut('slow');
                }, 10000);
            });
        </script>
    </head>
    <body <?php if($fixedfooter == true) { echo "class='fixed-footer'"; } if($productOrUserPage || $fixedfooter) { echo 'style="background-color:#fafafa;"'; } ?>>

    <nav class="navbar navbar-default navbar-static-top pi-navbar home-nav">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/"><img src="/themes/home/img/payivy-full.png" height="50px"></a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="/">Home</a></li>
                    <?php if(!$productOrUserPage) {?>
                    <li><a href="/pricing">Pricing</a></li>
                    <li><a href="/features">Features</a></li>
        <?php }?>
                    <li><a href="/seller/login">Sign In</a></li>
                    <li><a href="/seller/register" id="pi-signupbtn">Sign Up</a></li>
                </ul>

            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>
<?php
}