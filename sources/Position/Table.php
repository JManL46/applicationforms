<?php


namespace IPS\applicationform\Position;

/* To prevent PHP errors (extending class does not exist) revealing path */
use IPS\applicationform\Application;
use IPS\applicationform\Position;
use IPS\DateTime;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}


class _Table extends \IPS\Helpers\Table\Db
{

	/**
	 * @brief    Rows
	 */
	protected static $rows = null;

	/**
	 * @brief    WHERE clause
	 */
	public $where = array();

	public function setPosition ( Position $position )
	{
		$this->where[] = array( 'position_id=?', $position->_id );
	}

	public function __construct( \IPS\Http\Url $url, Position $position )
	{
		parent::__construct('applicationform_applications', $url);

		$this->tableTemplate = array(\IPS\Theme::i()->getTemplate('tables', 'core', 'admin'), 'table');
		$this->rowsTemplate = array(\IPS\Theme::i()->getTemplate('tables', 'core', 'admin'), 'rows');
		$this->include = array( 'member_id', 'date','topic_id', 'data', 'approved',);
		$this->langPrefix = 'applications_';

		$this->setPosition( $position );
		/* Filters */
		$this->filters = array(
	#		'application_approved'			=> 'approved=1',
	#		'application_declined'			=> 'approved=2',
		);

		$this->noSort = ['topic_id', 'data'];

		$this->parsers = array(
			'member_id' => function ($val, $row) {
				$member = \IPS\Member::load($val);
				return "<a href='" . \IPS\Http\Url::internal('app=core&module=members&controller=members&do=edit&id=') . $row['member_id'] . "'>" . htmlentities($member->name, ENT_DISALLOWED, 'UTF-8', FALSE) . "</a>";
			},
			'date' => function ( $val, $row )
			{
				if ( $val )
				{
					return DateTime::ts( $val );
				}
			},
			'topic_id' => function ($val, $row) {
				try
				{
					$topic = \IPS\forums\Topic::load($row['topic_id']);
					return "<a target='_blank' href='" . $topic->url() . "'>" . \IPS\Member::loggedIn()->language()->addToStack('topic') . "</a>";
				} catch (\OutOfRangeException $e)
				{
					return "";
				}

			},
			'data' => function( $val, $row )
			{
				return "<a target='_blank' href='" . \IPS\Http\Url::internal("app=core&module=modcp&controller=modcp&tab=application_approval&action=viewApplication&id={$row['id']}", 'front')  . "'>" . \IPS\Member::loggedIn()->language()->addToStack('applicationform_view_data') . "</a>";
			},
			'approved' => function ($val, $row) {

				if ($val == 1)
				{
					return '<i class="fa fa-check" aria-hidden="true"></i>';
				}
				else
				{
					return '';
				}
			},
			'votes' => function ($val, $row) {
				try
				{
					$topic = \IPS\forums\Topic::load($row['topic_id']);
					$poll = $topic->getPoll();
					$votes = iterator_to_array($poll->getVotes());

					return $votes;
				} catch (\OutOfRangeException $e)
				{
					return "";
				}
			});
	}



}