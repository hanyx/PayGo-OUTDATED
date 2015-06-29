<?php
if (isset($_GET['getdata'])) {
	$data = array();
	
	$affiliates = $uas->getUser()->getAffiliates();

	if ($affiliates) {
		foreach ($affiliates as $affiliate) {
			$product = $affiliate->getProduct(true);

			if ($product) {
				$data[] = array(
					'email' => $affiliate->getEmail(),
					'product' => ($product->isDeleted() ? '' : ('<a target=\'_blank\' href=\'' . $product->getUrl() . '\'>' . $product->getTitle() . '</a>')),
					'orders' => $affiliate->getOrders(),
					'unpaidOrders' => $affiliate->getUnpaidOrders(),
					'unpaidAmount' => '$' . $affiliate->getUnpaidFiat(),
					'pay' => '<a href=\'/seller/affiliates/pay/' . $affiliate->getId() . '\'><i class=\'fa fa-check-circle\'></i></a>'
				);
			}
		}
	}
	
	echo json_encode(array('aaData' => $data));
	die();
}

if (count($url) == 4 && $url[2] == 'pay') {
	$affiliate = new Affiliate();

	if ($affiliate->read($url[3])) {
		$product = $affiliate->getProduct(true);
		
		if ($product && $uas->getUser()->getId() == $product->getSellerId()) {
			if ($affiliate->getUnpaidOrders() == 0 || $affiliate->getUnpaidFiat() == 0) {
				$uas->addMessage(new ErrorSuccessMessage('There are no unpaid orders for that affiliate!'));
			} else {
                $affiliate->pay();

				$uas->addMessage(new ErrorSuccessMessage('Affiliate marked as paid.', false));
			}
		}
	}
}

__header();
?>
    <div class='row'>
        <div class='container-fluid'>
            <?php $uas->printMessages(); ?>
            <table class="table pi-table" data-ride='affiliates'>
                <thead>
                <tr>
                    <th>Email</th>
                    <th>Product</th>
                    <th>Total Orders</th>
                    <th>Unpaid Orders</th>
                    <th>Unpaid Amount</th>
                    <th>Mark as Paid</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
	<script>
		$('[data-ride=\'affiliates\']').dataTable( {
			'bProcessing': true,
			'sAjaxSource': '/seller/affiliates?getdata=true',
			'sDom': '<\'row\'<\'col-sm-6\'l><\'col-sm-6\'f>r>t<\'row\'<\'col-sm-6\'i><\'col col-sm-6\'p>>',
			'sPaginationType': 'full_numbers',
			'aoColumns': [
				{ 'mData': 'email' },
				{ 'mData': 'product' },
				{ 'mData': 'orders' },
				{ 'mData': 'unpaidOrders' },
				{ 'mData': 'unpaidAmount' },
				{ 'mData': 'pay' }
			]
		} );
	</script>
<?php
include_once('footer.php');