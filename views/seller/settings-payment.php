<?php
__header();

if (isset($_POST['update-password']) && isset($_POST['password-old']) && isset($_POST['password']) && isset($_POST['password-confirm'])) {
    $uas->processUpdatePassword($_POST['password-old'], $_POST['password'], $_POST['password-confirm']);
}

if (isset($_POST['update-payment-details'])) {
    $uas->processUpdatePaymentDetails(isset($_POST['paypal']) ? $_POST['paypal'] : '', isset($_POST['bitcoin']) ? $_POST['bitcoin'] : '', isset($_POST['litecoin']) ? $_POST['litecoin'] : '', isset($_POST['omnicoin']) ? $_POST['omnicoin'] : '');
}
?>
    <?php
    $uas->printMessages();
    if (isset($tfr)) {
        $tfr->printMessages();
    }
    ?>

    <form method="post">
        <div class="row">
            <div class="col-md-12 ">
                <div class="add-dropdown">
                    <select class="selectize" id='add-payment-method'>
                        <option></option>
                        <option value='0'>PayPal</option>
                        <option value='1'>Bitcoin</option>
                        <option value='2'>Litecoin</option>
                        <option value='3'>Omnicoin</option>
                    </select>
                </div>
                <table class="table pi-table bigger-table">
                    <tbody id="inputs">
                    </tbody>
                </table>
                <div class="form-save">
                    <button type="submit" name='update-payment-details' class="btn btn-success btn-save">Save settings</button>
                </div>
            </div>
        </div>
    </form>

    <script>
        $(function() {
            <?php echo $uas->getUser()->getPayPal() != '' ? "printInput(0, '" . $uas->getUser()->getPayPal() . "');" : ""; ?>
            <?php echo $uas->getUser()->getBitcoin() != '' ? "printInput(1, '" . $uas->getUser()->getBitcoin() . "');" : ""; ?>
            <?php echo $uas->getUser()->getLitecoin() != '' ? "printInput(2, '" . $uas->getUser()->getLitecoin() . "');" : ""; ?>
            <?php echo $uas->getUser()->getOmnicoin() != '' ? "printInput(3, '" . $uas->getUser()->getOmnicoin() . "');" : ""; ?>

            $("#add-payment-method").change(function() {
                if ($(this).val() != '') {
                    printInput(parseInt($(this).val()), '');
                }
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

            $('#inputs').prepend('<tr><td class="payment-processor-enable">\
            <input type="text" name="' + name + '" class="form-control payment-processor-input payment-processor-visible" placeholder="' + placeholder + '" value="' + value + '">\
            <span class="payment-processor-lbl">' + name + '</span>\
            </td></tr>');

            $('#add-payment-method')[0].selectize.removeOption(type);
        }
    </script>
<?php
__footer();