<?php

if(!isset($url[1])){
    include_once '404.php';
    die();
}
$seller = new User();
if(!$seller->readByUniqueId($url[1]) && !$seller->readByUrlUsername($url[1])){
    include_once '404.php';
    die();
}
if (isset($_POST['email']) && isset($_POST['name']) && isset($_POST['message'])) {
    try {
        NoCSRF::check('user_email_token', $_POST, true, 60 * 10, false);
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $uas->addMessage(new ErrorSuccessMessage('Invalid Email'));
        } else {
            $message = new Message();

            $message->setSender($_POST['email']);
            $message->setRecipient($seller->getId());
            $message->setMessage(stripTags($_POST['message']));

            $message->send();

            $uas->addMessage(new ErrorSuccessMessage('Message sent!', false));
        }
    } catch(Exception $e){}
}

$imgsrc = $seller->getProfilePicSrc($config['upload']['directory']);
___header($seller->getUsername() . "'s Products", false, true, $seller->getUsername() . '\'s products');
$amount=0;
?>


    <section class="seller-info">

        <div class="container">
<?php if($uas->hasMessage(true)) {$uas->printMessages(); echo '<br/>';}; ?>
            <div class="bottomrow">
                <div class="seller-image">
                    <img src="<?php echo $imgsrc; ?>">
                </div>
                <div class="info">
                    <h1><?php echo htmlspecialchars($seller->getUsername()); ?></h1>
                </div>
                <p class="muted"><?php echo htmlspecialchars($seller->getDescription());?></p>
                <button class="btn btn-success pull-right" data-toggle="modal" data-target="#modal-compose"><i class="bi_com-email"></i> </button>
                <input type="text" id="product-filter" class="form-control search-control" placeholder="Search for a product"/>
                <div class="clearfix"></div>
            </div>
        </div>
    </section>

    <section class="seller-products container">
        <div class="row">
            <span id="noprods" style="text-align:center; display:none; margin:20px auto; color:#333;">No products found</span>
            <?php foreach($seller->getProducts() as $product){
        if($product->getVisible()){
        ?>
            <div class="col-md-4 product-container" id="product-container-<?php echo $amount;?>" data-search="<?php echo base64_encode($product->getTitle());?>">
                <a href="<?php echo $product->getUrl(); ?>">
                    <section id="product" class="product-card">
                        <div class="product-image sellerpage-product-image <?php if($product->getProductImg() == '0') {echo  'empty-image empty-image-down'; } ?>" style="background: #72B611;">
                        <span class="helper">
                            <img src="<?php echo $product->getProductImgSrc($config['upload']['directory']); ?>" style="width:100%;height: 100%;">
                        </div>
                        <div class="step-1" id="step1">
                            <div class="product-card-body">
                                <div class="row">
                                    <div class="col-md-7 product-details">
                                        <span class="product-name"><?php echo htmlspecialchars($product->getTitle()); ?></span>
                                        <span class="product-type muted"><?php echo $product->getTypeString(); ?></span>
                                    </div>
                                    <div class="col-md-5 product-pricing">
                                        <span class="price hidden-sm hidden-xs">$ <?php echo $product->getPrice(); ?></span>
                                        <span class="price visible-sm visible-xs" style="text-align: center!important;">$ <?php echo $product->getPrice(); ?></span>

                                        <span class="quantity-left muted"><?php  if($product->getType() == ProductType::SERIAL) {echo $product->makeSerialString(); } else { echo ''; } ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </a>
            </div>
            <?php $amount++; }
} ?>
        </div>
    </section>


    <div class="modal fade" id="modal-compose" tabindex="-1" role="dialog" aria-labelledby="modal-compose">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modalsmth">Contact Seller</h4>
                </div>
                <form method="post">
                    <input type="hidden" name="user_email_token" value="<?php echo NoCSRF::generate('user_email_token'); ?>"/>
                    <div class="modal-body compose-body">
                        <input type="email" class="form-control compose-input" name="email" placeholder="Your E-mail" value="" />
                        <input type="text" class="form-control compose-input" name="name" placeholder="Your Name" value="" />

                        <textarea class="form-control compose-input" name="message" rows="10" placeholder="Message"></textarea>
                            <div class="clearfix"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <input type="submit" class="btn btn-primary" value="Send"/>
                        </div>
                </form>
            </div>
        </div>
    </div>
<script>
    $('#product-filter').on("keyup", function(){
        var matches = 0;
        var productRegExp = $('#product-filter').val().toLowerCase();
        for (var i = 0; i < <?php echo $amount;?>; i++) {
            var element = $('#product-container-'+i);
            var productName = atob(element.attr('data-search')).toLowerCase();
            if(productName.indexOf(productRegExp) < 0){
                element.hide();
            }else{
                element.show();
                $('#noprods').hide();
                matches ++;
            }
        }

        if(matches == 0){
            $('#noprods').css("display", "block");
        }
    });
</script>
</body>
</html>