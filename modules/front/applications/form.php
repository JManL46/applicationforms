<?php


namespace IPS\applicationform\modules\front\applications;

/* To prevent PHP errors (extending class does not exist) revealing path */
use IPS\Output;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * form
 */
class _form extends \IPS\Dispatcher\Controller
{

	protected $form;


	protected function manage()
	{
        \IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'forms.css', 'applicationform', 'front' ) );

		try
		{
			$this->form = \IPS\applicationform\Position::load( \IPS\Request::i()->id );
			$this->_showForm();
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'open_positions' );
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'forms' )->index();
		}
	}

	protected function _showForm()
	{
		$erroreason = 'no_module_permission';
		if ( !$this->form->canApply($erroreason) )
		{
			\IPS\Output::i()->error( $erroreason, '2app_cant_apply', 403, '' );
		}

		\IPS\Output::i()->breadcrumb[] = array( NULL, $this->form->_title );

		\IPS\Output::i()->title = $this->form->_title;


		$form = $this->form->form;

		if ( $values = $form->values() )
		{
			$this->form->handleForm( $values);
			if ( $this->form->redirect_target )
			{
				\IPS\Output::i()->redirect( $this->form->redirect_target );
			}
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'forms' )->done( $this->form );
		}
		else
		{
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'forms' )->form( $this->form  );
		}

	}

}