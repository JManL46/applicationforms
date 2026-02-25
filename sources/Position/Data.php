<?php

namespace IPS\applicationform\Position;



use IPS\Helpers\Form\YesNo;
use IPS\Member;
use IPS\Patterns\ActiveRecord;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}


class _Data extends ActiveRecord
{


	CONST STATUS_OPEN = 0;
	CONST STATUS_APPROVED = 1;
	CONST STATUS_DECLINED = 2;


	protected static $multitons;
	public static $databaseTable = 'applicationform_applications';



	public function get_position()
	{
		return \IPS\applicationform\Position::load ( $this->position_id );
	}

	public function get_member()
	{
		return \IPS\Member::load( $this->member_id );
	}

	public function get_data()
	{
		$fields = $this->position->fields;
		$data =  json_decode( $this->_data['data'] );
		$return = [];
		foreach ( $fields as $id => $fielddata )
		{
			$field = \IPS\applicationform\Position\Field::load( $id );

			if ( isset( $data->$id ) )
			{
				$return[$field->_title] = $field->displayValue($data->$id);
			}
			else
			{
				$return[$field->_title] = "empty";
			}
		}
		return $return;
	}


	public function get_topic()
	{
		if ( !$this->topic_id )
		{
			return FALSE;
		}
		try
		{
			$topic = \IPS\forums\Topic::load( $this->topic_id );
			return $topic;
		}
		catch ( \OutOfRangeException $e )
		{
			return FALSE;
		}
	}

	public function modcpUrl( $action = 'viewApplication' )
	{

		$url = \IPS\Http\Url::internal( "app=core&module=modcp&controller=modcp&tab=application_approval&id={$this->id}", 'front' );
		if ( $action )
		{
			$url = $url->setQueryString('action', $action);
		}
		return $url;
	}

	public function changeState( $type )
	{
		$this->approved = $type;
		$this->approved_by = \IPS\Member::loggedIn()->member_id;


		if ( $type == static::STATUS_APPROVED )
		{
			$this->approve();
		}
		else
		{
			$this->deny();
		}

		$this->save();
	}


	public function deny()
	{
		foreach ( static::extensions() as $ext )
		{
			$ext::onDeny( $this );
		}
		\IPS\applicationform\Application::fireEvent( 'applicationform.application.denied', $this );
	}

	public function approve()
	{
		$member = $this->member;

		if ( $this->position->primary_group AND $member->member_group_id != $this->position->primary_group )
		{
			$this->old_primary_group = $member->primary_group;
			$member->member_group_id = $this->position->primary_group;
			$member->logHistory( 'applicationform', 'group', array( 'type' => 'primary', 'by' => 'applicationforms', 'action' => 'add', 'id' => $this->id, 'old' => $this->old_primary_group, 'new' => $member->member_group_id ) );
		}
		if ( $this->position->secondary_group )
		{
            $secondary = array_filter( explode( ',', $this->position->secondary_group ), function( $v ){ return (bool) $v; } );
			$current_secondary = $member->mgroup_others ? explode( ',', $member->mgroup_others ) : array();
			$new_secondary = $current_secondary;
			if ( !empty( $secondary ) )
			{
				foreach ( $secondary as $gid )
				{
					if ( !in_array( $gid, $new_secondary ) )
					{
						$new_secondary[] = $gid;
					}
				}
			}

			if ( $current_secondary != $new_secondary )
			{
				$member->mgroup_others = ',' . implode( ',', $new_secondary ) . ',';
				$member->logHistory( 'applicationform', 'group', array( 'type' => 'secondary', 'by' => 'applicationforms', 'action' => 'remove', 'id' => $this->id, 'old' => $current_secondary, 'new' => $new_secondary ) );
			}
		}
		// set end date
		if ( $this->position->groupdemotion_time )
		{
			$this->end_date = time() + $this->position->groupdemotion_time * 86400;
		}

		$member->save();


		foreach ( static::extensions() as $ext )
		{
			$ext::onApproval( $this );
		}

		\IPS\applicationform\Application::fireEvent( 'applicationform.application.approved', $this );
	}

	public function get_status()
	{
		switch ( $this->approved )
		{
			case 0:
				return 'Open';
			case 1:
				return 'approved';
			case 2:
				return 'declined';
		}
	}

	public function get_approved_by()
	{
		if ( $this->_data['approved_by'] )
		{
			return \IPS\Member::load( $this->_data['approved_by'] )->link();
		}
		return NULL;
	}


	public function delete()
	{
		foreach ( static::extensions() as $ext )
		{
			$ext::onSubmissionDelete( $this );
		}

		parent::delete();
	}

	static $extensions = NULL;

	public function extensions()
	{
		if ( !static::$extensions )
		{
			foreach ( \IPS\Application::allExtensions( 'applicationform', 'ApplicationAction', TRUE, NULL, NULL, FALSE ) as $ext )
			{
				static::$extensions[] = $ext;
			}
		}
		return static::$extensions;
	}

	public function onEnd()
	{
		// REMOVE group if neccessary
		$member = $this->member;

		if ( $this->position->groupdemotion_time  )
		{
			// primary group
			if ( $this->position->primary_group AND $member->member_group_id == $this->old_primary_group )
			{
				/* Make sure this group exists */
				try
				{
					\IPS\Member\Group::load( $this->old_primary_group );
					//Change it only back if it exists
					$member->member_group_id = $this->old_primary_group;
					$member->save();
					$member->logHistory( 'applicationform', 'group', array( 'type' => 'primary', 'by' => 'applicationforms', 'action' => 'remove', 'id' => $this->id, 'old' => $this->old_primary_group, 'new' => $member->member_group_id ) );
				}
				catch( \OutOfRangeException $e )
				{
					\IPS\Log::log( 'couldn\'t demote membergroup for ' . $member->member_id . ' because the target group doesnt exist', 'applicationform_log');
				}
			}

			//secondary groups
			else if ( $this->position->secondary_group )
			{
				$done = FALSE;
				$membersSecondaryGroups = $member->mgroup_others ? array_unique( array_filter( explode( ',', $member->mgroup_others ) ) ) : array();
				$currentSecondaryGroups = $membersSecondaryGroups;
				foreach( $membersSecondaryGroups as $group )
				{
					if ( $group == $this->position->secondary_group )
					{
						$done = TRUE;
						unset( $membersSecondaryGroups[ array_search( $group, $membersSecondaryGroups ) ] );
					}
				}

				/* And make sure only valid groups are saved */
				$membersSecondaryGroups = array_filter( $membersSecondaryGroups, function( $group ){
					try
					{
						\IPS\Member\Group::load( $group );
						return TRUE;
					}
					catch( \OutOfRangeException $e )
					{
						return FALSE;
					}
				});

				$member->mgroup_others = implode( ',', $membersSecondaryGroups );
				$member->save();
				$member->logHistory( 'applicationform', 'group', array( 'type' => 'secondary', 'by' => 'applicationforms', 'action' => 'remove', 'id' => $this->id, 'old' => $currentSecondaryGroups, 'new' => $membersSecondaryGroups ) );
			}
			$this->end_date = 0;
			$this->save();
		}

		foreach ( static::extensions() as $ext )
		{
			$ext::onEnd( $this );
		}

		\IPS\applicationform\Application::fireEvent( 'applicationform.application.end', $this );
	}
}