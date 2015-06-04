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

include_once('header.php');
?>
    <div class="wrapper">
        <div class='clearfix'>
            <?php $uas->printMessages(); ?>
        </div>
        <section class='panel'>
            <table class='table table-striped m-b-none' data-ride='products'>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Affiliate Link</th>
                        <th>Notes</th>
                        <th>Orders</th>
                        <th>Revenue</th>
                        <th>Configure</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </section>
    </div>
    <script>
		$('[data-ride=\'products\']').dataTable( {
			'bProcessing': true,
			'sAjaxSource': '/seller/products/view?getdata=true',
			'sDom': '<\'row\'<\'col-sm-6\'l><\'col-sm-6\'f>r>t<\'row\'<\'col-sm-6\'i><\'col col-sm-6\'p>>',
			'sPaginationType': 'full_numbers',
			'aoColumns': [
				{ 'mData': 'title' },
				{ 'mData': 'affiliate' },
				{ 'mData': 'notes' },
				{ 'mData': 'orders' },
				{ 'mData': 'revenue' },
				{ 'mData': 'configure' }
			]
		} );
	</script>
<?php
include_once('footer.php');