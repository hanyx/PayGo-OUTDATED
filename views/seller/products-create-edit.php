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

if (count($url) == 4 && $url[3] == 'upload') {
    if (!empty($_FILES)) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (preg_grep('/' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) . '/i' , $config['upload']['allowedFiles'])) {
            if ($_FILES['file']['size'] < 50000000) {
                $fileHandler = new FileHandler();

                $fileHandler->setOwner($uas->getUser()->getId());
                $fileHandler->setExtension(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

                $fileHandler->create();

                move_uploaded_file($_FILES['file']['tmp_name'], $config['upload']['directory'] . $fileHandler->getFile());

                $_SESSION['file_id'] = $fileHandler->getFileId();

                die();
            } else {
                die($_SESSION['file_error'] = 'FILE_OVERSIZE');
            }
        } else {
            die($_SESSION['file_error'] = 'BAD_FILETYPE');
        }
    }
    die($_SESSION['file_error'] = 'UPLOAD_ERROR');
}

if (isset($_POST['title']) && isset($_POST['price']) && isset($_POST['description']) && isset($_POST['type']) && isset($_POST['details']) && isset($_POST['aff_percent']) && isset($_POST['secondary-aff-link'])) {
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
    } else if ($_POST['price'] <= 0) {
        $uas->addMessage(new ErrorSuccessMessage('Product price must be greater than 0'));
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
                if (isset($_SESSION['file_error']) && $_SESSION['file_error'] != '') {
                    switch ($_SESSION['file_error']) {
                        case 'UPLOAD_ERROR':
                            $uas->addMessage(new ErrorSuccessMessage('An error occurred while uploading your file'));
                            break;
                        case 'BAD_FILETYPE':
                            $uas->addMessage(new ErrorSuccessMessage('Uploaded file must be one of the following types: ' . implode(', ', $config['upload']['allowedFiles'])));
                            break;
                        case 'FILE_OVERSIZE':
                            $uas->addMessage(new ErrorSuccessMessage('Uploaded file must be under 50 MB'));
                            break;
                    }
                } else if (!isset($_SESSION['file_id']) || $_SESSION['file_id'] == '') {
                    $uas->addMessage(new ErrorSuccessMessage('No file uploaded'));
                } else {
                    $fileHandler = new FileHandler();

                    $fileHandler->readByFileId($_SESSION['file_id']);

                    $product->setFileId($fileHandler->getId());
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
    }

    $_SESSION['file_id'] = '';
    $_SESSION['file_error'] = '';
}

include_once('header.php');
?>
	<section id='content'>
		<section class='main padder'>
			<div class='clearfix'>
				<h4><i class='fa fa-plus'></i> Create Product</h4>
				<?php $uas->printMessages(); ?>
			</div>
            <?php if ($displayForm) { ?>
                <div class='row'>
                    <div class='col-sm-12'>
                        <section class='panel' data-initialize='wizard' id='form-wizard'>
                            <div class='wizard clearfix'>
                                <ul class='steps'>
                                    <li data-step='1' class='active'><span class='badge badge-info'>1</span></li>
                                    <li data-step='2'><span class='badge'>2</span></li>
                                    <li data-step='3'><span class='badge'>3</span></li>
                                </ul>
                            </div>
                            <div class='step-content'>
                                <form action='' method='post' id='form'>
                                    <div class='step-pane active' data-step='1'>
                                        <p>Title:</p>
                                        <div class='form-group' id='product-title'>
                                            <input name='title' type='text' class='input-sm form-control' value='<?php echo $product->getTitle(); ?>'>
                                        </div>
                                        <div class='line line-dashed m-t-large'></div>
                                        <p>Price:</p>
                                        <div class='input-group'>
                                            <span class='input-group-addon'>$</span>
                                            <input name='price' type='number' class='input-sm form-control' value='<?php echo $product->getPrice(); ?>'>
                                        </div>
                                        <div class='line line-dashed m-t-large'></div>
                                        <p>Currencies:</p>
                                        <div class='checkbox'>
                                            <label class='checkbox-custom'>
                                                <input type='checkbox' id='paypal-option' name='paypal' value='1' <?php echo $product->acceptsCurrency(ProductCurrency::PAYPAL) ? 'checked=\'1\'' : ''; ?>>
                                                <i class='fa fa-check-square-o'></i>
                                                PayPal
                                            </label>
                                        </div>
                                        <div class='checkbox'>
                                            <label class='checkbox-custom'>
                                                <input type='checkbox' id='paypal-sub-option' name='paypal-sub' value='1' <?php echo $product->acceptsCurrency(ProductCurrency::PAYPALSUB) ? 'checked=\'1\'' : ''; ?>>
                                                <i class='fa fa-check-square-o'></i>
                                                PayPal Subscription
                                            </label>
                                        </div>
                                        <div class='checkbox'>
                                            <label class='checkbox-custom'>
                                                <input type='checkbox' name='bitcoin' value='1' <?php echo $product->acceptsCurrency(ProductCurrency::BITCOIN) ? 'checked=\'1\'' : ''; ?>>
                                                <i class='fa fa-check-square-o'></i>
                                                BitCoin
                                            </label>
                                        </div>
                                        <div class='checkbox'>
                                            <label class='checkbox-custom'>
                                                <input type='checkbox' name='litecoin' value='1' <?php echo $product->acceptsCurrency(ProductCurrency::LITECOIN) ? 'checked=\'1\'' : ''; ?>>
                                                <i class='fa fa-check-square-o'></i>
                                                LiteCoin
                                            </label>
                                        </div>
                                        <div class='checkbox'>
                                            <label class='checkbox-custom'>
                                                <input type='checkbox' name='omnicoin' value='1' <?php echo $product->acceptsCurrency(ProductCurrency::OMNICOIN) ? 'checked=\'1\'' : ''; ?>>
                                                <i class='fa fa-check-square-o'></i>
                                                OmniCoin
                                            </label>
                                        </div>
                                        <div class='pp-sub-options <?php echo $product->acceptsCurrency(ProductCurrency::PAYPALSUB) ? '' : 'hide'; ?>'>
                                            <div class='line line-dashed m-t-large'></div>
                                            <p>Paypal Subscription Length:</p>
                                            <input name='pp-sub-length' type='number' class='input-sm form-control' value='<?php echo $product->getPaypalSubLength(); ?>'>
                                            <br>
                                            <p>Paypal Subscription Unit:</p>
                                            <select name='pp-sub-unit' class='select2' data-placeholder='Choose a Type' style='width:260px;'>
                                                <option></option>
                                                <option value='0' <?php echo $product->getPaypalSubUnit() == 0 ? 'selected=\'1\'' : ''?>>Days</option>
                                                <option value='1' <?php echo $product->getPaypalSubUnit() == 1 ? 'selected=\'1\'' : ''?>>Months</option>
                                                <option value='2' <?php echo $product->getPaypalSubUnit() == 2 ? 'selected=\'1\'' : ''?>>Years</option>
                                            </select>
                                        </div>
                                        <div class='pp-options <?php echo ($product->acceptsCurrency(ProductCurrency::PAYPAL) || $product->acceptsCurrency(ProductCurrency::PAYPALSUB)) ? '' : 'hide'; ?>'>
                                            <div class='line line-dashed m-t-large'></div>
                                            <div class='checkbox'>
                                                <label class='checkbox-custom'>
                                                    <input type='checkbox' name='require-shipping' value='1' <?php echo $product->getRequireShipping() ? 'checked=\'1\'' : ''; ?>>
                                                    <i class='fa fa-check-square-o'></i>
                                                    Require shipping address (Paypal Only)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='step-pane' data-step='2'>
                                        <p>Description:</p>
                                        <textarea name='description' type='text' class='input-sm form-control' id='description' style='height: 160px;' ><?php echo $product->getDescription(); ?></textarea>
                                        <div class='line line-dashed m-t-large'></div>
                                        <p>Product Type:</p>
                                        <?php echo (count($url) == 4 && $url[2] == 'edit') ? '<input type=\'hidden\' name=\'type\' value=\'' . $product->getType() . '\'>' : ''; ?>
                                        <select class='select2' id='type' data-placeholder='Choose a Type' style='width:260px' <?php echo (count($url) == 4 && $url[2] == 'edit') ? 'disabled=\'1\'' : 'name=\'type\'' ?>>
                                            <option></option>
                                            <option value='0' <?php echo $product->getType() == 0 ? 'selected=\'1\'' : ''; ?>>Download</option>
                                            <option value='1' <?php echo $product->getType() == 1 ? 'selected=\'1\'' : ''; ?>>Codes/Serials/Etc.</option>
                                            <option value='2' <?php echo $product->getType() == 2 ? 'selected=\'1\'' : ''; ?>>Netseal</option>
                                        </select>
                                        <br>
                                    </div>
                                    <div class='step-pane' data-step='3'>
                                        <div class='product-type product-type-0 <?php echo $product->getType() == 0 ? '' : 'hide'; ?>'>
                                            <div class='form-group text-center'>
                                                <div class='fileupload dropzone'></div>
                                                <i>Only the last file uploaded will be used. Other files will be discarded</i>
                                            </div>
                                        </div>
                                        <div class='product-type product-type-1 <?php echo $product->getType() == 1 ? '' : 'hide'; ?>'>
                                            <p>Codes/Serials/Etc:</p>
                                            <textarea name='details' type='text' class='input-sm form-control' id='description' style='height: 160px;' placeholder='Codes/Serials/Etc. separated by commas'><?php echo ($product->getType() == ProductType::SERIAL) ? implode(',', $product->getSerials()) : '' ?></textarea>
                                        </div>
                                        <div class='product-type product-type-2 <?php echo $product->getType() == 2 ? '' : 'hide'; ?>'>
                                            <div class='netseal-entries'>
                                                <?php
                                                if ($product->getType() == ProductType::NETSEAL) {
                                                    $x = 0;
                                                    foreach ($product->getSerials() as $serial) {
                                                        $x++;
                                                        echo '<div class=\'netseal-' . $x . '\'>
                                                            ' . ($x != 1 ? '<div class=\'line line-dashed m-t-large\'></div>' : '') . '
                                                            <p>License ' . $x . ':</p>
                                                            <input name=\'netseal-link-' . $x . '\' type=\'text\' class=\'input-sm form-control\' placeholder=\'Download Link\' value=\'' . $serial[0] . '\'>
                                                            <br>
                                                            <input name=\'netseal-time-' . $x . '\' type=\'number\' class=\'input-sm form-control\' placeholder=\'Time\' value=\'' . $serial[1] . '\'>
                                                            <br>
                                                            <input name=\'netseal-points-' . $x . '\' type=\'number\' class=\'input-sm form-control\' placeholder=\'Points\' value=\'' . $serial[2] . '\'>
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
                                                            <input name=\'netseal-track-' . $x . '\' type=\'text\' class=\'input-sm form-control\' placeholder=\'Track (optional)\' value=\'' . $serial[4] . '\'>
                                                            <br>
                                                            <input name=\'netseal-nkey-' . $x . '\' type=\'text\' class=\'input-sm form-control\' placeholder=\'Netseal Remote API\' value=\'' . $serial[5] . '\'>
                                                        </div>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <br>
                                            <button type='button' class='btn btn-success' id='netseal-add'>
                                                <span class='fa fa-plus'></span>
                                            </button>
                                            <button type='button' class='btn btn-success' id='netseal-remove'>
                                                <span class='fa fa-minus'></span>
                                            </button>
                                        </div>
                                        <div class='line line-dashed m-t-large'></div>
                                        <p>Custom Questions:</p>
                                        <div class='question-entries'>
                                            <?php
                                            $x = 0;
                                            foreach ($product->getQuestions() as $question) {
                                                $x++;
                                                echo '<div class=\'question-' . $x . '\'>
                                                        ' . ($x != 1 ? '<div class=\'line line-dashed m-t-large\'></div>' : '') . '
                                                        <p>Question ' . $x . ':</p>
                                                        <input name=\'question-q-' . $x . '\' type=\'text\' class=\'input-sm form-control\' value=\'' . $question . '\'>
                                                    </div>';
                                            }
                                            ?>
                                        </div>
                                        <br>
                                        <button type='button' class='btn btn-success' id='question-add'>
                                            <span class='fa fa-plus'></span>
                                        </button>
                                        <button type='button' class='btn btn-success' id='question-remove'>
                                            <span class='fa fa-minus'></span>
                                        </button>
                                        <div class='line line-dashed m-t-large'></div>
                                        <p>Affiliates:</p>
                                        <div class='checkbox'>
                                            <label class='checkbox-custom'>
                                                <input type='checkbox' name='affiliate-enabled' value='1' <?php echo $product->getAffiliateEnabled() ? 'checked=\'1\'' : ''; ?>>
                                                <i class='fa fa-check-square-o'></i>
                                                Allow users to create affiliate links for this product.
                                            </label>
                                        </div>
                                        <div class='input-group'>
                                            <input name='aff_percent' type='number' class='input-sm form-control' placeholder='Affiliate Percent' value='<?php echo $product->getAffiliatePercent(); ?>'>
                                            <span class='input-group-addon'>%</span>
                                        </div>
                                        <br>
                                        <input name='secondary-aff-link' type='text' class='input-sm form-control' placeholder='Secondary Affiliate Link' value='<?php echo $product->getAffiliateSecondaryLink(); ?>'>
                                        <div class='line line-dashed m-t-large'></div>
                                        <p>Custom Delivery:</p>
                                        <div class='form-group' id='product-title'>
                                            <input name='custom-delivery' placeholder='Optional' type='text' class='input-sm form-control' value='<?php echo $product->getCustomDelivery(); ?>'>
                                        </div>
                                        <div class='line line-dashed m-t-large'></div>
                                        <div class='checkbox'>
                                            <label class='checkbox-custom'>
                                                <input type='checkbox' name='display' value='1' <?php echo $product->getVisible() ? 'checked=\'1\'' : ''; ?>>
                                                <i class='fa fa-check-square-o'></i>
                                                Show on User Page
                                            </label>
                                        </div>
                                    </div>
                                    <div class='actions m-t'>
                                        <button type='button' class='btn btn-white btn-sm btn-prev' disabled='disabled'>Prev</button>
                                        <button type='button' class='btn btn-white btn-sm btn-next' data-last='Submit'>Next</button>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            <?php } ?>
		</section>
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

            $('#form-wizard').on('finished.fu.wizard', function() {
                document.getElementById('form').submit();
            });

            $('#type').change(function() {
                $('.product-type').addClass('hide');

                $('.product-type-' + $(this).val()).removeClass('hide');
            });

            $('.fileupload').dropzone({
                url: '/seller/products/create/upload',
                maxFilesize: 50,
                uploadMultiple: false,
                maxFiles: 10000
            });

            $('#description').wysihtml5();

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
        });
        
        function addNetseal(offset) {
            $('.netseal-entries').append('<div class=\'netseal-' + offset + '\'>\
                ' + (offset != 1 ? '<div class=\'line line-dashed m-t-large\'></div>' : '') + '\
                <p>License ' + offset + ':</p>\
                <input name=\'netseal-link-' + offset + '\' type=\'text\' class=\'input-sm form-control\' placeholder=\'Download Link\'>\
                <br>\
                <input name=\'netseal-time-' + offset + '\' type=\'number\' class=\'input-sm form-control\' placeholder=\'Time\'>\
                <br>\
                <input name=\'netseal-points-' + offset + '\' type=\'number\' class=\'input-sm form-control\' placeholder=\'Points\'>\
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
                <input name=\'netseal-track-' + offset + '\' type=\'text\' class=\'input-sm form-control\' placeholder=\'Track (optional)\'>\
                <br>\
                <input name=\'netseal-nkey-' + offset + '\' type=\'text\' class=\'input-sm form-control\' placeholder=\'Netseal Remote API\'>\
            </div>');
        }

        function addQuestion(offset) {
            $('.question-entries').append('<div class=\'question-' + offset + '\'>\
                ' + (offset != 1 ? '<div class=\'line line-dashed m-t-large\'></div>' : '') + '\
                <p>Question ' + offset + ':</p>\
                <input name=\'question-q-' + offset + '\' type=\'text\' class=\'input-sm form-control\'>\
            </div>');
        }
    </script>
<?php
include_once('footer.php');