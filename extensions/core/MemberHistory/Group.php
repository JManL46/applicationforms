<?php
/**
 * @brief		MemberHistory: Group
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	Application Forms
 * @since		22 Jul 2018
 */

namespace IPS\applicationform\extensions\core\MemberHistory;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Member History: Group
 */
class _Group
{
	/**
	 * Return the valid member history log types
	 *
	 * @return array
	 */
	public function getTypes()
	{
		return array(
			'group'
		);
	}

	/**
	 * Parse LogType column
	 *
	 * @param	string		$value		column value
	 * @param	array		$row		entire log row
	 * @return	string
	 */
	public function parseLogType( $value, $row )
	{
		return \IPS\Theme::i()->getTemplate( 'members', 'core' )->logType( $value );
	}

	/**
	 * Parse LogData column
	 *
	 * @param	string		$value		column value
	 * @param	array		$row		entire log row
	 * @return	string
	 */
	public function parseLogData( $value, $row )
	{
		$jsonValue = json_decode( $value, TRUE );

		if ( $row['log_by'] )
		{
			if ( $row['log_by'] === $row['log_member'] )
			{
				$byMember = \IPS\Member::loggedIn()->language()->addToStack('history_by_member');
			}

			$byStaff = \IPS\Member::loggedIn()->language()->addToStack('history_by_admin', FALSE, array( 'sprintf' => array( \IPS\Member::load( $row['log_by'] )->name ) ) );
		}

		switch( $row['log_type'] )
		{
			case 'group':
				foreach ( array('old', 'new') as $k )
				{
					$$k = array();
					foreach ( is_array( $jsonValue[$k] ) ? $jsonValue[$k] : array($jsonValue[$k]) as $id )
					{
						try
						{
							${$k}[] = \IPS\Theme::i()->getTemplate( 'members', 'core' )->groupLink( \IPS\Member\Group::load( $id ) );
						}
						catch ( \OutOfRangeException $e )
						{
							${$k}[] = \IPS\Member::loggedIn()->language()->addToStack( 'history_deleted_group_id', FALSE, array('sprintf' => array($id)) );
						}
					}
					if ( $$k )
					{
						$$k = \IPS\Member::loggedIn()->language()->formatList( $$k );
					}
					else
					{
						$$k = \IPS\Member::loggedIn()->language()->addToStack( 'history_no_groups' );
					}
				}

				try
				{
					$app = \IPS\applicationform\Position\Data::load( $jsonValue['id'] )->position;

					return \IPS\Member::loggedIn()->language()->addToStack( 'history_group_change_appform_' . $jsonValue['type'], FALSE, array( 'htmlsprintf' => array( $old, $new ), 'sprintf' => array( $app->_title ) ) );

				}
				catch ( \OutOfRangeException $e )
				{
					$app = "group changed by application form";
					return $app;
				}


		}
	}
}