<?php

$coupon = new Coupon();

if(count($url) == 4 && $url[2] == 'edit'){
    $coupon = new Coupon();

    if (!$coupon->read($url[3])|| $coupon->getSellerId() != $uas->getUser()->getId()) {
        $uas->addMessage(new ErrorSuccessMessage('No coupon found'));
        $displayForm = false;
    }
}

if(isset($_POST['name']) && isset($_POST['reduction']) && isset($_POST['maximum'])){
    $products = $uas->getUser()->getProducts();
    $chosen_products = array();

    foreach($_POST['products'] as $p) {
        $product = new Product();

        if ($product->read($p) && $product->getSellerId() == $uas->getUser()->getId()) {
            $chosen_products[] = $p;
        }
    }

    if($_POST['name'] == ''){
        $uas->addMessage(new ErrorSuccessMessage('Coupon name cannot be empty'));
    } else if(!ctype_alnum($_POST['name'])){
        $uas->addMessage(new ErrorSuccessMessage('Coupon name can only contain alphanumerical characters'));
    } else if(strlen($_POST['name']) < 3 || strlen($_POST['name']) > 10) {
        $uas->addMessage(new ErrorSuccessMessage('Coupon name must be between 3 and 10 characters in length'));
    } else if($_POST['reduction'] == ''){
        $uas->addMessage(new ErrorSuccessMessage('Coupon reduction percent cannot be empty'));
    } else if($_POST['reduction'] <= 0 || $_POST['reduction'] >= 100) {
        $uas->addMessage(new ErrorSuccessMessage('Coupon reduction percent must be between 1 % and 99 %'));
    } else if(!is_numeric($_POST['reduction'])){
        $uas->addMessage(new ErrorSuccessMessage('Invalid coupon reduction percent'));
    } else if($_POST['maximum'] == ''){
        $uas->addMessage(new ErrorSuccessMessage('Maximum used amount can not be empty'));
    } else if($_POST['maximum'] < 0 ||$_POST['maximum'] > 2147483647){
        $uas->addMessage(new ErrorSuccessMessage('Maximum used amount must be greater then 0 and smaller than 2147483647'));
    } else if(!is_numeric($_POST['maximum'])){
        $uas->addMessage(new ErrorSuccessMessage('Invalid maximum used amount'));
    } else if(count($chosen_products) <= 0){
        $uas->addMessage(new ErrorSuccessMessage('Please select one or more products'));
    }

    if (count($url) != 4 || $url[2] != 'edit') {
        $check = new Coupon();

        if ($check->readByNameAndSellerId($_POST['name'], $uas->getUser()->getId())) {
            $uas->addMessage(new ErrorSuccessMessage('Coupon name is already in use'));
        }
    }

    $coupon->setName($_POST['name']);
    $coupon->setReduction((int)$_POST['reduction']);
    $coupon->setMaxUsedAmount((int)$_POST['maximum']);
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

__header(((count($url) == 4 && $url[2] == 'edit') ? 'Edit' : 'Create') . ' Coupon');
?>
<div class='clearfix'>
    <?php $uas->printMessages(); ?>
</div>


<div class="row">
    <div class="container-fluid">
        <form class='form-horizontal col-md-8 col-md-offset-2 mtop' method="post">
            <div class="form-group form-group-lg col-md-12 ">
                <input type="text" class="form-control " placeholder="Name" aria-label="optional" name='name' value='<?php echo $coupon->getName(); ?>'>
            </div>
            <div class="form-group form-group-lg col-md-12 ">
                <div class="input-group">
                    <input type="text" class="form-control " placeholder="Reduction" aria-label="optional" name='reduction' value='<?php echo $coupon->getReduction(); ?>'>
                    <span class="input-group-addon">%</span>
                </div>
            </div>
            <div class="form-group form-group-lg col-md-12">
                <label class="control-label">Applicable products</label>
                <select class="selectize-multiple" name="products[]" multiple>
                    <option value="">Pick products</option>
                    <option value="2">Test</option>
                    <option value="3">Test 2</option>
                    <?php
                    foreach($uas->getUser()->getProducts() as $p){
                        if(!in_array($p->getId(), $coupon->getProducts())){
                            echo "<option value='" . $p->getId() . "'> " . $p->getTitle() . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group form-group-lg col-md-12">
                <label class="control-label">Usage limit</label>
                <input name="maximum" type="number" min="0" placeholder="(0 for unlimited)" class="form-control" value="0" value='<?php echo $coupon->getMaxUsedAmount(); ?>'>
            </div>
            <div class="form-group form-group-lg col-md-12 ">
                <div class="form-save">
                    <button type="submit" class="btn btn-success btn-save">Save Coupon</button>
                </div>
            </div>
        </form>
    </div>
</div>

    <?php
__footer();