<?php

namespace IPS\applicationform\extensions\core\FrontNavigation;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Front Navigation Extension: Node
 */
class _Node extends \IPS\core\FrontNavigation\FrontNavigationAbstract
{
	/**
	 * Get Type Title which will display in the AdminCP Menu Manager
	 *
	 * @return	string
	 */
	public static function typeTitle()
	{
		return \IPS\Member::loggedIn()->language()->addToStack('navbar_positions_node' );
	}

	/**
	 * Get configuration fields
	 *
	 * @param	array	$existingConfiguration	The existing configuration, if editing an existing item
	 * @param	int		$id						The ID number of the existing item, if editing
	 * @return	array
	 */
	public static function configuration( $existingConfiguration, $id = NULL )
	{
		$pages = array();
		foreach( new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( '*', 'applicationform_position' ), 'IPS\applicationform\Position' ) as $page )
		{

			$pages[ $page->id ] = $page->_title;
		}

		return array(
			new \IPS\Helpers\Form\Select( 'applicationsforms_position_id', isset( $existingConfiguration['applicationsforms_position_id'] ) ? $existingConfiguration['applicationsforms_position_id'] : NULL, NULL, array( 'options' => $pages ), NULL, NULL, NULL ),
		);
	}


	/**
	 * Can this item be used at all?
	 * For example, if this will link to a particular feature which has been diabled, it should
	 * not be available, even if the user has permission
	 *
	 * @return	bool
	 */
	public static function isEnabled()
	{
		return TRUE;
	}

	/**
	 * Can the currently logged in user access the content this item links to?
	 *
	 * @return	bool
	 */
	public function canAccessContent()
	{
		return $this->getPosition()->canView();
	}


	protected function getPosition()
	{
		return \IPS\applicationform\Position::load($this->configuration['applicationsforms_position_id']);
	}
	/**
	 * Get Title
	 *
	 * @return	string
	 */
	public function title()
	{
			return \IPS\Member::loggedIn()->language()->addToStack( "applicationform_position_{$this->configuration['applicationsforms_position_id']}" );
	}


	/**
	 * Get Link
	 *
	 * @return	\IPS\Http\Url
	 */
	public function link()
	{
		return $this->getPosition()->url();
	}

	/**
	 * Is Active?
	 *
	 * @return	bool
	 */
	public function active()
	{
		return \IPS\Dispatcher::i()->application->directory === 'applicationform' AND $this->configuration['applicationsforms_position_id'] == \IPS\Request::i()->id;
	}

	/**
	 * Children
	 *
	 * @param	bool	$noStore	If true, will skip datastore and get from DB (used for ACP preview)
	 * @return	array
	 */
	public function children( $noStore=FALSE )
	{
		return NULL;
	}
}