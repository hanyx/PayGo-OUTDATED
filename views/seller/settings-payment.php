<?php
__header('Payment Settings');

if (isset($_POST['update-password']) && isset($_POST['password-old']) && isset($_POST['password']) && isset($_POST['password-confirm'])) {
    $uas->processUpdatePassword($_POST['password-old'], $_POST['password'], $_POST['password-confirm']);
}

if (isset($_POST['update-payment-details'])) {
    $uas->processUpdatePaymentDetails(isset($_POST['paypal']) ? $_POST['paypal'] : '', isset($_POST['bitcoin']) ? $_POST['bitcoin'] : '', isset($_POST['litecoin']) ? $_POST['litecoin'] : '', isset($_POST['omnicoin']) ? $_POST['omnicoin'] : '');
}
?>
    <section class="wrapper">
        <div class='clearfix'>
            <?php
            $uas->printMessages();
            if (isset($tfr)) {
                $tfr->printMessages();
            }
            ?>
        </div>
        <div class='row'>
            <div class='col-sm-6'>
                <section class='panel'>
                    <header class="panel-heading font-bold">Add Payment Method</header>
                    <div class="panel-body">
                        <form class='form-horizontal'>
                            <div class='form-group'>
                                <div class='col-lg-9 col-lg-offset-3'>
                                    <select id='add-payment-method' class='select2' style='width:260px;'>
                                        <option></option>
                                        <option value='0'>PayPal</option>
                                        <option value='1'>Bitcoin</option>
                                        <option value='2'>Litecoin</option>
                                        <option value='3'>Omnicoin</option>
                                        <option value='4'>Payza</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        <div class='row'>
        </div>
            <div class='col-sm-6'>
                <section class='panel'>
                    <div class='panel-body'>
                        <form class='form-horizontal' method='post' data-validate='parsley' id="form">
                            <div class='form-group'>
                                <div class='col-lg-9 col-lg-offset-3'>
                                    <button type='submit' name='update-payment-details' class='btn btn-primary'>Update Payment Settings</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </section>
    <script>
        $(function() {
            <?php echo $uas->getUser()->getPayPal() != '' ? "printInput(0, '" . $uas->getUser()->getPayPal() . "');" : ""; ?>
            <?php echo $uas->getUser()->getBitcoin() != '' ? "printInput(1, '" . $uas->getUser()->getBitcoin() . "');" : ""; ?>
            <?php echo $uas->getUser()->getLitecoin() != '' ? "printInput(2, '" . $uas->getUser()->getLitecoin() . "');" : ""; ?>
            <?php echo $uas->getUser()->getOmnicoin() != '' ? "printInput(3, '" . $uas->getUser()->getOmnicoin() . "');" : ""; ?>

            $("#add-payment-method").change(function() {
                printInput(parseInt($(this).val()), '');
            });
        });

        function printInput(type, value) {
            switch (type) {
                case 0:
                    var label = "PayPal Email";
                    var name = "paypal";
                    var placeholder = "(e.g. john.doe@gmail.com)";
                    break;
                case 1:
                    var label = "Bitcoin Address";
                    var name = "bitcoin";
                    var placeholder = "(e.g. 1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa)";
                    break;
                case 2:
                    var label = "Litecoin Address";
                    var name = "litecoin";
                    var placeholder = "(e.g. Le6X4DDUchAD5GmEmbbnektzUZCQ3JpsUC)";
                    break;
                case 3:
                    var label = "Omnicoin Address";
                    var name = "omnicoin";
                    var placeholder = "(e.g. ocVUFe8YF2bPyocLFMhNtCF5zDjSQFKJVi)";
                    break;
                case 4:
                    var label = "Payza Email";
                    var name = "payza";
                    var placeholder = "(e.g. john.doe@gmail.com)";
                    break;
            }

            $("#form").prepend("<div class='form-group'>\
                <label class='col-lg-3 control-label'>" + label + "</label>\
                <div class='col-lg-8'>\
                    <input type='text' name='" + name + "' class='form-control' placeholder='" + placeholder + "' value='" + value + "'>\
                    <div class='line line-dashed m-t-large'></div>\
                </div>\
            </div>");

            $('#add-payment-method option[value="' + type + '"]').remove();
        }
    </script>
<?php
__footer();