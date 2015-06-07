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
                'configure' => '<a href=\'/seller/products/edit/' . $product->getId() . '\'><i class=\'fa fa-cog\'></i></a> <a href=\'#\' onclick="deleteProduct(' . $product->getId() . ');"><i class=\'fa fa-trash-o\'></i></a>'
            );
        }
	}
	
	echo json_encode(array('aaData' => $data));
	die();
}

if(isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $products = $uas->getUser()->getProducts();

    foreach($products as $p){
        $product = new Product();

        if($product->read($_GET['delete'])){
            $product->setDeleted(1);
            $product->update();
            break;
        }
    }
    die();
}

__header('Products');
?>
<script>
    function deleteProduct(id){
        $.get('/seller/products/view',  {delete : id});
        loadData(false);
    }
</script>

    <div class="wrapper">
        <div class='clearfix'>
            <?php $uas->printMessages(); ?>
            <div class='alert alert-success alert-dismissable hidden' id="delete-alert">Successfully removed your product!<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
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

        function loadData(n){
            if(!n){
                $('[data-ride=\'products\']').dataTable().fnDestroy();
                $('#delete-alert').removeClass("hidden");
            }

            var table = $('[data-ride=\'products\']').dataTable( {
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
        }
        loadData(true);
	</script>
<?php
__footer();