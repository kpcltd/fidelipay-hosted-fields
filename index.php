<?php
/*
 * This example code demonstrates the use of hosted fields in a simple
 * payment page. It uses the Gateway PHP SDK for the direct requests.
 * It also used the foundation framework to make a nice page.
 */

require_once('gateway.php');

use SDK\Gateway;

// Create an instance of the Gateway class. Make sure you have edited this
// class to add your gateway URLs, proxy URLs and test Merchant account.
$gateway = new Gateway();
$message = '';

// To prevent double submissions of the payment form, attach a unique transaction
// identifier to the user's session, and only remove it from the user's session
// when a transaction request to FideliPay is completed.
if (isset($_SESSION['fidelipay_transaction_unique'])) {
  $transaction_unique = $_SESSION['fidelipay_transaction_unique'];
} else {
  $transaction_unique = uniqid();
  $_SESSION['fidelipay_transaction_unique'] = $transaction_unique;
}

// If the form is submitted then send the required transaction to the Gateway.
if (isset($_POST['paymentToken'])) {

	// Build the Gateway request - refer to integration guide for details.
	$req = array(
		'action'			=> 'SALE',						// perform a SALE
		'type'				=> 1,							// e-commerce transaction
		'amount'			=> $_POST['amount'],			// for 10.00
		'currencyCode'		=> 826,							// in pounds sterling (GBP)
		'countryCode'		=> 826,							// from the United Kingdom (GB)
		'paymentToken'		=> $_POST['paymentToken'],		// using payment details supplied in the Hosted Fields
		'transactionUnique'	=> $transaction_unique,					// just to stop duplicate transaction flag. 
		'customerName'		=> $_POST['customerName'],		// where payment details belong to this customer
	);

  $res = $gateway->directRequest($req);					// Get response back using directRequest method from SDK.
  
  // Now allow the user to undertake further transactions.
  unset($_SESSION['fidelipay_transaction_unique']);

	if (isset($res['responseStatus']) && $res['responseStatus'] === '0') {
		$message =  '<h3 style="color: green;">TRANSACTION SUCCESS ' . htmlentities($res['responseMessage']) . '</h3>';
	} else {
		$message =  '<h3 style="color: red;">TRANSACTION ERROR ' . htmlentities($res['responseMessage']) . '</h3>';
	}
}

// The URL to the Gateway's Web SDK
$websdk_url = dirname($gateway::$hostedUrl) . '/sdk/web/v1/';

?>
<!doctype html>
<html class="no-js" lang="en" dir="ltr">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Hosted Fields</title>
		<link rel="stylesheet" href="css/foundation.css">
		<link rel="stylesheet" href="css/app.css">
		<link rel="icon" href="fav.png" sizes="192x192" />
		<link href="fonts/css.css" rel="stylesheet">
		<link rel="stylesheet" href="fonts/css/font-awesome.min.css">
		<style>
			.tooltip {
				max-width: rem-calc(1000px) !important;
				width: rem-calc(1000px);
			}
		</style>
		<style class="hostedfield">
			/* Custom style sheet for hosted fields class */
			.hostedfield {
				color: black;
				text-align: left;
				border: solid !important;
				padding: 10px !important;
			}
		</style>
	</head>
	<body style="font-family: \'Inconsolata\', monospace;">
		<div class="grid-container">
			<div class="grid-x">
				<div class="cell"> <!-- full length cell -->
				<div class="reveal" id="payment" data-reveal>
					<?php if ($message  != '') { ?>
						<?=$message?><br>
						<a href="index.php" class="button expanded">Back</a>
					<?php } else { ?>
						<h5>Test hosted fields payment form.</h5>
						<p style="font-family: \'Inconsolata\', monospace;">
							<form method="post" id="form1">
								<input type="hidden" name="merchantID" value="101093"/>
								<fieldset class="fieldset">
									<legend>Amount</legend>
									<select name="amount">
										<option value="500">£5.00</option>
										<option value="1000">£10.00</option>
										<option value="1500">£15.00</option>
										<option value="2000">£20.00</option>
										<option value="10000">£1000.00</option>
									</select><br>
								</fieldset>
								<fieldset class="fieldset">
									<legend>Customer Name</legend>
									<input type="text" name="customerName" placeholder="customer name"/><br>
								</fieldset>
								<fieldset class="fieldset">
									<legend>Card Number</legend>
									<input type="hostedfield:cardNumber" id="form1-card-number" placeholder="**** **** **** ****"/><br>
								</fieldset>
								<fieldset class="fieldset">
									<legend>Expire Date</legend>
									<input type="hostedfield:cardExpiryDate" placeholder="dd/yy"/><br>
								</fieldset>
								<fieldset class="fieldset">
									<legend>CVV</legend>
									<input type="hostedfield:cardCVV"/><br>
								</fieldset>
								<input type="submit" class="button expanded" value="PAY" onclick="this.value = 'Please Wait..';" >
							</form>
						</p>
					<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</body>
	<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
	<script src="<?=htmlentities($websdk_url)?>js/hostedfields.min.js"></script>
	<script src="js/vendor/what-input.js"></script>
	<script src="js/vendor/foundation.js"></script>
	<script src="js/app.js"></script>
	<script>
		//Initialize hosted form
		$(document).ready(function () {
			var $form1 = $('#form1');
			var auto = {
					autoSetup: true,		// Auto setup the form creating all hosted fields (default)
					autoSubmit: true,		// Auto validate, tokenize and submit the form (default)
			};
			try {
				var hf = $form1.hostedForm(auto);
			} catch(e) {
				console.log('Failed to create hosted form 1: ' + e);
				throw e;					// Can't continue with this script
			}
		});
	</script>
	<script>
		var popup = new Foundation.Reveal($('#payment'));
		popup.open();
    </script>
</html>

