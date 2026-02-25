<?php


namespace IPS\applicationform\setup\upg_10048;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * 3.1.2 Upgrade Code
 */
class _Upgrade
{
	/**
	 * ...
	 *
	 * @return	array	If returns TRUE, upgrader will proceed to next step. If it returns any other value, it will set this as the value of the 'extra' GET parameter and rerun this step (useful for loops)
	 */
	public function step1()
	{
		if ( !\IPS\Db::i()->checkForColumn( 'applicationform_position', 'redirect_target' ) )
		{
			\IPS\Db::i()->addColumn( 'applicationform_position', array(
				'name'			=> 'redirect_target',
				'type'			=> "MEDIUMTEXT",
				'length'		=> null,
				'allow_null'	=> true,
				'default'		=> null
			) );
		}


		return TRUE;
	}
	
	// You can create as many additional methods (step2, step3, etc.) as is necessary.
	// Each step will be executed in a new HTTP request
}