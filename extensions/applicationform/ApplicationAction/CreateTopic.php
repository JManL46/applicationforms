<?php

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
class _CreateTopic extends \IPS\applicationform\Position\Extension
{

	public static function onSubmit( \IPS\applicationform\Position $position, array &$values, \IPS\Member $member = NULL )
	{
		$member = $member ? : \IPS\Member::loggedIn();
		$newValues = $position->prepareValues($values);
		$topicId = 0;
		if ( $position->options[ 'bw_create_topic'] )
		{
			$topicCreator = $member;

			/* Create topic */
			$topic = \IPS\forums\Topic::createItem( $topicCreator, \IPS\Request::i()->ipAddress(), \IPS\DateTime::create(), \IPS\forums\Forum::load( $position->target_forum ) );
			$title= sprintf( $position->topictitle, $position->titleForLog(),  $topicCreator->name );

			foreach ( $newValues as $i => $v)
			{
				$title = \str_replace("[applicationformfield_" . $position::$fieldsLookup[$i] . "]", $v, $title);
			}

			$topic->title = $title;
			$topic->save();

			/* Create post */
			$content = \IPS\Theme::i()->getTemplate( 'forms' )->topic( $newValues );
			\IPS\Member::loggedIn()->language()->parseOutputForDisplay( $content );

			$post = static::createPost($topic, $topicCreator, $content);

			$topic->topic_firstpost = $post->pid;

			/* Create Poll */
			if ( $position->options[ 'bw_open_poll'] )
			{
				$poll = new \IPS\Poll;
				$poll->starter_id = $topicCreator->member_id;
				$data['title'] = $topicCreator->name;

				$data['questions'][1] = ['title' => 'Poll'];
				$optionCount = 1;
				foreach (array_filter ( explode( ',', $position->vote_options ) ) as $option)
                {
                    $data['questions'][1]['answers'][$optionCount] = ['value' => $option];
                    $optionCount++;
                }

				$poll->setDataFromForm( $data, TRUE );
				$poll->save();
				$pollColumn = \IPS\forums\Topic::$databaseColumnMap['poll'];
				$topic->$pollColumn = $poll->pid;
			}

			$topic->save();
			$topic->markRead();

			$topicId = $topic->tid;
		}

		$position->addDataToLog('topic_id', $topicId);
	}

	protected static function createPost( $topic, $creator, $content )
	{
		$post = \IPS\forums\Topic\Post::create( $topic, $content, TRUE, NULL, NULL, $creator, \IPS\DateTime::create() );
		$post->save();
		return $post;
	}


	public static function onApproval( \IPS\applicationform\Position\Data $data, \IPS\Member $approver = NULL )
	{
		if ( $data->position->options['bw_create_topic_reply'] AND $data->topic )
		{
			$approver = $approver ? : \IPS\Member::loggedIn();
			$post = \IPS\Member::loggedIn()->language()->addToStack( 'applicationform_post_approved', FALSE, [ 'htmlsprintf' => $approver->name ] );
			\IPS\Member::loggedIn()->language()->parseOutputForDisplay( $post );
			static::createPost( $data->topic, $approver, $post );
		}
	}

	public static function onDeny( \IPS\applicationform\Position\Data $data, \IPS\Member $approver = NULL )
	{
		if ( $data->position->options['bw_create_topic_reply'] AND $data->topic )
		{
			$approver = $approver ? : \IPS\Member::loggedIn();
			$post = \IPS\Member::loggedIn()->language()->addToStack( 'applicationform_post_denied', FALSE, [ 'htmlsprintf' => $approver->name ] );
			\IPS\Member::loggedIn()->language()->parseOutputForDisplay( $post );
			static::createPost( $data->topic, $approver, $post );
		}
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
			'position_target_forum',
			'position_topictitle',
			'position_bw_open_poll',
			'position_bw_create_topic_reply'
		];

		$form->add( new \IPS\Helpers\Form\YesNo( 'position_bw_create_topic', $position->id ? $position->options['bw_create_topic'] : FALSE , FALSE, [ 'togglesOn'=> $topicToggles ], NULL, NULL, NULL, 'position_bw_create_topic') );

		$form->add( new \IPS\Helpers\Form\Node( 'position_target_forum', $position->id ? $position->target_forum : NULL, FALSE, array(
			'class'		      => '\IPS\forums\Forum',
			'disabled'	      => false,
			'permissionCheck' => function( $node )
			{
				return $node->sub_can_post;
			}
		), NULL, NULL, NULL, 'position_target_forum'));
		$desc = static::getFieldsDescriptionForTitleField( $position );

		\IPS\Member::loggedIn()->language()->words['position_topictitle_desc'] =  $desc;

		$form->add( new \IPS\Helpers\Form\Text( 'position_topictitle', $position->id ? $position->topictitle : NULL, FALSE, [],function( $val ) use ( $position )
		{

			if ( ( $val == '' OR $val == NULL ) AND isset( $_REQUEST['position_bw_create_topic_checkbox']) AND $_REQUEST['position_bw_create_topic_checkbox'] == 1 )
			{
				throw new \DomainException( 'applicationform_invalid_title' );
			}
		}, NULL, NULL, 'position_topictitle' ) );

		$form->add( new \IPS\Helpers\Form\YesNo( 'position_bw_open_poll', $position->id ? $position->options['bw_open_poll'] : FALSE , FALSE, array(
            'togglesOn'	=> array( 'position_vote_options' ) ), NULL, NULL, NULL, 'position_bw_open_poll') );

		$voteOptions = array_filter ( explode( ',', $position->vote_options ) );
		if ( count($voteOptions) === 0)
        {
            array_push($voteOptions,
                \IPS\Member::loggedIn()->language()->get ('appform_voteoption_yes'),
                \IPS\Member::loggedIn()->language()->get ('appform_voteoption_no'));
        }

        $form->add( new \IPS\Helpers\Form\Stack( 'position_vote_options', $voteOptions , TRUE, [], NULL, NULL, NULL, 'position_vote_options' ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'position_bw_create_topic_reply', $position->id ? $position->options['bw_create_topic_reply'] : TRUE , FALSE, [ ], NULL, NULL, NULL, 'position_bw_create_topic_reply') );

		return $form;
	}

	public static function createFromForm( array &$values, \IPS\applicationform\Position $position )
	{
		if ( !isset( $values['position_bw_create_topic'] ) OR !$values['position_bw_create_topic']  )
		{
			$toUnset = ['position_target_forum', 'position_topictitle', 'position_bw_open_poll','position_bw_create_topic_reply'];

			foreach ( $toUnset as $k )
			{
				unset($values[$k]);
			}
		}
	}
}