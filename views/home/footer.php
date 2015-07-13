<?php
function ___footer($fixedfooter = false) {
    global $config;
    ?>
    <section class="footer <?php if($fixedfooter) {echo "fixed";} ?>">
        <div class="container">
            <div class="navbar footer-navigation navbar-collapse">
                <div class="navbar-inner">
                    <ul class="nav">
                        <li><a href="http://newnotefinancial.com/" class="footer-nnf"><img src="//uploads.webflow.com/5578e7f1be846a870957056a/5580f3dc5fd01fd44f5e3e99_Newnotefinancial-logo.jpg" width="178"></a>
                        </li>
                        <li><a href="#">Terms and Conditions</a></li>
                        <li><a href="//www.iubenda.com/privacy-policy/954106" class="no-brand iubenda-nostyle iubenda-embed" title="Privacy Policy">Privacy Policy</a></li>
                        <li><a href="<?php echo $config['url']['protocol'] . 'support.' . $config['url']['domain']; ?>">Support</a></li>
                        <li><a href="<?php echo $config['url']['protocol'] . 'support.' . $config['url']['domain']; ?>">Contact</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    </body>

    </html>
<script type="text/javascript">(function (w,d) {var loader = function () {var s = d.createElement("script"), tag = d.getElementsByTagName("script")[0]; s.src = "//cdn.iubenda.com/iubenda.js"; tag.parentNode.insertBefore(s,tag);}; if(w.addEventListener){w.addEventListener("load", loader, false);}else if(w.attachEvent){w.attachEvent("onload", loader);}else{w.onload = loader;}})(window, document);</script>
    <script type="text/javascript">

$(function() {
  $('a[href*=#].smoothscroll:not([href=#])').click(function() {
    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
      if (target.length) {
        $('html,body').animate({
          scrollTop: target.offset().top
        }, 300);
        return false;
      }
    }
  });
});

<?php if($fixedfooter) {
        ?>
        $(document).ready(function() {

            var bumpIt = function() {
                    console.log('called bump');
                    $('body.fixed-footer').css('margin-bottom', $('.footer.fixed').height() + 20);
                    console.log($('.footer.fixed').height());
                    console.log($('body.fixed-footer'));
                },
                didResize = false;

            bumpIt();

            $(window).resize(function() {
                didResize = true;
            });
            setInterval(function() {
                if(didResize) {
                    didResize = false;
                    bumpIt();
                    console.log('resize');
                }
            }, 250);

            $('.notification').click(function(){
                console.log($(this));
                $(this).remove();
            });
            setTimeout(function() {
                $('.notification').fadeOut('slow');
            }, 10000);


        });

    <?php
    }?>

</script>
<?php
}