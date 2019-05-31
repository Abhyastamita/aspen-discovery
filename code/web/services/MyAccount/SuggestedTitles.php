<?php

require_once ROOT_DIR . '/services/MyAccount/MyAccount.php';
require_once ROOT_DIR . '/services/MyResearch/lib/FavoriteHandler.php';
require_once ROOT_DIR . '/services/MyResearch/lib/Suggestions.php';

class SuggestedTitles extends MyAccount
{

	function launch()
	{
		global $interface;
		global $timer;

		$suggestions = Suggestions::getSuggestions();
		$timer->logTime("Loaded suggestions");

		$resourceList = array();
		$curIndex = 0;
		if (is_array($suggestions)) {
			foreach($suggestions as $suggestion) {
				$interface->assign('resultIndex', ++$curIndex);
				require_once ROOT_DIR . '/RecordDrivers/GroupedWorkDriver.php';
				/** @var GroupedWorkDriver $recordDriver */
				$recordDriver = new GroupedWorkDriver($suggestion['titleInfo']);
				$resourceEntry = $interface->fetch($recordDriver->getSearchResult());
				$resourceList[] = $resourceEntry;
			}
		}
		$timer->logTime("Loaded results for suggestions");
		$interface->assign('resourceList', $resourceList);

		//Check to see if the user has rated any titles
		$user = UserAccount::getLoggedInUser();
		$interface->assign('hasRatings', $user->hasRatings());

		$this->display('suggestedTitles.tpl', 'Recommended for You');
	}

}