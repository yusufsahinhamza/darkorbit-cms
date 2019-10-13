<?php
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
	$request = $_POST['action'];

  if ($request === 'register') {
    if (isset($_POST['username'], $_POST['password'], $_POST['email'])) {
    	echo Functions::Register(Functions::s($_POST['username']), Functions::s($_POST['password']), Functions::s($_POST['email']));
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
	} else if ($request === 'buy') {
		if (isset($_POST['itemId'])) {
			echo Functions::Buy(Functions::s($_POST['itemId']));
		}
	} else if ($request === 'delete_clan') {
		echo Functions::DeleteClan();
	} else {
    require_once(EXTERNALS . 'error.php');
  }

} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($page[1])) {
	$request = $page[1];

	if ($request === 'verify') {
		if (isset($page[2], $page[3]) && (!isset($page[4]) || isset($page[4]) && $page[4] === '')) {
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
