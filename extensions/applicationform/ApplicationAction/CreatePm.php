<?php
//TODO
namespace IPS\applicationform\extensions\applicationform\ApplicationAction;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Action extension
 */
class _CreatePm  extends \IPS\applicationform\Position\Extension
{

	public static function onSubmit( \IPS\applicationform\Position $position, array &$values, \IPS\Member $member = NULL )
	{
        $member = $member ? : \IPS\Member::loggedIn();

	}


	public static function onApproval( \IPS\applicationform\Position\Data $data, \IPS\Member $approver = NULL )
	{

	}

	public static function onDeny( \IPS\applicationform\Position\Data $data, \IPS\Member $approver = NULL )
	{

	}

	public static function onFormDelete( \IPS\applicationform\Position $position )
	{

	}

	public static function onSubmissionDelete( \IPS\applicationform\Position\Data $data )
	{

	}

	public static function form( \IPS\Helpers\Form &$form, \IPS\applicationform\Position $position )
	{
		return;
		$topicToggles = [
			'position_pm_sender',
			'position_pm_title',
			'position_pm_content'
		];

		$form->add( new \IPS\Helpers\Form\YesNo( 'position_bw_create_pm', $position->id ? $position->options['bw_create_pm'] : FALSE , FALSE, [ 'togglesOn'=> $topicToggles ], NULL, NULL, NULL, 'position_bw_create_pm') );


		$desc = static::getFieldsDescriptionForTitleField( $position );

		\IPS\Member::loggedIn()->language()->words['position_pm_title_desc'] =  $desc;

		$form->add( new \IPS\Helpers\Form\Text( 'position_pm_title', $position->id ? $position->pm_title : NULL, TRUE, [],NULL, NULL, NULL, 'position_pm_title' ) );


		$form->add(  new \IPS\Helpers\Form\Editor( 'position_pm_content' , $position->id ? $position->pm_content : NULL, FALSE, array(
			'app'			=>"applicationform",
			'key'			=> 'Pm',
			'autoSaveKey' 	=> 'pmcontent',
			'minimize'		=> "void_add_note_placeholder",
		), NULL, NULL, NULL, 'position_pm_content' ) );


		return $form;
	}

	public static function createFromForm( array &$values, \IPS\applicationform\Position $position )
	{
		/* Save form submit data */

		
	}
}