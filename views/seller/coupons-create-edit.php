<?php

$coupon = new Coupon();
$displayForm = true;

if(count($url) == 4 && $url[2] == 'edit'){
    $coupon = Coupon::getCoupon($url[3]);

    if($coupon === false || $coupon->getSellerId() != $uas->getUser()->getId()){
        $uas->addMessage(new ErrorSuccessMessage('No coupon found'));
        $displayForm = false;
    }
}

if(isset($_POST['name']) && isset($_POST['reduction']) && isset($_POST['maximum'])){
    $products = $uas->getUser()->getProducts();
    $chosen_products = array();

    foreach($products as $p){
        if(isset($_POST['coupon-p-' . $p->getId()])){
            $chosen_products[] = $p->getId();
        }
    }

    if($_POST['name'] == ''){
        $uas->addMessage(new ErrorSuccessMessage('Coupon name cannot be empty'));
    } else if(strlen($_POST['name']) < 3 || strlen($_POST['name']) > 10){
        $uas->addMessage(new ErrorSuccessMessage('Coupon name must be between 3 and 10 characters in length'));
    } else if($_POST['reduction'] == ''){
        $uas->addMessage(new ErrorSuccessMessage('Coupon reduction percent cannot be empty'));
    } else if($_POST['reduction'] <= 0 || $_POST['reduction'] >= 100) {
        $uas->addMessage(new ErrorSuccessMessage('Coupon reduction percent must be between 1 % and 99 %'));
    } else if(!is_numeric($_POST['reduction'])){
        $uas->addMessage(new ErrorSuccessMessage('Invalid coupon reduction percent'));
    } else if($_POST['maximum'] == ''){
        $uas->addMessage(new ErrorSuccessMessage('Maximum used amount can not be empty'));
    } else if($_POST['maximum'] <= 0 ||$_POST['maximum'] > 2147483647){
        $uas->addMessage(new ErrorSuccessMessage('Maximum used amount must be greater then 0 and smaller than 2147483647'));
    } else if(!is_numeric($_POST['maximum'])){
        $uas->addMessage(new ErrorSuccessMessage('Invalid maximum used amount'));
    } else if(count($chosen_products) <= 0){
        $uas->addMessage(new ErrorSuccessMessage('Please select one or more products'));
    }

    $coupon->setName($_POST['name']);
    $coupon->setReduction($_POST['reduction']);
    $coupon->setMaxUsedAmount($_POST['maximum']);
    $coupon->setSellerId($uas->getUser()->getId());

    $coupon->setProducts($chosen_products);

    if(!$uas->hasMessage()){
        if (count($url) == 4 && $url[2] == 'edit') {
            $coupon->update();

            $uas->addMessage(new ErrorSuccessMessage('Coupon successfully updated', false));
        } else {
            $coupon->create();

            $uas->addMessage(new ErrorSuccessMessage('Coupon successfully created', false));
        }
    }
}

include_once('header.php');
?>
<section class='wrapper'>
        <div class='clearfix'>
            <?php $uas->printMessages(); ?>
        </div>

    <?php if($displayForm){ ?>
    <div class='row'>
        <div class="col-sm-12">
            <section class="panel">
                <div class="panel-body">
                    <form class="bs-example form-horizontal" method="post">
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Name</label>
                            <div class="col-lg-10">
                                <input name='name' type='text' class='form-control' value='<?php echo $coupon->getName(); ?>'/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Reduction</label>
                            <div class="col-lg-10">
                                <div class='input-group'>
                                    <input name='reduction' type='number' min="1" max="99" class='form-control' value='<?php echo $coupon->getReduction(); ?>'>
                                    <span class='input-group-addon'>%</span>
                                </div>
                            </div>
                        </div>
                        <?php if(count($url) == 4 && $url['2'] == 'edit'){?>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Used</label>
                            <div class="col-lg-10">
                                <input name='used' type='number' class='form-control' readonly value='<?php echo $coupon->getUsedAmount(); ?>'/>
                            </div>
                        </div>
        <?php }?>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Maximum</label>
                            <div class="col-lg-10">
                                <input name='maximum' type='number' min="1" class='form-control' value='<?php echo $coupon->getMaxUsedAmount(); ?>'/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="products" class="col-lg-2 control-label">Products</label>
                            <div class="col-lg-10">
                                <select class="select2" id="products" data-placeholder="Choose your products" style="width: 256px;" onchange="productsChanged($(this).find(':selected'));">
                                    <option></option>
                                    <?php
                                    foreach($uas->getUser()->getProducts() as $p){
                                        if(!in_array($p->getId(), $coupon->getProducts())){
                                            echo "<option value='" . $p->getId() . "'> " . $p->getTitle() . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div><br/>
                            <div class="col-lg-10 col-lg-offset-2">
                                <div class="bootstrap-tagsinput" id="enteredProducts">
                                    <?php
                                        foreach($coupon->getProducts() as $p){
                                        $product = new Product();
                                            $product->read($p);
                                    ?>
                                            <span class="tag label label-primary" id="coupon-pd-<?php echo $product->getId(); ?>"><?php echo $product->getTitle(); ?>
                                                <input type="hidden" name="coupon-p-<?php echo $product->getId(); ?>" value="<?php echo $product->getId(); ?>"/>
                                                <span data-role="remove" onclick="productsRemove(<?php echo $product->getId(); ?>, '<?php echo htmlspecialchars($product->getTitle()); ?>');"></span>
                                            </span>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="col-lg-offset-2 col-lg-10">
                                <button class="btn btn-sm btn-primary" type="submit"><?php echo (count($url) == 4 && $url['2'] == 'edit') ? 'Update' : 'Create'; ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
    <?php }?>
</section>

<script>
    function productsChanged(e){
        var current = $('#enteredProducts').html();
        current += '<span class="tag label label-primary" id="coupon-pd-' + e.val() + '">' + e.text() + '<input type="hidden" name="coupon-p-' + e.val() + '" value="' + e.val() + '"/><span data-role="remove" onclick="productsRemove(' + e.val() + ');"></span></span>';
        $('#enteredProducts').html(current);

        e.remove();
    }

    function productsRemove(i){
        var layout = $('#coupon-pd-' + i);

        var current = $('#products').html();
        current += "<option value='" + i + "'>" + layout.text() + "</option>";

        $('#products').html(current);

        layout.remove();
    }
</script>

    <?php
include_once('footer.php');