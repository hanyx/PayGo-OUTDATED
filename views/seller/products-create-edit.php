<?php
$product = new Product();
$displayForm = true;

if (count($url) == 4 && $url[2] == 'edit') {
    $product = Product::getProduct($url[3]);

    if ($product === false || $product->getSellerId() != $uas->getUser()->getId()) {
        $uas->addMessage(new ErrorSuccessMessage('No product found'));
        $displayForm = false;
    }

}

if (isset($_POST['title']) && isset($_POST['price']) && isset($_POST['description']) && isset($_POST['type']) && isset($_POST['details']) && isset($_POST['aff_percent']) && isset($_POST['secondary-aff-link'])) {
    try {
        NoCSRF::check('products_token', $_POST, true, 60 * 10, false);
        if (!(count($url) == 4 && $url[2] == 'edit')) {
            switch ($_POST['type']) {
                case 0:
                    $product = new ProductDownload();
                    break;
                case 1:
                    $product = new ProductSerial();
                    break;
                case 2:
                    $product = new ProductNetseal();
                    break;
            }
        }

        $currencies = array();

        if (isset($_POST['paypal']) && $_POST['paypal'] == '1') {
            $currencies[] = ProductCurrency::PAYPAL;
        }

        if (isset($_POST['paypal-sub']) && $_POST['paypal-sub'] == '1') {
            $currencies[] = ProductCurrency::PAYPALSUB;
        }

        if (isset($_POST['bitcoin']) && $_POST['bitcoin'] == '1') {
            $currencies[] = ProductCurrency::BITCOIN;
        }

        if (isset($_POST['litecoin']) && $_POST['litecoin'] == '1') {
            $currencies[] = ProductCurrency::LITECOIN;
        }

        if (isset($_POST['omnicoin']) && $_POST['omnicoin'] == '1') {
            $currencies[] = ProductCurrency::OMNICOIN;
        }

        $product->setSellerId($uas->getUser()->getId());
        $product->setTitle(htmlspecialchars($_POST['title'], ENT_QUOTES));
        $product->setPrice((double)$_POST['price']);
        $product->setPaypalSubLength((int)$_POST['pp-sub-length']);
        $product->setPaypalSubUnit((int)$_POST['pp-sub-unit']);
        $product->setCurrency($currencies);
        $product->setDescription(stripTags($_POST['description']));
        if (is_numeric($_POST['type'])) {
            $product->setType($_POST['type']);
        }
        $product->setAffiliateEnabled(isset($_POST['affiliate-enabled']) && $_POST['affiliate-enabled'] == '1');
        $product->setAffiliatePercent((double)$_POST['aff_percent']);
        $product->setAffiliateSecondaryLink(htmlspecialchars($_POST['secondary-aff-link'], ENT_QUOTES));
        $product->setCustomDelivery(htmlspecialchars($_POST['custom-delivery'], ENT_QUOTES));
        $product->setRequireShipping(isset($_POST['require-shipping']) && $_POST['require-shipping'] == '1');
        $product->setVisible(isset($_POST['display']) && $_POST['display'] == '1');
        $product->setSuccessUrl(htmlspecialchars($_POST["success-url"], ENT_QUOTES));

        $questions = array();

        for ($x = 1; $x < 10; $x++) {
            if (isset($_POST['question-q-' . $x])) {
                $questions[] = htmlspecialchars($_POST['question-q-' . $x], ENT_QUOTES);
            }
        }

        $product->setQuestions($questions);

        if ($_POST['title'] == '') {
            $uas->addMessage(new ErrorSuccessMessage('Product title cannot be empty'));
        } else if (strlen($_POST['title']) < 3 || strlen($_POST['title']) > 100) {
            $uas->addMessage(new ErrorSuccessMessage('Product title must be between 3 and 100 characters in length'));
        } else if ($_POST['price'] == '') {
            $uas->addMessage(new ErrorSuccessMessage('Product price cannot be empty'));
        } else if ($_POST['price'] < 0) {
            $uas->addMessage(new ErrorSuccessMessage('Product price cannot be negative'));
        } else if (!is_numeric($_POST['price'])) {
            $uas->addMessage(new ErrorSuccessMessage('Invalid product price'));
        } else if ($_POST['type'] == '') {
            $uas->addMessage(new ErrorSuccessMessage('You must supply a product type'));
        } else if (isset($_POST['affiliate-enabled']) && $_POST['affiliate-enabled'] == '1' && $_POST['aff_percent'] == '') {
            $uas->addMessage(new ErrorSuccessMessage('Affiliate percent cannot be empty'));
        } else if (isset($_POST['affiliate-enabled']) && $_POST['affiliate-enabled'] == '1' && !is_numeric($_POST['aff_percent'])) {
            $uas->addMessage(new ErrorSuccessMessage('Invalid affiliate percent'));
        } else {
            switch ($_POST['type']) {
                case 0:
                    $file = new File();

                    if (!$file->read($_POST['file'])) {
                        $uas->addMessage(new ErrorSuccessMessage('Invalid file'));
                        break;
                    } else {
                        $product->setFileId($_POST['file']);
                    }

                    break;
                case 1:
                    $product->setSerials(explode(',', $_POST['details']));
                    break;
                case 2:
                    $seals = array();

                    for ($x = 1; $x < 10; $x++) {
                        if (isset($_POST['netseal-link-' . $x])) {
                            $seals[] = array(htmlspecialchars($_POST['netseal-link-' . $x], ENT_QUOTES), (int)$_POST['netseal-time-' . $x], (int)$_POST['netseal-points-' . $x], (int)$_POST['netseal-type-' . $x], htmlspecialchars($_POST['netseal-track-' . $x], ENT_QUOTES), htmlspecialchars($_POST['netseal-nkey-' . $x], ENT_QUOTES));
                        }
                    }

                    $product->setSerials($seals);

                    if (count($seals) < 1) {
                        $uas->addMessage(new ErrorSuccessMessage('You must supply at least 1 netseal'));
                    }

                    break;
            }

            if (!$uas->hasMessage()) {
                if (count($url) == 4 && $url[2] == 'edit') {
                    $product->update();

                    $uas->addMessage(new ErrorSuccessMessage('Product successfully updated', false));
                } else {
                    $product->create();

                    $uas->addMessage(new ErrorSuccessMessage('Product successfully created', false));
                }
            }
        };
    } catch (Exception $e){}
}

if (!empty($_FILES)) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if ($_FILES['file']["error"] == UPLOAD_ERR_OK) {
        if (preg_grep('/' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) . '/i', $config['upload']['profilepics'])) {
            if ($_FILES['file']['size'] < 50000000) {
                $fileHandler = new File();

                $fileHandler->setOwner($uas->getUser()->getId());
                $fileHandler->setExtension(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                $fileHandler->setName(htmlspecialchars($_FILES['file']['name']));
                $fileHandler->setHidden(true);

                $fileHandler->create();

                move_uploaded_file($_FILES['file']['tmp_name'], $config['upload']['directory'] . $fileHandler->getFile());

                $product->setProductImg($fileHandler->getId());

                $product->update();

                $uas->addMessage(new ErrorSuccessMessage('Successfully updated your product picture.', false));
            }
        } else {
            $uas->addMessage(new ErrorSuccessMessage('Invalid image format'));
        }
    }
}

__header(((count($url) == 4 && $url[2] == 'edit') ? 'Edit' : 'Create') . ' Product');
?>
    <section class='wrapper'>
        <div class='clearfix'>
            <?php $uas->printMessages(); ?>
        </div>
        <?php if ($displayForm) { ?>
            <div class='row'>
                <div class="col-sm-12">
                    <section class="panel">
                        <div class="panel-body">
                            <form class="bs-example form-horizontal" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="products_token" value="<?php echo NoCSRF::generate('products_token'); ?>"/>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Title</label>
                                    <div class="col-lg-10">
                                        <input name='title' type='text' class='form-control' value='<?php echo $product->getTitle(); ?>'>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Price</label>
                                    <div class="col-lg-10">
                                        <div class='input-group'>
                                            <span class='input-group-addon'>$</span>
                                            <input name='price' type='number' step="any" min="0" class='form-control' value='<?php echo $product->getPrice(); ?>'>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Picture (Optional)</label>
                                    <div class="col-lg-4">
                                        <input name='file' type='file' class='form-control' value=''>
                                    </div>
                                </div>
                                <div class="line line-dashed m-t-large"></div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Currencies</label>
                                    <div class="col-lg-10">
                                        <label class="switch">
                                            <input type='checkbox' id='paypal-option' name='paypal' value='1' <?php echo $product->acceptsCurrency(ProductCurrency::PAYPAL) ? 'checked=\'1\'' : ''; ?>>
                                            <span></span>
                                            PayPal
                                        </label>
                                        <br>
                                        <label class="switch">
                                            <input type='checkbox' id='paypal-sub-option' name='paypal-sub' value='1' <?php echo $product->acceptsCurrency(ProductCurrency::PAYPALSUB) ? 'checked=\'1\'' : ''; ?>>
                                            <span></span>
                                            PayPal Subscription
                                        </label>
                                        <br>
                                        <label class="switch">
                                            <input type='checkbox' name='bitcoin' value='1' <?php echo $product->acceptsCurrency(ProductCurrency::BITCOIN) ? 'checked=\'1\'' : ''; ?>>
                                            <span></span>
                                            Bitcoin
                                        </label>
                                        <br>
                                        <label class="switch">
                                            <input type='checkbox' name='litecoin' value='1' <?php echo $product->acceptsCurrency(ProductCurrency::LITECOIN) ? 'checked=\'1\'' : ''; ?>>
                                            <span></span>
                                            Litecoin
                                        </label>
                                        <br>
                                        <label class="switch">
                                            <input type='checkbox' name='omnicoin' value='1' <?php echo $product->acceptsCurrency(ProductCurrency::OMNICOIN) ? 'checked=\'1\'' : ''; ?>>
                                            <span></span>
                                            Omnicoin

                                        </label>
                                    </div>
                                </div>
                                <div class="line line-dashed m-t-large"></div>
                                <div class='pp-sub-options <?php echo $product->acceptsCurrency(ProductCurrency::PAYPALSUB) ? '' : 'hide'; ?>'>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">PayPal Subscription Length</label>
                                        <div class="col-lg-10">
                                            <input name='pp-sub-length' type='number' class='form-control' value='<?php echo $product->getPaypalSubLength(); ?>'>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">PayPal Subscription Unit</label>
                                        <div class="col-lg-10">
                                            <select name='pp-sub-unit' class='select2' data-placeholder='Choose a Type' style='width:260px;'>
                                                <option></option>
                                                <option value='0' <?php echo $product->getPaypalSubUnit() == 0 ? 'selected=\'1\'' : ''?>>Days</option>
                                                <option value='1' <?php echo $product->getPaypalSubUnit() == 1 ? 'selected=\'1\'' : ''?>>Months</option>
                                                <option value='2' <?php echo $product->getPaypalSubUnit() == 2 ? 'selected=\'1\'' : ''?>>Years</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="line line-dashed m-t-large"></div>
                                </div>
                                <div class='pp-options <?php echo ($product->acceptsCurrency(ProductCurrency::PAYPAL) || $product->acceptsCurrency(ProductCurrency::PAYPALSUB)) ? '' : 'hide'; ?>'>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Require Shipping (PayPal Only)</label>
                                        <div class="col-lg-10">
                                            <label class="switch">
                                                <input type='checkbox' name='require-shipping' value='1' <?php echo $product->getRequireShipping() ? 'checked=\'1\'' : ''; ?>>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="line line-dashed m-t-large"></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Description</label>
                                    <div class="col-lg-10">
                                        <textarea name='description' type='text' class='form-control wysi' id='description' style='height: 160px;' ><?php echo $product->getDescription(); ?></textarea>
                                    </div>
                                </div>
                                <div class="line line-dashed m-t-large"></div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Product Type</label>
                                    <div class="col-lg-10">
                                        <?php echo (count($url) == 4 && $url[2] == 'edit') ? '<input type=\'hidden\' name=\'type\' value=\'' . $product->getType() . '\'>' : ''; ?>
                                        <select class='select2' id='type' data-placeholder='Choose a Type' style='width:260px' <?php echo (count($url) == 4 && $url[2] == 'edit') ? 'disabled=\'1\'' : 'name=\'type\'' ?>>
                                            <option></option>
                                            <option value='0' <?php echo $product->getType() == 0 ? 'selected=\'1\'' : ''; ?>>Download</option>
                                            <option value='1' <?php echo $product->getType() == 1 ? 'selected=\'1\'' : ''; ?>>Codes / Serials</option>
                                            <option value='2' <?php echo $product->getType() == 2 ? 'selected=\'1\'' : ''; ?>>Netseal</option>
                                        </select>
                                    </div>
                                </div>
                                <div class='product-type product-type-0 <?php echo $product->getType() == 0 ? '' : 'hide'; ?>'>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">File </label>
                                        <div class="col-lg-10">
                                            <select style='width:260px' name='file'>
                                                <option></option>
                                                <?php
                                                $files = $uas->getUser()->getFiles();

                                                foreach ($files as $file) {
                                                    echo '<option ' . (($product->getType() == ProductType::DOWNLOAD && $file->getId() == $product->getFileId()) ? 'selected=1' : '') . ' value=\'' . $file->getId() . '\'>' . $file->getName() . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <div class="btn-group btn-group-xs"><button class="btn btn-primary" type="button" data-toggle="tooltip" title="Files can be uploaded and managed from the Product Files page"><i class="fa fa-question"></i></button></div>
                                        </div>
                                    </div>
                                </div>
                                <div class='product-type product-type-1 <?php echo $product->getType() == 1 ? '' : 'hide'; ?>'>
                                    <label class="col-lg-2 control-label">Serials / Codes</label>
                                    <div class="col-lg-10">
                                        <textarea name='details' type='text' class='form-control' id='description' style='height: 160px;' placeholder='Codes / Serials separated by commas'><?php echo ($product->getType() == ProductType::SERIAL) ? implode(',', $product->getSerials()) : '' ?></textarea>
                                    </div>

                                </div>
                                <br/>
                                <div class="line line-dashed m-t-large"></div>
                                <div class='product-type product-type-2 <?php echo $product->getType() == 2 ? '' : 'hide'; ?>'>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Netseal Codes</label>
                                        <div class="col-lg-10">
                                            <div class='netseal-entries'>
                                                <?php
                                                if ($product->getType() == ProductType::NETSEAL) {
                                                    $x = 0;
                                                    foreach ($product->getSerials() as $serial) {
                                                        $x++;
                                                        echo '<div class="panel netseal-' . $x . '"><header class="panel-heading font-bold">License ' . $x . '</header><div class="panel-body">
                                                    ' . ($x != 1 ? '<div class=\'line line-dashed m-t-large\'></div>' : '') . '
                                                    <p>License ' . $x . ':</p>
                                                    <input name=\'netseal-link-' . $x . '\' type=\'text\' class=\'form-control\' placeholder=\'Download Link\' value=\'' . $serial[0] . '\'>
                                                    <br>
                                                    <input name=\'netseal-time-' . $x . '\' type=\'number\' class=\'form-control\' placeholder=\'Time\' value=\'' . $serial[1] . '\'>
                                                    <br>
                                                    <input name=\'netseal-points-' . $x . '\' type=\'number\' class=\'form-control\' placeholder=\'Points\' value=\'' . $serial[2] . '\'>
                                                    <br>
                                                    <select name=\'netseal-type-' . $x . '\' class=\'select2\' data-placeholder=\'Choose a Type\' style=\'width:260px;\'>
                                                        <option></option>
                                                        <option value=\'0\' ' . ($serial[3] == '0' ? 'selected=\'1\'' : '') . '>Free</option>
                                                        <option value=\'1\' ' . ($serial[3] == '1' ? 'selected=\'1\'' : '') . '>Bronze</option>
                                                        <option value=\'2\' ' . ($serial[3] == '2' ? 'selected=\'1\'' : '') . '>Silver</option>
                                                        <option value=\'3\' ' . ($serial[3] == '3' ? 'selected=\'1\'' : '') . '>Gold</option>
                                                        <option value=\'4\' ' . ($serial[3] == '4' ? 'selected=\'1\'' : '') . '>Platinum</option>
                                                        <option value=\'5\' ' . ($serial[3] == '5' ? 'selected=\'1\'' : '') . '>Diamond</option>
                                                    </select>
                                                    <br><br>
                                                    <input name=\'netseal-track-' . $x . '\' type=\'text\' class=\'form-control\' placeholder=\'Track (optional)\' value=\'' . $serial[4] . '\'>
                                                    <br>
                                                    <input name=\'netseal-nkey-' . $x . '\' type=\'text\' class=\'form-control\' placeholder=\'Netseal Remote API\' value=\'' . $serial[5] . '\'>
                                                </div></div>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <br>
                                            <button type='button' class='btn btn-primary' id='netseal-add'>
                                                <span class='fa fa-plus'></span>
                                            </button>
                                            <button type='button' class='btn btn-primary' id='netseal-remove'>
                                                <span class='fa fa-minus'></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="line line-dashed m-t-large"></div>
                                <div class="form-group">

                                    <label class="col-lg-2 control-label">Custom Questions</label>
                                    <div class="col-lg-10">
                                        <div class='question-entries'>
                                            <?php
                                            $x = 0;
                                            foreach ($product->getQuestions() as $question) {
                                                $x++;
                                                echo '<div class="panel question-' . $x . '"><header class="panel-heading font-bold">Question ' . $x . '</header><div class="panel-body">
                                                    <input name=\'question-q-' . $x . '\' type=\'text\' class=\'form-control\' value=\'' . $question . '\'>
                                                </div></div>';
                                            }
                                            ?>
                                        </div>
                                        <br>
                                        <button type='button' class='btn btn-primary' id='question-add'>
                                            <span class='fa fa-plus'></span>
                                        </button>
                                        <button type='button' class='btn btn-primary' id='question-remove'>
                                            <span class='fa fa-minus'></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="line line-dashed m-t-large"></div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Affiliates</label>
                                    <div class="col-lg-10">
                                        <label class="switch">
                                            <input type='checkbox' id="affiliate-enabled" name='affiliate-enabled' value='1' <?php echo $product->getAffiliateEnabled() ? 'checked=\'1\'' : ''; ?>>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                                <div id="affiliates-container" class="hide">
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Affiliate Percent</label>
                                        <div class="col-lg-10">
                                            <input name='aff_percent' type='number' class='form-control' placeholder='Affiliate Percent' value='<?php echo $product->getAffiliatePercent(); ?>'>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">Affiliate Secondary Link</label>
                                        <div class="col-lg-10">
                                            <input name='secondary-aff-link' type='text' class='form-control' placeholder='Optional' value='<?php echo $product->getAffiliateSecondaryLink(); ?>'>
                                        </div>
                                    </div>
                                </div>
                                <div class="line line-dashed m-t-large"></div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Custom Delivery Message</label>
                                    <div class="col-lg-10">
                                        <input name='custom-delivery' placeholder='Optional' type='text' class='form-control' value='<?php echo $product->getCustomDelivery(); ?>'>
                                    </div>
                                </div>
                                <div class="line line-dashed m-t-large"></div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Custom Success Url</label>
                                    <div class="col-lg-10">
                                        <input name='success-url' placeholder='Optional' type='url' class='form-control' value='<?php echo $product->getSuccessUrl(); ?>'>
                                    </div>
                                </div>
                                <div class="line line-dashed m-t-large"></div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Show on User Page</label>
                                    <div class="col-lg-10">
                                        <label class="switch">
                                            <input type='checkbox' name='display' value='1' <?php echo $product->getVisible() ? 'checked=\'1\'' : ''; ?>>
                                            <span></span>
                                        </label>
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
        <?php } ?>
    </section>
    <script>
        $(function() {
            if ($('.netseal-entries').children().length == 0) {
                addNetseal(1);
            }

            $('#paypal-sub-option').change(function() {
                checkPaypalOptions();
                if ($(this).is(':checked')) {
                    $('.pp-sub-options').removeClass('hide');
                } else {
                    $('.pp-sub-options').addClass('hide');
                }
            });

            $('#paypal-option').change(function() {
                checkPaypalOptions();
            });

            function checkPaypalOptions() {
                if ($('#paypal-option').is(':checked') || $('#paypal-sub-option').is(':checked')) {
                    $('.pp-options').removeClass('hide');
                } else {
                    $('.pp-options').addClass('hide');
                }
            }

            $('#type').change(function() {
                $('.product-type').addClass('hide');

                $('.product-type-' + $(this).val()).removeClass('hide');
            });

            $('#netseal-add').click(function() {
                addNetseal($('.netseal-entries').children().length + 1);
            });

            $('#netseal-remove').click(function() {
                var x = $('.netseal-entries').children().length;

                if (x > 1) {
                    $('.netseal-' + x).remove();
                }
            });

            $('#question-add').click(function() {
                addQuestion($('.question-entries').children().length + 1);
            });

            $('#question-remove').click(function() {
                var x = $('.question-entries').children().length;

                if (x > 0) {
                    $('.question-' + x).remove();
                }
            });

            $('#affiliate-enabled').change(function() {
                if ($(this).is(':checked')) {
                    $('#affiliates-container').removeClass('hide');
                } else {
                    $('#affiliates-container').addClass('hide');
                }
            });
        });

        function addNetseal(offset) {
            $('.netseal-entries').append('<div class="panel netseal-' + offset + '"> <header class="panel-heading font-bold">License ' + offset + '</header><div class="panel-body">\
                ' + (offset != 1 ? '<div class=\'line line-dashed m-t-large\'></div>' : '') + '\
                <input name=\'netseal-link-' + offset + '\' type=\'text\' class=\'form-control\' placeholder=\'Download Link\'>\
                <br>\
                <input name=\'netseal-time-' + offset + '\' type=\'number\' class=\'form-control\' placeholder=\'Time\'>\
                <br>\
                <input name=\'netseal-points-' + offset + '\' type=\'number\' class=\'form-control\' placeholder=\'Points\'>\
                <br>\
                <select name=\'netseal-type-' + offset + '\' class=\'select2\' data-placeholder=\'Choose a Type\' style=\'width:260px;\'>\
                    <option></option>\
                    <option value=\'0\'>Free</option>\
                    <option value=\'1\'>Bronze</option>\
                    <option value=\'2\'>Silver</option>\
                    <option value=\'3\'>Gold</option>\
                    <option value=\'4\'>Platinum</option>\
                    <option value=\'5\'>Diamond</option>\
                </select>\
                <br><br>\
                <input name=\'netseal-track-' + offset + '\' type=\'text\' class=\'form-control\' placeholder=\'Track (optional)\'>\
                <br>\
                <input name=\'netseal-nkey-' + offset + '\' type=\'text\' class=\'form-control\' placeholder=\'Netseal Remote API\'>\
            </div></div>');
        }

        function addQuestion(offset) {
            $('.question-entries').append('<div class="panel question-' + offset + '"> <header class="panel-heading font-bold">Question ' + offset + '</header><div class="panel-body">\
                <input name=\'question-q-' + offset + '\' type=\'text\' class=\'form-control\'>\
            </div></div>');
        }
    </script>
<?php
__footer();