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

__header($seller->getUsername() . "'s products");

?><div style=' margin: 70px auto 0 50px;'>
    <h1><?php echo $seller->getUsername() . "'s products"; ?></h1>
<div class="row">

    <?php foreach($seller->getProducts() as $p){
        if(!$p->getVisible()) continue; ?>


    <div class="col-lg-3 col-md-3 col-sm-3">
        <a href="<?php echo $p->getUrl(); ?>">
            <section class="panel">
                <header class="panel-heading bg-white">
                    <div class="text-center h5"><i class="fa fa-3x fa-<?php if($p->getType() == ProductType::SERIAL) { echo 'shopping-cart'; } else { echo 'cloud-download'; } ?> text-success"></i></div>
                </header>
                <div class="panel-body pull-in text-center">
                    <strong><?php echo $p->getTitle(); ?></strong>
                    <br /> $ <?php echo $p->getPrice(); ?> USD
                </div>
            </section>
        </a>
    </div>
    <?php }?>
</div>
    </div>