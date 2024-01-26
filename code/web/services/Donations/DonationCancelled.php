<?php
require_once ROOT_DIR . "/sys/Donations/Donation.php";
require_once ROOT_DIR . "/sys/ECommerce/DonationsSetting.php";
require_once ROOT_DIR . "/sys/Account/UserPayment.php";

class Donations_DonationCancelled extends Action {
	public function launch() {
		global $interface;
		$error = '';
		$message = '';
		if (empty($_REQUEST['id'])) {
			$error = 'No Payment ID was provided, could not cancel the payment';
		} else {
			$paymentId = $_REQUEST['id'];
			require_once ROOT_DIR . '/sys/Account/UserPayment.php';
			$userPayment = new UserPayment();
			$userPayment->id = $paymentId;
			if ($userPayment->find(true)) {
				$userPayment->cancelled = true;
				$userPayment->update();
				$message = 'Your payment has been cancelled.';
			} else {
				$error = 'Incorrect Payment ID provided';
			}
		}
		$interface->assign('error', $error);
		$interface->assign('message', $message);
		$this->display('donationCancelled.tpl', 'Payment Cancelled');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/', 'Home');
		$breadcrumbs[] = new Breadcrumb('/Donations/NewDonation', 'Donations');
		$breadcrumbs[] = new Breadcrumb('', 'Donation Cancelled', true);
		return $breadcrumbs;
	}
}