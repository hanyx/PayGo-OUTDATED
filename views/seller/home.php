<?php
include_once('header.php');

$products = $uas->getUser()->getProducts();

$allProducts = $uas->getUser()->getProducts(true);

$revenueChart = array();
$revenueToday = 0;
$revenueTotal = 0;

$ordersChart = array();
$ordersToday = 0;
$ordersTotal = 0;

$affiliates = 0;

for ($x = 0; $x <= 7; $x++) {
	$date = strtotime(date("Y-m-d")) - (86400 * $x);
	$revenueChart[] = array(($date - 21600) * 1000, 0);
	$ordersChart[] = array(($date - 21600) * 1000, 0);
}

if ($allProducts !== false) {
	foreach ($allProducts as $product) {
		$affiliates += count($product->getAffiliates());
		
		$orders = $product->getOrders();
		foreach ($orders as $order) {
			$ordersTotal++;
			$revenueTotal += $order->getFiat();
			
			if (strtotime($order->getDate()) > time() - 86400) {
				$ordersToday++;
				$revenueToday += $order->getFiat();
			}
			
			$date = strtotime(date("Y-m-d", strtotime($order->getDate())));
			if ($date > strtotime(date("Y-m-d")) - (86400 * 7)) {
				$offset = (strtotime(date("Y-m-d")) - $date) / 86400;
				$revenueChart[$offset][1] += $order->getFiat();
				
				$ordersChart[$offset][1]++;
			}
		}
	}
}

$chartStart = date("Y", strtotime(date("Y-m-d")) - 518400) . ", " . (date("m", strtotime(date("Y-m-d")) - 518400) - 1) . ", " . date("d", strtotime(date("Y-m-d")) - 518400);
$chartEnd = date("Y", strtotime(date("Y-m-d"))) . ", " . (date("m", strtotime(date("Y-m-d"))) - 1) . ", " . date("d", strtotime(date("Y-m-d")));

$inbox = $uas->getUser()->getMessages();

$inboxUnread = 0;

foreach ($inbox as $message) {
	if (!$message->getRead()) {
		$inboxUnread ++;
	}
}

$productDeliveryUnread = 0;

$productDelivery = $uas->getUser()->getMessages(MessageFolder::PRODUCTDELIVERY);

foreach ($productDelivery as $message) {
	if (!$message->getRead()) {
		$productDeliveryUnread ++;
	}
}
?>
	<section id="content">
		<section class="main padder">
			<div class="row">
				<div class="col-lg-12">
					<section class="toolbar clearfix m-t-large m-b">
						<a href="/seller/messages/inbox" class="btn btn-white btn-circle"><i class="fa fa-envelope-o"></i>Inbox</a>
						<a href="/seller/products/view" class="btn btn-primary btn-circle active"><i class="fa fa-list-alt"></i>Products</a>
						<a href="CHANGEME" class="btn btn-success btn-circle"><i class="fa fa-check"></i>Orders</a>
						<a href="/seller/messages/product-delivery" class="btn btn-info btn-circle active"><i class="fa fa-clock-o"></i>Deliveries</b></a>
						<a href="/seller/affiliates" class="btn btn-warning btn-circle"><i class="fa fa-user"></i>Affiliates</a>
						<a href="/seller/products/create" class="btn btn-danger btn-circle"><i class="fa fa-plus"></i>Create Product</a>
					</section>
				</div>
				<div class="col-lg-6">
					<div class="row">
						<div class="col-xs-6">
							<section class="panel">
								<header class="panel-heading bg-white">
									<div class="text-center h5">Revenue Today: <strong>$<?php echo number_format($revenueToday, 2); ?></strong></div>
								</header>
								<div class="panel-body pull-in text-center">
									<div class="inline">
										<div class="easypiechart" data-percent="100" data-bar-color="#ff5f5f">
											<span class="h2" style="margin-left:10px;margin-top:10px;display:inline-block"><?php echo number_format($ordersToday); ?></span>
											<div class="easypie-text text-muted"><?php echo formatS($ordersToday, "order"); ?> today</div>
										</div>
									</div>
								</div>
							</section>
						</div>
						<div class="col-xs-6">
							<section class="panel">
								<header class="panel-heading bg-white">
									<div class="text-center h5">Total Revenue: <strong>$<?php echo number_format($revenueTotal, 2); ?></strong></div>
								</header>
								<div class="panel-body pull-in text-center">
									<div class="inline">
										<div class="easypiechart" data-percent="100" data-bar-color="#13c4a5">
											<span class="h2" style="margin-left:10px;margin-top:10px;display:inline-block"><?php echo number_format($ordersTotal); ?></span>
											<div class="easypie-text text-muted">total <?php echo formatS($ordersTotal, "order"); ?></div>
										</div>
									</div>
								</div>
							</section>
						</div>
					</div>
					<section class="panel">
						<div class="panel-body text-muted l-h-2x">
							<span class="badge"><?php echo number_format($ordersTotal); ?></span>
							<span class="m-r-small"><?php echo formatS($ordersTotal, "Order"); ?></span>
							<span class="badge"><?php echo number_format(count($products)); ?></span>
							<span class="m-r-small"><?php echo formatS(count($products), "Product"); ?></span>
							<span class="badge"><?php echo number_format($affiliates); ?></span>
							<span class="m-r-small"><?php echo formatS($affiliates, "Affiliate"); ?></span>
						</div>
					</section>
				</div>
				<div class="col-lg-6">
					<section class="panel">
						<header class="panel-heading">
							<span>Products</span>
						</header>
						<ul class="list-group">
							<?php
							foreach ($products as $product) {
								$orders = $product->getOrders();
								
								$revenue = 0;
								
								foreach ($orders as $order) {
									$revenue += $order->getFiat();
								}
								?>
								<li class="list-group-item">
									<div class="media">
										<div class="pull-left media-large">
											<div class="h4 m-t-mini"><strong><?php echo $product->getTitle(); ?></strong></div>
										</div>
										<div class="pull-right hidden-sm text-right m-t-mini">
											<b class="badge bg-success" data-toggle="tooltip" data-title="Total Orders"><?php echo number_format(count($orders)) . " " .  formatS(count($orders), "Order"); ?></b>
											<b class="badge bg-info" data-toggle="tooltip" data-title="Total Revenue">$<?php echo number_format($revenue, 2); ?></b>
										</div>
									</div>
								</li>
							<?php
							}
							?>
						</ul>
					</section>
				</div>				
			</div>
			<div class="row">
				<div class="col-md-6">
					<section class="panel">
						<header class="panel-heading">Orders this Week</header>
						<div class="panel-body">
							<div id="orders-chart" style="height:240px"></div>
						</div>
					</section>
				</div>
				<div class="col-md-6">
					<section class="panel">
						<header class="panel-heading">Revenue this Week</header>
						<div class="panel-body">
							<div id="revenue-chart" style="height:240px"></div>
						</div>
					</section>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-6">
					<section class="panel">
						<header class="panel-heading">
							<ul class="nav nav-pills pull-right">
								<li>
									<a href="#" class="panel-toggle text-muted <?php echo $inboxUnread == 0 ? "active" : ""; ?>"><i class="fa fa-caret-down fa-lg text-active"></i><i class="fa fa-caret-up fa-lg text"></i></a>
								</li>
							</ul>
							<?php
							if ($inboxUnread != 0) {
							?>
								<span class="label label-large bg-default"><?php echo $inboxUnread; ?></span>
							<?php
							}
							?> Inbox
						</header>
						<section style="height:210px" class="panel-body scrollbar scroll-y m-b <?php echo $inboxUnread == 0 ? "collapse" : ""; ?>">
							<?php
							if ($inbox) {
								foreach ($inbox as $message) {
								?>
									<article class="media">
										<span class="pull-left thumb-small"><i class="fa fa-user fa fa-2x text-muted"></i></span>		
										<div class="media-body">
											<div class="pull-right media-mini text-center text-muted">
												<?php
												if (date("Y-m-d", strtotime($message->getDate())) == date("Y-m-d")) {
												?>
													<strong class="h4"><?php echo date("h:i", strtotime($message->getDate())); ?></strong><br>
													<small class="label bg-light"><?php echo date("a", strtotime($message->getDate())); ?></small>
												<?php
												} else {
												?>
													<strong class="h4"><?php echo date("d", strtotime($message->getDate())); ?></strong><br>
													<small class="label bg-light"><?php echo date("M", strtotime($message->getDate())); ?></small>
												<?php
												}
												?>
											</div>
											<a href="/seller/messages/inbox/view/<?php echo $message->getId(); ?>" class="h4"><?php echo ($message->getRead() ? "" : "<b>") . $message->getSender() . ($message->getRead() ? "" : "</b>"); ?></a>
											<small class="block"><?php echo $message->getExcerpt(); ?></small>
										</div>
									</article>
									<div class="line pull-in"></div>
								<?php
								}
							}
							?>
						</section>
					</section>
				</div>
				<div class="col-lg-6">
					<section class="panel">
						<header class="panel-heading">
							<ul class="nav nav-pills pull-right">
								<li>
									<a href="#" class="panel-toggle text-muted <?php echo $productDeliveryUnread == 0 ? "active" : ""; ?>"><i class="fa fa-caret-down fa-lg text-active"></i><i class="fa fa-caret-up fa-lg text"></i></a>
								</li>
							</ul>
							<?php
							if ($productDeliveryUnread != 0) {
							?>
								<span class="label label-large bg-default"><?php echo $productDeliveryUnread; ?></span>
							<?php
							}
							?> Product Delivery
						</header>
						<section style="height:210px" class="panel-body scrollbar scroll-y m-b <?php echo $productDeliveryUnread == 0 ? "collapse" : ""; ?>">
							<?php
							if ($productDelivery) {
								foreach ($productDelivery as $message) {
								?>
									<article class="media">
										<span class="pull-left thumb-small"><i class="fa fa-user fa fa-2x text-muted"></i></span>		
										<div class="media-body">
											<div class="pull-right media-mini text-center text-muted">
												<?php
												if (date("Y-m-d", strtotime($message->getDate())) == date("Y-m-d")) {
												?>
													<strong class="h4"><?php echo date("h:i", strtotime($message->getDate())); ?></strong><br>
													<small class="label bg-light"><?php echo date("a", strtotime($message->getDate())); ?></small>
												<?php
												} else {
												?>
													<strong class="h4"><?php echo date("d", strtotime($message->getDate())); ?></strong><br>
													<small class="label bg-light"><?php echo date("M", strtotime($message->getDate())); ?></small>
												<?php
												}
												?>
											</div>
											<a href="/seller/messages/product-delivery/view/<?php echo $message->getId(); ?>" class="h4"><?php echo ($message->getRead() ? "" : "<b>") . $message->getSender() . ($message->getRead() ? "" : "</b>"); ?></a>
											<small class="block"><?php echo $message->getExcerpt(); ?></small>
										</div>
									</article>
									<div class="line pull-in"></div>
								<?php
								}
							}
							?>
						</section>
					</section>
				</div>
			</div>
		</section>
	</section>
	<script>
		$.plot($("#orders-chart"), [{
				data: <?php echo json_encode($ordersChart); ?>,
				label: "Orders"
			}], 
			{
				series: {
					lines: {
						show: true,
						lineWidth: 1,
						fill: true,
						fillColor: {
							colors: [{
								opacity: 0.2
							}, {
								opacity: 0.1
							}]
						}
					},
					points: {
						show: true
					},
					shadowSize: 2
				},
				grid: {
					hoverable: true,
					clickable: true,
					tickColor: "#f0f0f0",
					borderWidth: 0
				},
				colors: ["#5191d1"],
				xaxis: {
					mode: "time",
					minTickSize: [1, "day"],
					min: Date.UTC(<?php echo $chartStart; ?>),
					max: Date.UTC(<?php echo $chartEnd; ?>)
				},
				yaxis: {
					ticks: 10,
					tickDecimals: 0
				},
				tooltip: true,
				tooltipOpts: {
					content: "%y.4 %s on %x.1",
					defaultTheme: false,
					shifts: {
						x: 0,
						y: 20
					}
				}
			}
		);
		$.plot($("#revenue-chart"), [{
				data: <?php echo json_encode($revenueChart); ?>,
				label: "Revenue"
			}], 
			{
				series: {
					lines: {
						show: true,
						lineWidth: 1,
						fill: true,
						fillColor: {
							colors: [{
								opacity: 0.2
							}, {
								opacity: 0.1
							}]
						}
					},
					points: {
						show: true
					},
					shadowSize: 2
				},
				grid: {
					hoverable: true,
					clickable: true,
					tickColor: "#f0f0f0",
					borderWidth: 0
				},
				colors: ["#5191d1"],
				xaxis: {
					mode: "time",
					minTickSize: [1, "day"],
					min: Date.UTC(<?php echo $chartStart; ?>),
					max: Date.UTC(<?php echo $chartEnd; ?>)
				},
				yaxis: {
					ticks: 10,
					tickDecimals: 0
				},
				tooltip: true,
				tooltipOpts: {
					content: "$%y.4 %s on %x.1",
					defaultTheme: false,
					shifts: {
						x: 0,
						y: 20
					}
				}
			}
		);
	</script>
<?php
include_once('footer.php');