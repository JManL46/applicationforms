<?php
/**
 * @brief		Application Form Action extension
 * @since		01 Mar 2018
 */

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
class _CreatePost  extends \IPS\applicationform\Position\Extension
{

	public static function onSubmit( \IPS\applicationform\Position $position, array &$values, \IPS\Member $member = NULL )
	{
        $member = $member ? : \IPS\Member::loggedIn();
		$member = $member ? : \IPS\Member::loggedIn();
		$newValues = $position->prepareValues($values);

		if ( $position->options[ 'bw_create_post'] )
		{
			/* Create topic */
			$topic = \IPS\forums\Topic::load( $position->target_post );

			/* Create post */
			$content = \IPS\Theme::i()->getTemplate( 'forms' )->topic( $newValues );
			\IPS\Member::loggedIn()->language()->parseOutputForDisplay( $content );

			$post = \IPS\forums\Topic\Post::create( $topic, $content, TRUE, NULL, NULL, $member, \IPS\DateTime::create() );
			$post->save();
		#	$position->addDataToLog('post_id', $post->pid);
		}


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
		$topicToggles = [
			'position_target_post',
		];

		$form->add( new \IPS\Helpers\Form\YesNo( 'position_bw_create_post', $position->id ? $position->options['bw_create_post'] : FALSE , FALSE, [ 'togglesOn'=> $topicToggles ], NULL, NULL, NULL, 'position_bw_create_post') );


		$form->add(new \IPS\Helpers\Form\Item( 'position_target_post', $position ? $position->target_post : NULL, FALSE, array(
			'class' => 'IPS\forums\Topic',
			'maxItems' => 1,
			'minAjaxLength' => 1
		), NULL, NULL,NULL, 'position_target_post') );

		$desc = static::getFieldsDescriptionForTitleField( $position );

		\IPS\Member::loggedIn()->language()->words['position_topictitle_desc'] =  $desc;

		return $form;
	}

	public static function createFromForm( array &$values, \IPS\applicationform\Position $position )
	{
		if ( !isset( $values['position_bw_create_post'] ) OR !$values['position_target_post']  )
		{
			$toUnset = [ 'position_target_post' ];

			foreach ( $toUnset as $k )
			{
				unset($values[$k]);
			}
		}
		else
		{
			$item = array_pop($values['position_target_post']);
			if ($item)
			{
				$values['position_target_post'] = $item->tid;
			}
			else
			{
				$values['position_bw_create_post'] = 0;
				$values['position_target_post'] = 0;
			}
		}
	}
}