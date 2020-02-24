<?php
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
	$request = $_POST['action'];

  if ($request === 'register') {
    if (isset($_POST['username'], $_POST['password'], $_POST['password_confirm'], $_POST['email'])) {
    	echo Functions::Register(Functions::s($_POST['username']), Functions::s($_POST['password']), Functions::s($_POST['password_confirm']), Functions::s($_POST['email']));
    }
  }	else if ($request === 'login') {
		if (isset($_POST['username'], $_POST['password'])) {
			echo Functions::Login(Functions::s($_POST['username']), Functions::s($_POST['password']));
		}
	} else if ($request === 'company_select') {
		if (isset($_POST['company'])) {
			echo Functions::CompanySelect(Functions::s($_POST['company']));
		}
	} else if ($request === 'search_clan') {
		if (isset($_POST['keywords'])) {
			echo Functions::SearchClan(Functions::s($_POST['keywords']));
		}
	} else if ($request === 'send_clan_application') {
		if (isset($_POST['clanId'], $_POST['text'])) {
			echo Functions::SendClanApplication(Functions::s($_POST['clanId']), Functions::s($_POST['text']));
		}
	} else if ($request === 'withdraw_pending_application') {
		if (isset($_POST['clanId'])) {
			echo Functions::WithdrawPendingApplication(Functions::s($_POST['clanId']));
		}
	} else if ($request === 'found_clan') {
		if (isset($_POST['name'], $_POST['tag'], $_POST['description'])) {
			echo Functions::FoundClan(Functions::s($_POST['name']), Functions::s($_POST['tag']), Functions::s($_POST['description']));
		}
	} else if ($request === 'dismiss_clan_member') {
		if (isset($_POST['userId'])) {
			echo Functions::DismissClanMember(Functions::s($_POST['userId']));
		}
	} else if ($request === 'accept_clan_application') {
		if (isset($_POST['userId'])) {
			echo Functions::AcceptClanApplication(Functions::s($_POST['userId']));
		}
	} else if ($request === 'decline_clan_application') {
		if (isset($_POST['userId'])) {
			echo Functions::DeclineClanApplication(Functions::s($_POST['userId']));
		}
	} else if ($request === 'change_pilot_name') {
		if (isset($_POST['pilotName'])) {
			echo Functions::ChangePilotName(Functions::s($_POST['pilotName']));
		}
	} else if ($request === 'change_version') {
		if (isset($_POST['version'])) {
			echo Functions::ChangeVersion(Functions::s($_POST['version']));
		}
	} else if ($request === 'send_link_again') {
		if (isset($_POST['username'])) {
			echo Functions::SendLinkAgain(Functions::s($_POST['username']));
		}
	} else if ($request === 'diplomacy_search_clan') {
		if (isset($_POST['keywords'])) {
			echo Functions::DiplomacySearchClan(Functions::s($_POST['keywords']));
		}
	} else if ($request === 'request_diplomacy') {
		if (isset($_POST['clanId'], $_POST['diplomacyType'])) {
			echo Functions::RequestDiplomacy(Functions::s($_POST['clanId']), Functions::s($_POST['diplomacyType']));
		}
	} else if ($request === 'cancel_diplomacy_request') {
		if (isset($_POST['requestId'])) {
			echo Functions::CancelDiplomacyRequest(Functions::s($_POST['requestId']));
		}
	} else if ($request === 'decline_diplomacy_request') {
		if (isset($_POST['requestId'])) {
			echo Functions::DeclineDiplomacyRequest(Functions::s($_POST['requestId']));
		}
	} else if ($request === 'accept_diplomacy_request') {
		if (isset($_POST['requestId'])) {
			echo Functions::AcceptDiplomacyRequest(Functions::s($_POST['requestId']));
		}
	} else if ($request === 'end_diplomacy') {
		if (isset($_POST['diplomacyId'])) {
			echo Functions::EndDiplomacy(Functions::s($_POST['diplomacyId']));
		}
	} else if ($request === 'end_war_request') {
		if (isset($_POST['clanId'])) {
			echo Functions::RequestDiplomacy(Functions::s($_POST['clanId']), 4);
		}
	} else if ($request === 'buy') {
		if (isset($_POST['itemId'], $_POST['amount'])) {
			echo Functions::Buy(Functions::s($_POST['itemId']), Functions::s($_POST['amount']));
		}
	} else if ($request === 'use_researchPoints') {
		if (isset($_POST['skill'])) {
			echo Functions::UseResearchPoints(Functions::s($_POST['skill']));
		}
	} else if ($request === 'exchange_logdisks') {
		echo Functions::ExchangeLogdisks();
	} else if ($request === 'reset_skills') {
		echo Functions::ResetSkills();
	} else if ($request === 'delete_clan') {
		echo Functions::DeleteClan();
	} else if ($request === 'leave_clan') {
		echo Functions::LeaveClan();
	} else if ($request === 'change_ship') {
		if (isset($_POST['ship'])) {
			echo Functions::ChangeShip(Functions::s($_POST['ship']));
		}
	} else {
    require_once(EXTERNALS . 'error.php');
  }

} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($page[1])) {
	$request = $page[1];

	if ($request === 'verify') {
		if (isset($page[2], $page[3])) {
    	echo Functions::VerifyEmail(Functions::s($page[2]), Functions::s($page[3]));
		}
	} else if ($request === 'logout') {
		Functions::Logout();
	} else {
		require_once(EXTERNALS . 'error.php');
	}

} else {
	require_once(EXTERNALS . 'error.php');
}
?>
