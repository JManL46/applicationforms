<?php

namespace IPS\applicationform\Helper;


if (!defined('\IPS\SUITE_UNIQUE_KEY'))
{
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}


abstract class _Notification
{

	public static function getNotificationRecipients( $permission = 'can_see_applicationdata' )
	{
		$moderators = array( 'm' => array(), 'g' => array() );
		foreach (\IPS\Db::i()->select( '*', 'core_moderators' ) as $mod)
		{
			$canView = FALSE;

			if ($mod['perms'] == '*')
			{
				$canView = TRUE;
			}

			if ($canView === FALSE)
			{
				$perms = json_decode($mod['perms'], TRUE);

				if (isset($perms[$permission]) AND $perms[$permission] === TRUE)
				{
					$canView = TRUE;
				}
			}

			if ($canView === TRUE)
			{
				$moderators[$mod['type']][] = $mod['id'];
			}
		}

		return $moderators;
	}

	public static function sendNotificationForSubmission($dataOrId)
	{
		$members = static::getNotificationRecipients();

		if (is_int( $dataOrId ) )
		{
			$dataOrId = \IPS\applicationform\Position\Data::load($dataOrId);
		}

		$notification = new \IPS\Notification(\IPS\Application::load('applicationform'), 'submission', $dataOrId, array($dataOrId));
		foreach (\IPS\Db::i()->select('*', 'core_members', (count($members['m']) ? \IPS\Db::i()->in('member_id', $members['m']) . ' OR ' : '') . \IPS\Db::i()->in('member_group_id', $members['g']) . ' OR ' . \IPS\Db::i()->findInSet('mgroup_others', $members['g'])) as $member)
		{
			$notification->recipients->attach(\IPS\Member::constructFromData($member));
		}

		$notification->send();
	}
}