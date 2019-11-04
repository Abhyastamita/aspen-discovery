<?php

/** @noinspection PhpUnused */
class Hoopla_AJAX extends Action
{
	function launch() {
		global $timer;
		header('Content-type: application/json');
		header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past

		$method = (isset($_GET['method']) && !is_array($_GET['method'])) ? $_GET['method'] : '';
		if (method_exists($this, $method)) {
			$timer->logTime("Starting method $method");

			echo json_encode($this->$method());
		}else{
			echo json_encode(array('error'=>'invalid_method'));
		}
	}

	/** @noinspection PhpUnused */
	function reloadCover(){
		require_once ROOT_DIR . '/RecordDrivers/HooplaRecordDriver.php';
		$id = $_REQUEST['id'];
		$recordDriver = new HooplaRecordDriver($id);

		//Reload small cover
		$smallCoverUrl = str_replace('&amp;', '&', $recordDriver->getBookcoverUrl('small')) . '&reload';
		file_get_contents($smallCoverUrl);

		//Reload medium cover
		$mediumCoverUrl = str_replace('&amp;', '&', $recordDriver->getBookcoverUrl('medium')) . '&reload';
		file_get_contents($mediumCoverUrl);

		//Reload large cover
		$largeCoverUrl = str_replace('&amp;', '&', $recordDriver->getBookcoverUrl('large')) . '&reload';
		file_get_contents($largeCoverUrl);

		//Also reload covers for the grouped work
		require_once ROOT_DIR . '/RecordDrivers/GroupedWorkDriver.php';
		$groupedWorkDriver = new GroupedWorkDriver($recordDriver->getGroupedWorkId());
		//Reload small cover
		$smallCoverUrl = str_replace('&amp;', '&', $groupedWorkDriver->getBookcoverUrl('small', true)) . '&reload';
		file_get_contents($smallCoverUrl);

		//Reload medium cover
		$mediumCoverUrl = str_replace('&amp;', '&', $groupedWorkDriver->getBookcoverUrl('medium', true)) . '&reload';
		file_get_contents($mediumCoverUrl);

		//Reload large cover
		$largeCoverUrl = str_replace('&amp;', '&', $groupedWorkDriver->getBookcoverUrl('large', true)) . '&reload';
		file_get_contents($largeCoverUrl);

		return array('success' => true, 'message' => 'Covers have been reloaded.  You may need to refresh the page to clear your local cache.');
	}


	/** @noinspection PhpUnused */
	function getCheckOutPrompts(){
		$user = UserAccount::getLoggedInUser();
		$id = $_REQUEST['id'];
		if (strpos($id, ':') !== false) {
			list(, $id) = explode(':', $id);
		}
		if ($user) {
			$hooplaUsers = $user->getRelatedEcontentUsers('hoopla');

			require_once ROOT_DIR . '/Drivers/HooplaDriver.php';
			$driver = new HooplaDriver();

			if ($id) {
				global $interface;
				$interface->assign('hooplaId', $id);

				//TODO: need to determine what happens to cards without a Hoopla account
				$hooplaUserStatuses = array();
				foreach ($hooplaUsers as $tmpUser) {
					$checkOutStatus                   = $driver->getAccountSummary($tmpUser);
					$hooplaUserStatuses[$tmpUser->id] = $checkOutStatus;
				}

				if (count($hooplaUsers) > 1) {
					$interface->assign('hooplaUsers', $hooplaUsers);
					$interface->assign('hooplaUserStatuses', $hooplaUserStatuses);

					return
						array(
							'title'   => 'Hoopla Check Out',
							'body'    => $interface->fetch('Hoopla/ajax-checkout-prompt.tpl'),
							'buttons' => '<button class="btn btn-primary" type= "button" title="Check Out" onclick="return AspenDiscovery.Hoopla.checkOutHooplaTitle(\'' . $id . '\');">Check Out</button>'
						);
				} elseif (count($hooplaUsers) == 1) {
					/** @var User $hooplaUser */
					$hooplaUser = reset($hooplaUsers);
					if ($hooplaUser->id != $user->id) {
						$interface->assign('hooplaUser', $hooplaUser); // Display the account name when not using the main user
					}
					$checkOutStatus = $hooplaUserStatuses[$hooplaUser->id];
					if (!$checkOutStatus) {
						require_once ROOT_DIR . '/RecordDrivers/HooplaRecordDriver.php';
						$hooplaRecord = new HooplaRecordDriver($id);

                        // Base Hoopla Title View Url
                        $accessLink = $hooplaRecord->getAccessLink();
                        $hooplaRegistrationUrl = $accessLink['url'];
                        $hooplaRegistrationUrl .= (parse_url($hooplaRegistrationUrl, PHP_URL_QUERY) ? '&' : '?') . 'showRegistration=true'; // Add Registration URL parameter

                        return array(
                            'title'   => 'Create Hoopla Account',
                            'body'    => $interface->fetch('Hoopla/ajax-hoopla-single-user-checkout-prompt.tpl'),
                            'buttons' =>
                                '<button id="theHooplaButton" class="btn btn-default" type="button" title="Check Out" onclick="return AspenDiscovery.Hoopla.checkOutHooplaTitle(\'' . $id . '\', ' . $hooplaUser->id . ')">I registered, Check Out now</button>'
                                .'<a class="btn btn-primary" role="button" href="'.$hooplaRegistrationUrl.'" target="_blank" title="Register at Hoopla" onclick="$(\'#theHooplaButton+a,#theHooplaButton\').toggleClass(\'btn-primary btn-default\');">Register at Hoopla</a>'
                        );
					}
					if ($hooplaUser->hooplaCheckOutConfirmation) {
						$interface->assign('hooplaPatronStatus', $checkOutStatus);
						return
							array(
								'title'   => 'Confirm Hoopla Check Out',
								'body'    => $interface->fetch('Hoopla/ajax-hoopla-single-user-checkout-prompt.tpl'),
								'buttons' => '<button class="btn btn-primary" type="button" title="Check Out" onclick="return AspenDiscovery.Hoopla.checkOutHooplaTitle(\'' . $id . '\', ' . $hooplaUser->id . ')">Check Out</button>'
							);
					}else{
						// Go ahead and checkout the title
						return array(
							'title'   => 'Checking out Hoopla title',
							'body'    => "<script>AspenDiscovery.Hoopla.checkOutHooplaTitle('{$id}', '{$hooplaUser->id}')</script>",
							'buttons' => ''
						);
					}
				} else {
					// No Hoopla Account Found, give the user an error message
					$invalidAccountMessage = translate('hoopla_invalid_account_or_library');
					global $logger;
					$logger->log('No valid Hoopla account was found to check out a Hoopla title.', Logger::LOG_ERROR);
					return
						array(
							'title'   => 'Invalid Hoopla Account',
							'body'    => '<p class="alert alert-danger">'. $invalidAccountMessage .'</p>',
							'buttons' => ''
						);
				}
			} else {
                return array(
                    'title'   => 'Error',
                    'body'    => 'Item to checkout was not provided.',
                    'buttons' => ''
                );
            }
		}else{
            return array(
                'title'   => 'Error',
                'body'    => 'You must be logged in to checkout an item.'
                    .'<script>Globals.loggedIn = false;  AspenDiscovery.Hoopla.getCheckOutPrompts(\''.$id.'\')</script>',
                'buttons' => ''
            );
		}

	}

	/** @noinspection PhpUnused */
	function checkOutHooplaTitle() {
		$user = UserAccount::getLoggedInUser();
		if ($user){
			$patronId = $_REQUEST['patronId'];
			$patron   = $user->getUserReferredTo($patronId);
			if ($patron) {
				global $interface;
				if ($patron->id != $user->id) {
					$interface->assign('hooplaUser', $patron); // Display the account name when not using the main user
				}

				$id = $_REQUEST['id'];
				require_once ROOT_DIR . '/Drivers/HooplaDriver.php';
				$driver = new HooplaDriver();
				$result = $driver->checkOutTitle($patron, $id);
				if (!empty($_REQUEST['stopHooplaConfirmation'])) {
					$patron->hooplaCheckOutConfirmation = 0;
					$patron->update();
				}
				if ($result['success']) {
					$checkOutStatus = $driver->getAccountSummary($patron);
					$interface->assign('hooplaPatronStatus', $checkOutStatus);
					$title = empty($result['title']) ? "Title checked out successfully" : $result['title'] . " checked out successfully";
                    /** @noinspection HtmlUnknownTarget */
                    return array(
						'success' => true,
						'title'   => $title,
						'message' => $interface->fetch('Hoopla/hoopla-checkout-success.tpl'),
						'buttons' => '<a class="btn btn-primary" href="/MyAccount/CheckedOut" role="button">' . translate('View My Check Outs') . '</a>'
					);
				} else {
					return $result;
				}
			}else{
				return array('success'=>false, 'message'=>'Sorry, it looks like you don\'t have permissions to checkout titles for that user.');
			}
		}else{
			return array('success'=>false, 'message'=>'You must be logged in to checkout an item.');
		}
	}

	/** @noinspection PhpUnused */
	function returnCheckout() {
		$user = UserAccount::getLoggedInUser();
		if ($user){
			$patronId = $_REQUEST['patronId'];
			$patron   = $user->getUserReferredTo($patronId);
			if ($patron) {
				$id = $_REQUEST['id'];
				require_once ROOT_DIR . '/Drivers/HooplaDriver.php';
				$driver = new HooplaDriver();
				$result = $driver->returnCheckout($patron, $id);
				return $result;
			}else{
				return array('success'=>false, 'message'=>'Sorry, it looks like you don\'t have permissions to return titles for that user.');
			}
		}else{
			return array('success'=>false, 'message'=>'You must be logged in to return an item.');
		}
	}

}