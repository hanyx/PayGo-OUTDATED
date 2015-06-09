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
            'fiat' => '$' . $order->getFiat(),
            'product' => '<a href=\'' . $product->getUrl() . '\'>' . $product->getTitle() . '</a>'
        );
    }

    echo json_encode(array('aaData' => $data));
    die();
}

__header('Orders');
?>
    <div class="wrapper">
        <div class='clearfix'>
            <?php $uas->printMessages(); ?>
        </div>
        <section class='panel'>
            <table class='table table-striped m-b-none' data-ride='orders'>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Buyer Email</th>
                        <th>Buyer IP</th>
                        <th>Transaction ID</th>
                        <th>Currency</th>
                        <th>Amount</th>
                        <th>Product</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </section>
    </div>
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
                { 'mData': 'product' }
            ]
        } );
    </script>
<?php
__footer();