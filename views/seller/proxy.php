<?php
if (isset($_GET['getdata'])) {
    $data = array();

    $products = $uas->getUser()->getProducts();

    if ($products) {
        foreach ($products as $product) {
            $orders = $product->getOrders();

            $numOrders = 0;
            $revenue = 0;

            foreach ($orders as $order) {
                $numOrders++;
                $revenue += $order->getFiat();
            }

            $data[] = array(
                'title' => '<a href=\'' . $product->getUrl() . '\'>' . $product->getTitle() . '</a>',
                'affiliate' => $product->getAffiliateEnabled() ? ('<a href=\'' . $product->getAffiliateLink() . '\'>Affiliate</a>') : '',
                'notes' => $product->getNotes(),
                'orders' => $numOrders,
                'revenue' => $revenue,
                'configure' => '<a href=\'/seller/products/edit/' . $product->getId() . '\'><i class=\'fa fa-cog\'></i></a>'
            );
        }
    }

    echo json_encode(array('aaData' => $data));
    die();
}

if (isset($_POST['ip'])) {
    $ip = preg_replace('/[^0-9.]/', '', $_POST['ip']);
    $info = file_get_contents('http://iphub.info/api.php?ip=' . $ip . '&showtype=4');
    if ($info != 'Error: Not a valid IP Address') {
        $info = json_decode($info);

        $uas->addMessage(new ErrorSuccessMessage('IP: ' . $ip . '<br />Hostname: ' . $info->hostname . '<br />Country: ' . $info->countryName . '<br />Region: ' . $info->region . '<br />ASN: ' . $info->asn . '<br />Proxy: ' . ($info->proxy == '1' ? 'Yes' : 'No'), false));
    } else {
        $uas->addMessage(new ErrorSuccessMessage('Invalid IP address'));
    }
}

include_once('header.php');
?>
    <section class="wrapper">
        <div class='clearfix'>
            <?php $uas->printMessages(); ?>
        </div>
        <div class='row'>
            <div class='col-sm-6'>
                <section class='panel'>
                    <div class='panel-body'>
                        <form class='form-horizontal' method='post'>
                            <div class='form-group'>
                                <label class='col-lg-3 control-label'>IP</label>
                                <div class='col-lg-8'>
                                    <input name='ip' class='form-control' required='' type='text'>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-lg-9 col-lg-offset-3'>
                                    <button type='submit' class='btn btn-primary'>Lookup</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </section>
<?php
include_once('footer.php');