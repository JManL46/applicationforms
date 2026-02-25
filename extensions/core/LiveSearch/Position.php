<?php
/**
 * @brief		ACP Live Search Extension
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
{subpackage}
 * @since		26 Nov 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\applicationform\extensions\core\LiveSearch;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	ACP Live Search Extension
 */
class _Position
{
	/**
	 * Get the search results
	 *
	 * @param	string	Search Term
	 * @return	array 	Array of results
	 */
	public function getResults( $searchTerm )
	{

		/* Check we have access */
		if( !$this->hasAccess() )
		{
			return array();
		}

		/* Init */
		$results = array();
		$searchTerm = mb_strtolower( $searchTerm );

		/* Perform the search */
		$groups = \IPS\Db::i()->select(
						"*",
						'applicationform_position',
						array( "word_custom LIKE CONCAT( '%', ?, '%' ) AND lang_id=?", $searchTerm, \IPS\Member::loggedIn()->language()->id ),
						NULL,
						NULL
					)->join(
						'core_sys_lang_words',
						"word_key=CONCAT( 'applicationform_position_', id )"
					);


		/* Format results */
		foreach ( $groups as $group )
		{
			$category = \IPS\applicationform\Position::constructFromData( $group );     //TODO

			$results[] = \IPS\Theme::i()->getTemplate( 'livesearch', 'applicationform', 'admin' )->Position( $category );
		}

		return $results;
	}

	/**
	 * Check we have access
	 *
	 * @return	bool
	 */
	public function hasAccess()
	{
		/* Check Permissions */
		return \IPS\Member::loggedIn()->hasAcpRestriction( 'applicationform', 'members', 'groups_manage' ); //TODO
	}

	/**
	 * Is default for current page?
	 *
	 * @return	bool
	 */
	public function isDefault()
	{
		return \IPS\Dispatcher::i()->application->directory == 'applicationform';
	}
}