<!doctype html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<!--[if lt IE 9]> 
		<meta http-equiv='X-UA-Compatible' content='IE=edge'>
		<![endif]-->
		<meta name='description' content='PayIvy is an online marketplace for all types of online products. If you want to sell your virtual items now, PayIvy is your one stop.'>
		<meta name='keywords' content='payivy, virtual marketplace, sell online, online shop, online selling'>
		<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1'>
		
		<title>PayIvy - Sell online items with ease!</title>
		
		<link rel='shortcut icon' href='/favicon.ico'>
		
		<link rel='stylesheet' href='/css/bootstrap.css'>
		<link rel='stylesheet' href='/css/font-awesome.css'>
		<link rel='stylesheet' href='/css/home.css'>
		<link rel='stylesheet' href='/css/animate.css'>
		
		<script src='/js/jquery.js'></script>
		<script src='/js/bootstrap.js'></script>
		<script src='/js/home.js'></script>
		<script src='/js/jquery.smooth-scroll.js'></script>
		<script src='/js/wow.min.js'></script>
		
		<link href='https://fonts.googleapis.com/css?family=Roboto:400,400italic,500,500italic,700,700italic,900,300italic,300' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Muli:300,400' rel='stylesheet' type='text/css'>
	</head>
	<body>
		<div class='preloader'>
			<div class='status'>&nbsp;</div>
		</div>
		<header class='header fixed-image-bg' id='home'>
			<div class='color-overlay full-screen'>
				<nav class='navbar navbar-default horizontal-gradient' role='navigation'>
					<div class='container'>
						<div class='navbar-header'>
							<button type='button' class='navbar-toggle button ionic collapsed' data-toggle='collapse' data-target='.navbar-ex1-collapse'>
								<i class='fa fa-navicon'></i>
							</button>
							<a class='navbar-brand' href='/'>
								<image src='/images/logo.png' width='195' height='40'>
							</a>
						</div>
						<div class='navbar-collapse navbar-ex1-collapse collapse'>
							<ul class='nav navbar-nav navbar-right main-navigation'>
								<li><a class='' href='#getting-started'>Getting Started</a></li>
								<li><a class='' href='#features'>Features</a></li>
								<li><a class='' href='#pricing'>Pricing</a></li>
								<li><a class='' href='<?php echo $config['url']['protocol'] . 'support.' . $config['url']['domain']; ?>'>Support</a></li>
								<li><a id='header-nav-button' class='btn btn-sm' href='/seller/' onClick='document.location='/seller/''>Login</a></li>
							</ul>
						</div>
					</div>
				</nav>
				<div class='primary-row'>
					<div class='centered-display'>
						<div class='header-content'>
							<div class='centered-content'>
								<h1>Start selling online products now</h1>
								<p class='subheading'>Why waste time and money getting sites developed to sell your digital contents when you can use PayIvy?</p>
								<div class='buttons'>
									<a href='/seller/register' class='btn btn-default btn-lg standard-button'>Signup</a>	
									<a href='#getting-started' class='btn btn-lg alternate-button'>Learn More</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</header>
		<section id='getting-started' class='getting-started-section section-wrapper'>
			<div class='container'>
				<div class='section-header wow fadeIn animated' data-wow-offset='120' data-wow-duration='.5s'>
					<h2 class='dark-text'>Getting Started</h2>
					<div class='section-description'>
						Sell ebooks, musics and any digital products or services online. Sell softwares with netseal or even sell codes and serials. Accept affiliates to help boost your sales using PayIvy. All for free. 
					</div>
				</div>
				<div class='row infographic'>
					<div class='col-md-6 col-sm-6'>
						<image width='164px' height='164px' src='/images/icons/rocket.png' alt='' class='image-section image-on-left wow pulse animated' data-wow-offset='10' data-wow-duration='.5s'>				    
					</div>
					<div class='col-md-6 col-sm-6'>
						<div class='details wow fadeInRight animated' data-wow-offset='10' data-wow-duration='.5s'>
							<h3>Accept Crypto Currency</h3>
							<p>Accept Crypto Currency By using CoinPayments as the payment processor, you are able to accept money through crypto currencies as well as Paypal giving you access to all popular payment methods.</p>
							<a href='/seller/register' class='callout-link'>Sign up to get started <i class='fa fa-arrow-right'></i></a>
						</div>
					</div>
				</div>
				<div class='row infographic'>
					<div class='col-md-6 col-sm-6'>
						<image width='164px' height='164px' src='/images/icons/bag.png' alt='' class='image-section image-on-left wow pulse animated' data-wow-offset='10' data-wow-duration='.5s'>						    
					</div>
					<div class='col-md-6 col-sm-6'>
						<div class='details wow fadeInRight animated' data-wow-offset='10' data-wow-duration='.5s'>
							<h3>Sell Anything</h3>
							<p>That's right. You can sell almost anything with PayIvy be it e-books, music, videos, software, codes, keys or anything else digital. </p>
							<a href='/seller/register' class='callout-link'>Sign up to get started <i class='fa fa-arrow-right'></i></a>
						</div>
					</div>
				</div>
				<div class='row infographic'>
					<div class='col-md-6 col-sm-6'>
						<image width='164px' height='164px' src='/images/icons/laptop.png' alt='' class='image-section image-on-left wow pulse animated' data-wow-offset='10' data-wow-duration='.5s'>		
					</div>
					<div class='col-md-6 col-sm-6'>
						<div class='details wow fadeInRight animated' data-wow-offset='10' data-wow-duration='.5s'>
							<h3>Amazing Features</h3>
							<p>PayIvy offers features that are not offered on other similar services such as an affiliate system, netseal integration for selling softwares, Serials/Codes purchase support and a multiple crypto currencies payment system.</p>
							<a href='/seller/register' class='callout-link'>Sign up to get started <i class='fa fa-arrow-right'></i></a>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section id='features' class='app-brief custom-section-1 section-wrapper'>
			<div class='container'>
				<div class='row'>
					<div class='col-md-6 col-sm-6 wow fadeInRight animated' data-wow-offset='10' data-wow-duration='.5s'>
						<div class='phone-image'>
							<image src='/images/mascot.png' alt='' style='margin-top:110px;'>
						</div>
					</div>
					<div class='col-md-6 col-sm-6 left-align wow fadeInLeft animated' data-wow-offset='10' data-wow-duration='.5s'>
						<h2 class='dark-text'>Introducing PayIvy</h2>
						<p>
							The fast, easy way to sell your digital products online.
						</p>
						<ul class='feature-list colored-border'>
							<li><i class='fa fa-lock'></i>Reliable and Secure Platform</li>
							<li><i class='fa fa-check'></i>Easily add and configure your products</li>
							<li><i class='fa fa-cube'></i>Easily sell serials, file downloads, or other digital products!</li>
							<li><i class='fa fa-clock-o'></i>Start selling in minutes.</li>
						</ul>
						<a href='/seller/register' class='scroll btn btn-lg standard-button'>Sign up</a>
					</div>
				</div>
			</div>
		</section>
		<section id='pricing' class='section-wrapper'>
			<div class='container'>
				<div class='section-header wow fadeIn animated' data-wow-offset='10' data-wow-duration='.5s'>
					<h2 class='dark-text'>Simple pricing. Always free.</h2>
				</div>
				<div class='pricing-item-section-wrapper'>
					<div class='row'>
						<div class='col-md-4 col-sm-4' style='margin: 0 auto;'></div>
						<div class='col-md-4 col-sm-4' style='margin: 0 auto;'>
							<div class='pricing-item-section-basic pricing-item-section pricing-item-section-machine wow fadeInLeft animated' data-wow-offset='10' data-wow-duration='.5s'>
								<h3>Free</h3>
								<div class='pricing-item-price-note'>Absolutely free</div>
								<div class='pricing-item-price'>
									<span class='pricing-item-price-amount'>0</span><span class='pricing-item-price-unit'>&dollar;</span> <span class='pricing-item-price-frequency'>/ month</span>
								</div>
								<ul class='pricing-item-feature-items'>
									<li class='pricing-item-feature-item'>
										<i class='fa fa-check'></i>
										<p class='pricing-item-feature-title'>0% Fees</p>
										<p class='pricing-item-feature-description'>Sell your product easily and get %100 of the revenue.</p>
									</li>
									<li class='pricing-item-feature-item'>
										<i class='fa fa-check'></i>
										<p class='pricing-item-feature-title'>Multiple payment options</p>
										<p class='pricing-item-feature-description'>Accept PayPal, Bitcoin, Litecoin, and OmniCoin with ease</p>
									</li>
								</ul>
							</div>
						</div>
						<div class='col-md-4 col-sm-4' style='margin: 0 auto;'></div>
					</div>
				</div>
			</div>
		</section>
		<section class='cta-section section-wrapper' id='cta'>
			<div class='container'>
				<div class='row'>
					<div class='col-md-12'>
						<div class='section-header wow fadeIn animated' data-wow-offset='10' data-wow-duration='.5s'>
							<h2 class='dark-text'>Get started now!</h2>
							<div class='section-description'>
								Start selling your digital content online for FREE! No strings attached! Simply signup and start making money!
							</div>
							<a href='/seller/register' class='alternate-button-2'>Get started!</a>				
						</div>
					</div>
				</div>
			</div>
		</section>
		<footer>
			<div class='container'>
				<div class='contact-box wow fadeinUp animated' data-wow-offset='10' data-wow-duration='.5s'>
					<div class='col-md-4 col-sm-4 company'>
						<image alt='mobility footer logo' src='/images/logo-inv.png'>
						<p>PayIvy is an online marketplace for all types of online products. If you want to sell your virtual items now, PayIvy is your one stop.</p>
						<p class='copyright'><i class='fa fa-copyright'></i> <?php echo date('Y'); ?> PayIvy</p>
					</div>
					<div class='col-md-3 col-sm-3 col-md-push-1'>
						<ul class='link-list'>
							<li class='heading'>
								Pages
							</li>
							<li><a href='/'>Home</a></li>
							<li><a href='#pricing'>Plans and Pricing</a></li>
							<li><a href='/seller/register'>Signup</a></li>
							<li><a href='/seller/'>Login</a></li>
						</ul>
					</div>
					<div class='col-md-3 col-sm-3 col-md-push-1'>
						<ul class='link-list'>
							<li class='heading'>
								Get in touch
							</li>
							<li><a href='<?php echo $config['url']['protocol'] . 'support.' . $config['url']['domain']; ?>'>Contact Us</a></li>
						</ul>
					</div>
				</div>
			</div>
		</footer>
	</body>
</html>