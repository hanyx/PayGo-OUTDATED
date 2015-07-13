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
                'configure' => '<a href=\'/seller/products/edit/' . $product->getId() . '\'><i class=\'fa fa-cog\'></i></a>',
                'delete' => '<a href="#" onClick="doModal(' . $product->getId() . ', \'' . htmlspecialchars($product->getTitle(), ENT_QUOTES) . '\');"><i class=\'fa fa-trash-o\'></i></a>'
            );
        }
	}
	
	echo json_encode(array('aaData' => $data));
	die();
}

if (count($url) == 4 && $url['3'] == 'delete' && isset($_POST['deleteId'])) {
    try {
        NoCSRF::check('products_del_token', $_POST, true, 60 * 10, false);
        $product = new Product();

        if ($product->read($_POST['deleteId']) && $product->getSellerId() == $uas->getUser()->getId()) {
            $product->setDeleted();
            $product->update();

            $uas->addMessage(new ErrorSuccessMessage('Product Deleted', false));
        }
    } catch(Exception $e) {}
}

__header('Products');
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
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </section>
    </div>
    <script>
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
                { 'mData': 'configure' },
                { 'mData': 'delete' }
            ]
        } );

        function doModal(id, name) {
            $('#delete-modal .modal-title').html('Are you sure you want to delete ' + name + '?<br><br>');
            $('#delete-modal #deleteId').val(id);

            $('#delete-modal').modal();
        }
	</script>
    <div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="/seller/products/view/delete">
                    <input type="hidden" name="products_del_token" value="<?php echo NoCSRF::generate('products_del_token'); ?>"/>
                    <input type="hidden" name="deleteId" id="deleteId" />
                    <div class="modal-header">
                        <h4 class="modal-title"></h4>
                    </div>
                    <div class="modal-footer">
                        <a href="" type="button" class="btn btn-primary">Yes</a>
                        <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
__footer();