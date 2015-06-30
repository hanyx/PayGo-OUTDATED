<?php
function ___footer() {
    global $config;
    ?>
    <section class="footer fixed">
        <div class="container">
            <div class="navbar footer-navigation navbar-collapse">
                <div class="navbar-inner">
                    <ul class="nav">
                        <li><a href="http://newnotefinancial.com/" class="footer-nnf"><img src="//uploads.webflow.com/5578e7f1be846a870957056a/5580f3dc5fd01fd44f5e3e99_Newnotefinancial-logo.jpg" width="178"></a>
                        </li>
                        <li><a href="#">Terms and Conditions</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="<?php echo $config['url']['protocol'] . 'support.' . $config['url']['domain']; ?>">Support</a></li>
                        <li><a href="<?php echo $config['url']['protocol'] . 'support.' . $config['url']['domain']; ?>">Contact</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src='//www.google.com/recaptcha/api.js'></script>
    </body>

    </html>
<?php
}