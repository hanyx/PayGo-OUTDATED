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

if (isset($_POST['title']) && isset($_POST['price']) && isset($_POST['description']) && isset($_POST['details']) && isset($_POST['aff_percent']) && isset($_POST['secondary-aff-link'])) {
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
    if (!(count($url) == 4 && $url[2] == 'edit') && is_numeric($_POST['type'])) {
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
    } else if (!(count($url) == 4 && $url[2] == 'edit') && $_POST['type'] == '') {
        $uas->addMessage(new ErrorSuccessMessage('You must supply a product type'));
    } else if (isset($_POST['affiliate-enabled']) && $_POST['affiliate-enabled'] == '1' && $_POST['aff_percent'] == '') {
        $uas->addMessage(new ErrorSuccessMessage('Affiliate percent cannot be empty'));
    } else if (isset($_POST['affiliate-enabled']) && $_POST['affiliate-enabled'] == '1' && !is_numeric($_POST['aff_percent'])) {
        $uas->addMessage(new ErrorSuccessMessage('Invalid affiliate percent'));
    } else {
        switch ($product->getType()) {
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
}

__header(((count($url) == 4 && $url[2] == 'edit') ? 'Edit' : 'Create') . ' Product');
?>
    <section class='wrapper'>
        <div class='clearfix'>
            <?php $uas->printMessages(); ?>
        </div>
        <?php if ($displayForm) { ?>
            <div class="row">
                <div class="container-fluid">
                    <form class='form-horizontal col-md-8 col-md-offset-2 mtop' method="post">

                        <div class="form-group form-group-lg col-md-8">
                            <div class="">
                                <input type="text" class="form-control" name="title" placeholder="Title" value="<?php echo $product->getTitle(); ?>"/>
                            </div>
                        </div>
                        <div class="form-group form-group-lg col-md-4">
                            <div class="">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Amount" name="price" value="<?php echo $product->getPrice(); ?>">
                                    <span class="input-group-addon">USD</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group form-group-lg col-md-12">
                            <select name="currency[]" multiple class="selectize-multiple" id="product-currencies">
                                <option value="">Currencies</option>
                                <option value="<?php echo ProductCurrency::PAYPAL; ?>" <?php echo $product->acceptsCurrency(ProductCurrency::PAYPAL) ? 'selected=\'1\'' : ''; ?>>PayPal</option>
                                <option value="<?php echo ProductCurrency::PAYPALSUB; ?>" <?php echo $product->acceptsCurrency(ProductCurrency::PAYPALSUB) ? 'selected=\'1\'' : ''; ?>>PayPal Subscription</option>
                                <option value="<?php echo ProductCurrency::BITCOIN; ?>" <?php echo $product->acceptsCurrency(ProductCurrency::BITCOIN) ? 'selected=\'1\'' : ''; ?>>Bitcoin</option>
                                <option value="<?php echo ProductCurrency::LITECOIN; ?>" <?php echo $product->acceptsCurrency(ProductCurrency::LITECOIN) ? 'selected=\'1\'' : ''; ?>>Litecoin</option>
                                <option value="<?php echo ProductCurrency::OMNICOIN; ?>" <?php echo $product->acceptsCurrency(ProductCurrency::OMNICOIN) ? 'selected=\'1\'' : ''; ?>>Omnicoin</option>
                            </select>
                        </div>

                        <div class="form-group form-group-lg col-md-12 pp-options hide">
                            <label class="pull-left control-label">Require Shipping Address</label>
                            <div class="">
                                <label class="switch">
                                    <input type="checkbox" name="require-shipping" value="1" <?php echo $product->getRequireShipping() ? 'checked' : ''; ?>>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group form-group-lg col-md-12 pp-sub-options hide">
                            <label class="control-label" style="margin-bottom: 10px;">PayPal Subscription Length</label>
                            <div class="">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Subscription Length" name="pp-sub-length" value="<?php echo $product->getPaypalSubLength(); ?>">
                                    <div class="input-group-btn input-group-select">
                                        <select class="selectize currency-select" name="pp-sub-unit">
                                            <option value=""></option>
                                            <option value="<?php echo PaypalSubscriptionUnit::DAY; ?>" <?php echo $product->getPaypalSubUnit() == PaypalSubscriptionUnit::DAY ? 'selected=\'1\'' : ''; ?>>Day</option>
                                            <option value="<?php echo PaypalSubscriptionUnit::MONTH; ?>" <?php echo $product->getPaypalSubUnit() == PaypalSubscriptionUnit::MONTH ? 'selected=\'1\'' : ''; ?>>Month</option>
                                            <option value="<?php echo PaypalSubscriptionUnit::YEAR; ?>" <?php echo $product->getPaypalSubUnit() == PaypalSubscriptionUnit::YEAR ? 'selected=\'1\'' : ''; ?>>Year</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group form-group-lg col-md-12">
                            <div class="">
                                <textarea class="form-control wysi" name="description" rows="6" placeholder="Description" name="description"><?php echo $product->getDescription(); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group form-group-lg col-md-12">
                            <div class="">
                                <select class="selectize product-type-dropdown" name="type" <?php echo ((count($url) == 4 && $url[2] == 'edit') ? 'disabled' : ''); ?>>
                                    <option value="">Product Type</option>
                                    <option value="<?php echo ProductType::DOWNLOAD; ?>" <?php echo $product->getType() == ProductType::DOWNLOAD ? 'selected=\'1\'' : ''; ?>>File Download</option>
                                    <option value="<?php echo ProductType::SERIAL; ?>" <?php echo $product->getType() == ProductType::SERIAL ? 'selected=\'1\'' : ''; ?>>Codes/Serials</option>
                                    <option value="<?php echo ProductType::NETSEAL; ?>" <?php echo $product->getType() == ProductType::NETSEAL ? 'selected=\'1\'' : ''; ?>>Netseal</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group form-group-lg col-md-12 product-type product-type-0">
                            <div class="">
                                <select class="selectize" name="file">
                                    <option value="">Select a File</option>
                                    <?php
                                    $files = $uas->getUser()->getFiles();
                                    foreach ($files as $file) {
                                        echo '<option ' . (($product->getType() == ProductType::DOWNLOAD && $file->getId() == $product->getFileId()) ? 'selected=1' : '') . ' value=\'' . $file->getId() . '\'>' . $file->getName() . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>


                        <div class="form-group form-group-lg col-md-12 product-type product-type-1">
                            <div class="">
                                <textarea class="form-control" name="details" rows="6" placeholder="Serials separated by commas"><?php echo ($product->getType() == ProductType::SERIAL) ? implode(',', $product->getSerials()) : '' ?></textarea>
                            </div>
                        </div>

                        <div class="form-group form-group-lg col-md-12 product-type product-type-2">
                            <div class="">
                                <label class='control-label netseal-control'>
                                    NetSeal Packages
                                </label>
                                <a id="create-netseal-package" class="netseal-remove btn btn-danger" style="margin-left: 10px;">Remove Package</a>
                                <a id="create-netseal-package" class="netseal-add btn btn-success">Create Package</a>
                            </div>
                        </div>
                        <div class="netseal-packages-children col-md-12 product-type product-type-2">
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

                                    echo "<div class=\"well netseal-package netseal-offset-" . $x . " col-md-12\">
                                        <h3>Package " . $x . "</h3>
                                        <div class=\"form-group form-group-lg col-md-12\">
                                            <input class=\"form-control\" type=\"text\" placeholder=\"Download Link\" name='netseal-link-" . $x . "' value='" . $serial[0] . "'></input>
                                        </div>
                                        <div class=\"form-group form-group-lg col-md-12\">
                                            <input class=\"form-control\" type=\"text\" placeholder=\"Time\" name='netseal-time-" . $x . "' value='" . $serial[1] . "'></input>
                                        </div>
                                        <div class=\"form-group form-group-lg col-md-12\">
                                            <input class=\"form-control\" type=\"text\" placeholder=\"Points\" name='netseal-points-" . $x . "' value='" . $serial[2] . "'></input>
                                        </div>
                                        <div class=\"form-group form-group-lg col-md-12\">
                                            <select class=\"selectize-s\" name='netseal-type-" . $x . "'><option value=\"\"></option>
                                                <option value='0' " . ($serial[3] == '0' ? "selected='1'" : '') . ">Free</option>
                                                <option value='1' " . ($serial[3] == '1' ? "selected='1'" : '') . ">Bronze</option>
                                                <option value='2' " . ($serial[3] == '2' ? "selected='1'" : '') . ">Silver</option>
                                                <option value='3' " . ($serial[3] == '3' ? "selected='1'" : '') . ">Gold</option>
                                                <option value='4' " . ($serial[3] == '4' ? "selected='1'" : '') . ">Platinum</option>
                                                <option value='5' " . ($serial[3] == '5' ? "selected='1'" : '') . ">Diamond</option>
                                            </select>
                                        </div>
                                        <div class=\"form-group form-group-lg col-md-12\">
                                            <input class=\"form-control\" type=\"text\" placeholder=\"Track (optional)\" name='netseal-points-" . $x . "' value='" . $serial[4] . "'></input>
                                        </div>
                                        <div class=\"form-group form-group-lg col-md-12\">
                                            <input class=\"form-control\" type=\"text\" placeholder=\"Netseal Remote API\" name='netseal-points-" . $x . "' value='" . $serial[5] . "'></input>
                                        </div>
                                        <div class=\"clearfix\"></div>
                                    </div>";
                                }
                            }
                            ?>
                        </div>

                        <div class="form-group form-group-lg col-md-12">
                            <label class="pull-left control-label">Affiliates</label>
                            <div class="">
                                <label class="switch affiliates">
                                    <input type="checkbox" class="payment-processor-switch" id="affiliates" name="affiliate-enabled" value="1" <?php echo $product->getAffiliateEnabled() ? 'checked' : ''; ?>>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group form-group-lg col-md-12 affiliate-hidden">
                            <input type="text" class="form-control " placeholder="Affiliate Percent" aria-label="optional" name="aff_percent" value="<?php echo $product->getAffiliatePercent(); ?>">
                        </div>
                        <div class="form-group form-group-lg col-md-12 affiliate-hidden">
                            <input type="text" class="form-control " placeholder="Affiliate Secondary Link" aria-label="optional" name="secondary-aff-link" value="<?php echo $product->getAffiliateSecondaryLink(); ?>">
                        </div>


                        <div class="form-group form-group-lg col-md-12">
                            <label class="pull-left control-label">Advanced</label>
                            <div class="">
                                <label class="switch advanced">
                                    <input type="checkbox" class="payment-processor-switch" id="advanced" value="0">
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group form-group-lg col-md-12 advanced-sibling">
                            <label class="pull-left control-label">Show on profile</label>
                            <div class="">
                                <label class="switch">
                                    <input type="checkbox" name="display" value="1" <?php echo $product->getVisible() ? 'checked' : 'checked'; ?>>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group form-group-lg col-md-12 advanced-sibling">
                            <div class="">
                                <label class="control-label" style=" text-align:left; margin-bottom:20px;">Custom Fields</label>
                                <a style="margin-top:10px; margin-left:15px" class="btn btn-danger pull-right" id="remove-custom-question">Remove Field</a>
                                <a style="margin-top:10px; margin-left:15px" class="btn btn-success pull-right" id="create-custom-question">Add Field</a>
                            </div>
                        </div>

                        <div class="custom-question-container advanced-sibling">
                            <div class='question-entries'>
                                <?php
                                $x = 0;
                                foreach ($product->getQuestions() as $question) {
                                    $x++;
                                    echo "<div class=\"form-group form-group-lg col-md-12 custom-question-" . $x . "\">
                                        <input type=\"text\" class=\"form-control\" placeholder=\"Custom Field " . $x . "\" aria-label=\"optional\" name=\"question-q-" . $x . "\">
                                    </div>";
                                }
                                ?>
                            </div>
                        </div>

                        <div class="form-group form-group-lg col-md-12 advanced-sibling">
                            <div class="">
                                <input type="text" class="form-control" placeholder="Delivery Message" aria-label="optional" name="custom-delivery" value="<?php echo $product->getCustomDelivery(); ?>">
                            </div>
                        </div>
                        <div class="form-group form-group-lg col-md-12 advanced-sibling">
                            <div class="">
                                <input type="text" class="form-control" placeholder="Custom Success URL" aria-label="optional" name="success-url" value="<?php echo $product->getSuccessUrl(); ?>">
                            </div>
                        </div>
                        <div class="form-group form-group-lg col-md-12 ">
                            <div class="form-save">

                                <button type="submit" class="btn btn-success btn-save"><?php echo (count($url) == 4 && $url['2'] == 'edit') ? 'Update' : 'Create'; ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php } ?>
    </section>
    <script>
        $(function() {
            checkCurrency();
            productType();

            if ($('.netseal-packages-children').children().length == 0) {
                addNetseal(1);
            }

            $('.pp-options label').click(function() {
                if ($(this).find('input').is(":checked")) {
                    $('.pp-sub-options').show();
                } else {
                    $('.pp-sub-options').hide();
                }
            });

            $('#product-currencies').change(function() {
                checkCurrency();
            });

            function checkCurrency() {
                if ($('#product-currencies').val() != null) {
                    if ($('#product-currencies').val().indexOf('<?php echo ProductCurrency::PAYPAL; ?>') > -1 || $('#product-currencies').val().indexOf('<?php echo ProductCurrency::PAYPALSUB; ?>') > -1) {
                        $('.pp-options').removeClass('hide');
                    } else {
                        $('.pp-options').addClass('hide');
                    }
                    if ($('#product-currencies').val().indexOf('<?php echo ProductCurrency::PAYPALSUB; ?>') > -1) {
                        $('.pp-sub-options').removeClass('hide');
                    } else {
                        $('.pp-sub-options').addClass('hide');
                    }
                }
            }


            $('.netseal-add').click(function() {
                addNetseal($('.netseal-packages-children').children().length + 1);
            });

            $('.netseal-remove').click(function() {
                var x = $('.netseal-packages-children').children().length;
                if (x > 1) {
                    $('.netseal-offset-' + x).remove();
                }
            });

            function addNetseal(offset) {
                $('.netseal-packages-children').append("<div class=\"well netseal-package netseal-offset-" + offset + " col-md-12\">" +
                    "<h3>Package " + offset + "</h3>" +
                    "<div class=\"form-group form-group-lg col-md-12\">" +
                        "<input class=\"form-control\" type=\"text\" placeholder=\"Download Link\" name='netseal-link-" + offset + "'></input>" +
                    "</div>" +
                    "<div class=\"form-group form-group-lg col-md-12\">" +
                        "<input class=\"form-control\" type=\"text\" placeholder=\"Time\" name='netseal-time-" + offset + "'></input>" +
                    "</div>" +
                    "<div class=\"form-group form-group-lg col-md-12\">" +
                        "<input class=\"form-control\" type=\"text\" placeholder=\"Points\" name='netseal-points-" + offset + "'></input>" +
                    "</div>" +
                    "<div class=\"form-group form-group-lg col-md-12\">" +
                        "<select class=\"selectize-s\" name='netseal-type-" + offset + "'><option value=\"\"></option>" +
                            "<option value=\"0\">Free</option>" +
                            "<option value=\"1\">Bronze</option>" +
                            "<option value=\"2\">Silver</option>" +
                            "<option value=\"3\">Gold</option>" +
                            "<option value=\"4\">Platinum</option>" +
                            "<option value=\"5\">Diamond</option>" +
                        "</select>" +
                    "</div>" +
                    "<div class=\"form-group form-group-lg col-md-12\">" +
                        "<input class=\"form-control\" type=\"text\" placeholder=\"Track (optional)\" name='netseal-points-" + offset + "'></input>" +
                    "</div>" +
                    "<div class=\"form-group form-group-lg col-md-12\">" +
                        "<input class=\"form-control\" type=\"text\" placeholder=\"Netseal Remote API\" name='netseal-points-" + offset + "'></input>" +
                    "</div>" +
                    "<div class=\"clearfix\"></div>" +
                "</div>");

                $('.selectize-s').selectize();
            }

            $('#create-custom-question').click(function() {
                addQuestion($('.question-entries').children().length + 1);
            });

            $('#remove-custom-question').click(function() {
                var x = $('.question-entries').children().length;
                if (x > 0) {
                    $('.custom-question-' + x).remove();
                }
            });

            function addQuestion(offset) {
                $('.question-entries').append("<div class=\"form-group form-group-lg col-md-12 custom-question-" + offset + "\">" +
                    "<input type=\"text\" class=\"form-control\" placeholder=\"Custom Field " + offset + "\" aria-label=\"optional\" name=\"question-q-" + offset + "\">" +
                "</div>");
            }

            $(".product-type-dropdown").change(function() {
                productType();
            });

            function productType() {
                $('.product-type').hide()
                $('.product-type-' + $(".product-type-dropdown").val()).show()
            }

            $('label.affiliates').click(function () {
                if ($(this).find('input').is(":checked")) {
                    $('.affiliate-hidden').show();
                } else {
                    $('.affiliate-hidden').hide();
                }
            });

            $('label.advanced').click(function () {
                if ($(this).find('input').is(":checked")) {
                    $('.advanced-sibling').show();
                } else {
                    $('.advanced-sibling').hide();
                }
            });
        });
    </script>
<?php
__footer();