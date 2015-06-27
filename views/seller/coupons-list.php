<?php
if (isset($_GET['getdata'])) {
	$data = array();
	
	$coupons = $uas->getUser()->getCoupons();

	if ($coupons) {
		foreach ($coupons as $coupon) {
			$orders = $coupon->getOrders();

            $numOrders = 0;
            $revenue = 0;

            foreach ($orders as $order) {
                $numOrders++;
                $revenue += $order->getFiat();
            }

            $data[] = array(
                'name' => $coupon->getName(),
                'reduction' => $coupon->getReduction() . ' %',
                'used' => $coupon->getUsedAmount(),
                'maximum' => $coupon->getMaxUsedAmount(),
                'configure' => '<a href=\'/seller/coupons/edit/' . $coupon->getId() . '\'><i class=\'fa fa-cog\'></i></a>'
            );
        }
	}
	
	echo json_encode(array('aaData' => $data));
	die();
}

__header()
?>
    <div class="wrapper">
        <div class='clearfix'>
            <?php $uas->printMessages(); ?>
        </div>
        <section class='panel'>
            <table class="table pi-table" data-ride='products'>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Reduction</th>
                        <th>Times Used</th>
                        <th>Usage Limit</th>
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
			'sAjaxSource': '/seller/coupons/view?getdata=true',
			'sDom': '<\'row\'<\'col-sm-6\'l><\'col-sm-6\'f>r>t<\'row\'<\'col-sm-6\'i><\'col col-sm-6\'p>>',
			'sPaginationType': 'full_numbers',
			'aoColumns': [
				{ 'mData': 'name' },
				{ 'mData': 'reduction' },
				{ 'mData': 'used' },
				{ 'mData': 'maximum' },
				{ 'mData': 'configure' }
			]
		} );
	</script>
<?php
__footer();