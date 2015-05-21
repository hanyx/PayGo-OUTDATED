<?php
if (isset($_GET['getdata'])) {
    $data = array();

    $orders = Order::getOrdersByUser($uas->getUser()->getId());

    foreach ($orders as $order) {
        $product = new Product();
        $product->read($order->getProductId());

        $data[] = array(
            'date' => $order->getDate(),
            'email' => $order->getEmail(),
            'ip' => $order->getIp(),
            'txid' => $order->getTxid(),
            'currency' => ($order->getCurrency() == ProductCurrency::PAYPAL ? 'PayPal' : ($order->getCurrency() == ProductCurrency::PAYPALSUB ? 'PayPal Subscription' : ($order->getCurrency() == ProductCurrency::BITCOIN ? 'Bitcoin' : ($order->getCurrency() == ProductCurrency::LITECOIN ? 'Litecoin' : ($order->getCurrency() == ProductCurrency::OMNICOIN ? 'Omnicoin' : ''))))),
            'fiat' => $order->getFiat(),
            'native' => $order->getNative(),
            'product' => '<a href=\'' . $product->getUrl() . '\'>' . $product->getTitle() . '</a>'
        );
    }

    echo json_encode(array('aaData' => $data));
    die();
}

include_once('header.php');
?>
    <section id='content'>
        <section class='main padder'>
            <div class='clearfix'>
                <h4><i class='fa fa-eye'></i> orders</h4>
                <?php $uas->printMessages(); ?>
            </div>
            <div class='row'>
                <div class='col-lg-12'>
                    <section class='panel'>
                        <div class='table-responsive' style='overflow-x: scroll;'>
                            <table class='table table-striped m-b-none' data-ride='orders'>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Buyer Email</th>
                                        <th>Buyer IP</th>
                                        <th>Transaction ID</th>
                                        <th>Currency</th>
                                        <th>Fiat</th>
                                        <th>Native</th>
                                        <th>Product</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </section>
    <script>
        $('[data-ride=\'orders\']').dataTable( {
            'bProcessing': true,
            'sAjaxSource': '/seller/products/orders?getdata=true',
            'sDom': '<\'row\'<\'col-sm-6\'l><\'col-sm-6\'f>r>t<\'row\'<\'col-sm-6\'i><\'col col-sm-6\'p>>',
            'sPaginationType': 'full_numbers',
            'aoColumns': [
                { 'mData': 'date' },
                { 'mData': 'email' },
                { 'mData': 'ip' },
                { 'mData': 'txid' },
                { 'mData': 'currency' },
                { 'mData': 'fiat' },
                { 'mData': 'native' },
                { 'mData': 'product' }
            ]
        } );
    </script>
<?php
include_once('footer.php');