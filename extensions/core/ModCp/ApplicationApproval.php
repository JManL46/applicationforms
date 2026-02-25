<?php
/**
 * @brief		Moderator Control Panel Extension: ApplicationApproval
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Application Forms
 * @since		05 Aug 2017
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\applicationform\extensions\core\ModCp;

/* To prevent PHP errors (extending class does not exist) revealing path */
use IPS\applicationform\Position\Data;
use IPS\Output;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Moderator Control Panel Extension: ApplicationApproval
 */
class _ApplicationApproval
{
	/**
	 * Returns the primary tab key for the navigation bar
	 *
	 * @return	string|null
	 */
	public function getTab()
	{
		if ( ! \IPS\Member::loggedIn()->modPermission( 'can_see_applicationdata' ) )
		{
			return NULL;
		}

		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'front_modcp.js', 'applicationform', 'front' ) );

		return 'application_approval';
	}
	
	/**
	 * Manage
	 *
	 * @return	void
	 */
	public function manage()
	{
		$url =  \IPS\Http\Url::internal( "app=core&module=modcp&controller=modCp&tab=application_approval" );
		\IPS\Output::i()->breadcrumb[] = array( $url, \IPS\Member::loggedIn()->language()->addToStack( 'modcp_application_approval' ) );

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'modcp_application_approval' );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'forms', 'applicationform' )->modcp();
	}


	public function viewApplications(){
		$url =  \IPS\Http\Url::internal( "app=core&module=modcp&controller=modCp&tab=application_approval" );
		\IPS\Output::i()->breadcrumb[] = array( $url, \IPS\Member::loggedIn()->language()->addToStack( 'modcp_application_approval' ) );
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'modcp_application_approval' );

		$this->form = \IPS\applicationform\Position::load( \IPS\Request::i()->id );

		if ( !$this->form->can( 'view') )
		{
			\IPS\Output::i()->error( 'no_module_permission', '2applicationforms', 403 );
		}


		\IPS\Output::i()->breadcrumb[] = array( $url->setQueryString('id', $this->form->_id), $this->form->_title );
		$table = new \IPS\applicationform\Position\Table( $this->form->modcpUrl(), $this->form );

		return $table;
	}

	public function viewApplication()
	{
		$this->_setTitleAndBreadcrumb();
		$data = $this->getDataOrError();
		$url =  \IPS\Http\Url::internal( "app=core&module=modcp&controller=modCp&tab=application_approval" );
		\IPS\Output::i()->breadcrumb[] = array( $url->setQueryString('id', $data->position->id), $data->position->_title );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'data', 'applicationform' )->details($data);
	}


	public function approve()
	{
		$this->_setTitleAndBreadcrumb();
		$data = $this->getDataOrError();
		$data->changeState( Data::STATUS_APPROVED );
		Output::i()->redirect( $data->modcpUrl());
	}

	public function denial()
	{
		$this->_setTitleAndBreadcrumb();
		$data = $this->getDataOrError();
		$data->changeState( Data::STATUS_DECLINED );
		Output::i()->redirect( $data->modcpUrl());
	}

	public function delete()
	{
		if ( ! \IPS\Member::loggedIn()->modPermission( 'can_fapp_delete_submissions' ) )
		{
			\IPS\Output::i()->error( 'no_module_permission', '2applicationforms', 403 );
		}

		$this->_setTitleAndBreadcrumb();
		$data = $this->getDataOrError();
		\IPS\Request::i()->confirmedDelete();
		$url = \IPS\Http\Url::internal( "app=core&module=modcp&controller=modcp&tab=application_approval&id={$data->position_id}&action=viewApplications", 'front' );

		$data->delete();
		Output::i()->redirect( $url );
	}


    /**
     * @param null $id
     * @return \IPS\applicationform\Position\Data
     */
	protected function getDataOrError($id = NULL )
	{
		if ( !$id )
		{
			$id = \IPS\Request::i()->id;
		}

		$data = \IPS\applicationform\Position\Data::load( $id );

		if ( $data->position->can( 'view') )
		{
			return $data;
		}
		else
		{
			\IPS\Output::i()->error( 'no_module_permission', '2applicationforms', 403 );
		}
	}

	protected function _setTitleAndBreadcrumb()
	{
		$url =  \IPS\Http\Url::internal( "app=core&module=modcp&controller=modCp&tab=application_approval" );
		\IPS\Output::i()->breadcrumb[] = array( $url, \IPS\Member::loggedIn()->language()->addToStack( 'modcp_application_approval' ) );
		\IPS\Output::i()->breadcrumb[] = array( NULL, \IPS\Member::loggedIn()->language()->addToStack( 'application_Data' ) );
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'application_Data' );
	}

}