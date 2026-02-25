<?php

namespace IPS\applicationform\extensions\trophies\TrophyCriteria;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}


class _Applicationform
{

    public static function isActive()
    {
        return TRUE;
    }

	public static function getExtraAreas( \IPS\Helpers\Form &$form , \IPS\trophies\Trophy $trophy = NULL )
	{
		$form->addHeader('__app_applicationform');
		$form->add( new \IPS\Helpers\Form\Number( 'trophy_position_applied', isset( $trophy->id, $trophy->crdata['position_applied'] ) ? $trophy->crdata['position_applied'] : NULL ) );
	}

	public static function addMemberCriteria( \IPS\Helpers\Form &$form , \IPS\trophies\Trophy $trophy = NULL )
	{

	}

	public static function addContentCriteria( \IPS\Helpers\Form &$form , \IPS\trophies\Trophy $trophy = NULL )
	{

	}

	public static function handleForm( &$values, &$data )
	{
		foreach ( [
					  'position_applied',
				  ]
				  AS $field )
		{
			if ( isset( $values[$field] ) )
			{
				if ( $values[$field] > 0 )
				{
					$data[$field] = $values[$field];
				}

				unset( $values[$field] );
			}
		}
	}

	public static function memberMeetsCriteria( \IPS\Member $member, \IPS\trophies\Trophy $trophy, &$failedStep = NULL )
	{
		if ( isset( $trophy->crdata['position_applied'] ) AND $trophy->crdata['position_applied'] > 0 )
		{
			try
			{
				$count = \IPS\Db::i()->select( 'count(*)', 'applicationform_applications', [ ' member_id=?', $member->member_id] )->first();
				if ( $count >= $trophy->crdata['position_applied'] )
				{
					return TRUE;
				}
				else
				{
					return FALSE;
				}
			}
			catch ( \UnderflowException $e )
			{
				return FALSE;
			}
		}

		return TRUE;
	}


}