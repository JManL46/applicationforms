<?php
/**
 * @brief		WhoWasOnline Widget
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	whowasonline
 * @since		24 Oct 2024
 */

namespace IPS\whowasonline\widgets;

use IPS\Helpers\Form;
use IPS\Helpers\Form\Number;
use IPS\Helpers\Form\Select;
use IPS\Helpers\Form\Radio;
use IPS\Helpers\Form\YesNo;
use IPS\Member\Group;
use IPS\Member;
use function defined;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * WhoWasOnline Widget
 */
class WhoWasOnline extends \IPS\Widget
{
	/**
	 * @brief	Widget Key
	 */
	public string $key = 'WhoWasOnline';
	
	/**
	 * @brief	App
	 */
	public string $app = 'whowasonline';
	
	/**
	 * Initialise this widget
	 *
	 * @return void
	 */ 
	public function init() : void
	{
		// Use this to perform any set up and to assign a template that is not in the following format:
		// $this->template( array( \IPS\Theme::i()->getTemplate( 'widgets', $this->app, 'front' ), $this->key ) );
		// If you are creating a plugin, uncomment this line:
		// $this->template( array( \IPS\Theme::i()->getTemplate( 'plugins', 'core', 'global' ), $this->key ) );
		// And then create your template at located at plugins/<your plugin>/dev/html/WhoWasOnline.phtml
		
		
		parent::init();
	}
	
	/**
	 * Specify widget configuration
	 *
	 * @param	null|Form	$form	Form object
	 * @return	Form
	 */
	public function configuration( Form &$form=null ): Form
	{
 		$form = parent::configuration( $form );

 		$form->add( new Number( 'whowasonline_hours', isset( $this->configuration['whowasonline_hours'] ) ? $this->configuration['whowasonline_hours'] : 24, TRUE ) );
		$form->add( new Select( 'whowasonline_access', isset( $this->configuration['whowasonline_access'] ) ? $this->configuration['whowasonline_access'] : '*', TRUE, array( 'options' => Group::groups(), 'parse' => 'normal', 'multiple' => true, 'unlimited' => '*', 'unlimitedLang' => 'everyone' ) ) );
		$form->add( new Number( 'whowasonline_max_members', isset( $this->configuration['whowasonline_max_members'] ) ? $this->configuration['whowasonline_max_members'] : 100, FALSE ) );
		$form->add( new Select( 'whowasonline_exclude_groups', isset( $this->configuration['whowasonline_exclude_groups'] ) ? $this->configuration['whowasonline_exclude_groups'] : '', FALSE, array( 'options' => Group::groups(), 'parse' => 'normal', 'multiple' => true ) ) );
		$form->add( new Select( 'whowasonline_sort_order', isset( $this->configuration['whowasonline_sort_order'] ) ? $this->configuration['whowasonline_sort_order'] : 'visit', TRUE, array( 'options' => array('name' => 'whowasonline_name', 'group' => 'whowasonline_group', 'visit' => 'whowasonline_last_visit'), 'multiple' => FALSE ) ) );
		$form->add( new Radio( 'whowasonline_order_by', isset( $this->configuration['whowasonline_order_by'] ) ? $this->configuration['whowasonline_order_by'] : 'desc', TRUE, array( 'options' => array('desc' => 'whowasonline_desc', 'asc' => 'whowasonline_asc') ) ) );

 		return $form;
 	} 
 	
 	 /**
 	 * Ran before saving widget configuration
 	 *
 	 * @param	array	$values	Values from form
 	 * @return	array
 	 */
 	public function preConfig( array $values ): array
 	{
 		return $values;
 	}

	/**
	 * Render a widget
	 *
	 * @return	string
	 */
	public function render(): string
	{
		if( !$this->configuration )
		{
			return "";
		}

		// Access
		if ( $this->configuration['whowasonline_access'] AND $this->configuration['whowasonline_access'] != '*' AND ! Member::loggedIn()->inGroup( $this->configuration['whowasonline_access'], TRUE ) )
		{	
			return "";
		}

		// Ordering
		switch( $this->configuration['whowasonline_order_by'] )
		{
			case 'name':
				$orderBy = 'name';
			break;
			case 'group':
				$orderBy = 'member_group_id';
			break;
				$orderBy = 'last_activity';
			break;
			case 'visit':
			default:
				$orderBy = 'last_activity';
			break;
		}

		// Sorting
		$sortBy = $this->configuration['whowasonline_order_by'] == 'desc' ? 'DESC' : 'ASC';

		// Hours
		$hours = $this->configuration['whowasonline_hours'] ?: 24;

		$where = array(
			array( 'last_activity>' . \IPS\DateTime::create()->sub( new \DateInterval( 'PT' . $hours . 'H' ) )->getTimeStamp())
		);

		// Exclude groups
		if( $this->configuration['whowasonline_exclude_groups'] AND \count( $this->configuration['whowasonline_exclude_groups'] ) ) {
			$where[] = array( 'member_group_id NOT IN(' . implode( ',', $this->configuration['whowasonline_exclude_groups'] ) . ')' );
		}

		// Select members
		$members = iterator_to_array( \IPS\Db::i()->select( '*', 'core_members', $where, "{$orderBy} {$sortBy}" )->setKeyField( 'member_id' ) );

		// Count
		$count = \count( $members );

		// Caching
		if(!isset( \IPS\Data\Store::i()->whowasonline_count ) OR $count > \IPS\Data\Store::i()->whowasonline_count) 
		{
			\IPS\Data\Store::i()->whowasonline_count = $count;
			\IPS\Data\Store::i()->whowasonline_time = \IPS\DateTime::create()->getTimeStamp();
		}

		// Max members
		if( $this->configuration['whowasonline_max_members'] > 0 )
		{
			$members = \array_slice( $members, 0, $this->configuration['whowasonline_max_members'] );
		}

		return $this->output( $members, $count, $hours );
	}
}