<?php
/**
 * @brief		OpenPositions Widget
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
{subpackage}
 * @since		21 Jan 2017
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\applicationform\widgets;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * OpenPositions Widget
 */
class _OpenPositions extends \IPS\Widget\PermissionCache
{
	/**
	 * @brief	Widget Key
	 */
	public $key = 'OpenPositions';

	/**
	 * @brief	App
	 */
	public $app = 'applicationform';

	/**
	 * @brief	Plugin
	 */
	public $plugin = '';




 	 /**
 	 * Ran before saving widget configuration
 	 *
 	 * @param	array	$values	Values from form
 	 * @return	array
 	 */
 	public function preConfig( $values )
 	{
 		return $values;
 	}

	/**
	 * Render a widget
	 *
	 * @return	string
	 */
	public function render()
	{
		$openPositions = \IPS\applicationform\Position::roots();

	    $params = [];
		/** @var \IPS\applicationform\Position $pos */
		foreach ( $openPositions as $pos  )
		{
			if ( $pos->can('view' ) )
			{
				$params[] = $pos;
			}
		}

		return $this->output( $params );
	}
}