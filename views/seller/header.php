<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='utf-8'>
		<title>PayIvy</title>
		<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1'>
		
		<link rel='stylesheet' href='/css/bootstrap.css'>
		<link rel='stylesheet' href='/css/font-awesome.css'>
		<link rel='stylesheet' href='/css/seller.css'>
		<link rel='stylesheet' href='/css/dropzone.css'>
		<link rel='stylesheet' href='/css/select2.css'>
		
		<link rel='shortcut icon' href='/favicon.ico'>
		
		<script src='https://www.google.com/recaptcha/api.js'></script>
		<script src='/js/jquery.js'></script>
		<script src='/js/bootstrap.js'></script>
		<script src='/js/jquery.easy-pie-chart.js'></script>
		<script src='/js/seller.js'></script>
		<script src='/js/jquery.flot.min.js'></script>
		<script src='/js/jquery.flot.tooltip.min.js'></script>
		<script src='/js/jquery.flot.resize.js'></script>
		<script src='/js/jquery.flot.time.js'></script>
		<script src='/js/jquery.dataTables.min.js'></script>
		<script src='/js/wysihtml5.min.js'></script>
        <script src='/js/fuelex.js'></script>
        <script src='/js/select2.min.js'></script>
        <script src='/js/dropzone.min.js'></script>
		<!--
		<script src='js/app.js'></script>
		<script src='js/app.plugin.js'></script>
		<script src='js/app.data.js'></script>
		<script src='js/charts/easypiechart/jquery.easy-pie-chart.js'></script>
		<script src='js/charts/flot/jquery.flot.min.js'></script>
		<script src='js/charts/flot/jquery.flot.tooltip.min.js'></script>
		<script src='js/charts/flot/jquery.flot.resize.js'></script>
		<script src='js/charts/flot/jquery.flot.time.js'></script>
		<script src='js/select2/select2.min.js'></script>
		<script src='js/bootstrap3-wysihtml5.all.min.js'></script>
		<script src='js/file-input/bootstrap.file-input.js'></script>
		<script src='js/fuelux/fuelux.js'></script>
		<script src='js/dropzone/dropzone.min.js'></script>
		<script src='js/datatables/jquery.dataTables.min.js'></script>
		-->
	</head>
	<body class='fuelux <?php echo (!$uas->isAuthenticated() || $pageManager->getCurrentPage()->noAuth()) ? 'navbar-fixed' : ''; ?>'>
		<header id='header' class='navbar'>
			<?php if ($uas->isAuthenticated() && !$pageManager->getCurrentPage()->noAuth()) { ?>
				<ul class='nav navbar-nav navbar-avatar pull-right'>
					<li class='dropdown'>
						<a href='#' class='dropdown-toggle' data-toggle='dropdown'>						
							<span class='hidden-xs-only'><?php echo $uas->getUser()->getUsername(); ?></span>
							<span class='thumb-small avatar inline'><img src='/images/avatar.png' class='img-circle'></span>
							<b class='caret hidden-xs-only'></b>
						</a>
						<ul class='dropdown-menu pull-right'>
							<li><a href='/seller/settings'>Settings</a></li>
							<li><a href='/seller/logout'>Logout</a></li>
						</ul>
					</li>
				</ul>
			<?php } ?>
			<a class='navbar-brand' href='/seller/' style='padding: 0;'>
				<img src='/images/logo-alt.jpg' alt='Payivy' style='padding: 12px 32px'/>
			</a>
			<button type='button' class='btn btn-link pull-left nav-toggle visible-xs' data-toggle='class:slide-nav slide-nav-left' data-target='body'>
				<i class='fa fa-bars fa-lg text-default'></i>
			</button>
		</header>
		<?php if ($uas->isAuthenticated() && !$pageManager->getCurrentPage()->noAuth()) { ?>
			<nav id='nav' class='nav-primary hidden-xs bg-light'>
				<ul class='nav affix-top' data-offset-top='50' data-spy='affix'>
					<?php
					foreach ($pageManager->getCategories() as $category) {
						if (!$category->isHidden() && $category->checkAuth($url, $uas)) {
							$pages = $category->getPages();
							$alerts = 0;
							$link = '#';
							
							foreach ($pages as $page) {
								$alerts += $page->getAlerts($uas->getUser());
								if ($link == '#') {
									$link = $page->getLink();
								}
							}
							
							if (count($pages) > 1) {
								echo '<li class=\'dropdown-submenu ' . ($category->isCurrent() ? 'active' : '') . '\'>';
									echo '<a href=\'' . $link . '\'>' . (($alerts > 0) ? ('<b class=\'badge bg-danger pull-right\'>' . $alerts . '</b>') : '') . '<i class=\'fa ' . $category->getIcon() . ' fa-lg\'></i><span>' . $category->getName() . '</span></a>';
									echo '<ul class=\'dropdown-menu\'>';
									
									foreach ($pages as $page) {
										echo '<li ' . ($page->isCurrentPage() ? 'class=\'active\'' : '') . ' ><a href=\'' . $page->getLink() . '\'>' . $page->getName() . '</a></li>';
									}
									
									echo '</ul>';
								echo '</li>';
							} else {
								echo '<li class=\'' . ($category->isCurrent() ? 'active' : '') . '\'>';
									echo '<a href=\'' . $link . '\'>' . (($alerts > 0) ? ('<b class=\'badge bg-danger pull-right\'>' . $alerts . '</b>') : '') . '<i class=\'fa ' . $category->getIcon() . ' fa-lg\'></i><span>' . $category->getName() . '</span></a>';
								echo '</li>';
							}
						}
					}
					?>
				</ul>
			</nav>
		<?php } ?>