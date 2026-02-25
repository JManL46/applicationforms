<?php
/**
 * @brief		Uninstall callback
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
{subpackage}
 * @since		26 Nov 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\applicationform\extensions\core\Uninstall;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Uninstall callback
 */
class _Uninstall
{
	/**
	 * Code to execute before the application has been uninstalled
	 *
	 * @param	string	$application	Application directory
	 * @return	array
	 */
	public function preUninstall( $application )
	{
	}

	/**
	 * Code to execute after the application has been uninstalled
	 *
	 * @param	string	$application	Application directory
	 * @return	array
	 */
	public function postUninstall( $application )
	{
			$customTableChanges = [];

    		foreach ( $customTableChanges AS $table => $column )
    		{
    			if( \IPS\Db::i()->checkForColumn( $table, $column ) )
    			{
    				\IPS\Db::i()->dropColumn( $table, $column );
    			}
    		}
	}

	/**
	 * Code to execute when other applications are uninstalled
	 *
	 * @param	string	$application	Application directory
	 * @return	void
	 * @deprecated	This is here for backwards-compatibility - all new code should go in onOtherUninstall
	 */
	public function onOtherAppUninstall( $application )
	{
		return $this->onOtherUninstall( $application );
	}

	/**
	 * Code to execute when other applications or plugins are uninstalled
	 *
	 * @param	string	$application	Application directory
	 * @param	int		$plugin			Plugin ID
	 * @return	void
	 */
	public function onOtherUninstall( $application=NULL, $plugin=NULL )
	{
	}
}