//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class applicationform_hook_Forum extends _HOOK_CLASS_
{
    /**
     * @brief    Database column for the target_forum
     */
    public static $databaseColumnTargetForum = 'target_forum';

    /**
     * @brief    Database column for the target_forum
     */
    public static $databaseColumnOptions = 'options';

    /**
     * @brief    Is there a application form assigned
     */
    protected $_applicationFormAssigned = NULL;

    /**
     * Check permissions
     *
     * @param    mixed $permission A key which has a value in static::$permissionMap['view'] matching a column ID in core_permission_index
     * @param    \IPS\Member|\IPS\Member\Group|NULL $member The member or group to check (NULL for currently logged in member)
     * @return    bool
     * @throws    \OutOfBoundsException    If $permission does not exist in static::$permissionMap
     */
    public function can($permission, $member = NULL)
    {
	try
	{
	        if ($permission === 'apply')
	        {
	            /* Load member */
	            if ($member === NULL)
	            {
	                $member = \IPS\Member::loggedIn();
	            }
	
	            return $this->canMemeberApply($member);
	
	        } else if ($permission === 'add' AND $this->isApplicationFormAssigned())
	        {
	            /* Load member */
	            if ($member === NULL)
	            {
	                $member = \IPS\Member::loggedIn();
	            }
	
	            /* If we're checking add permissions - make sure we are not over our posts per day limit */
	            if ($member instanceof \IPS\Member)
	            {
	                if ($member->checkPostsPerDay() === FALSE)
	                {
	                    return FALSE;
	                }
	            }
	
	            if ($this->password !== NULL and (($member !== NULL and $member !== \IPS\Member::loggedIn()) or !$this->loggedInMemberHasPasswordAccess()))
	            {
	                return FALSE;
	            }
	            return $this->canMemeberApply( $member );
	        }
	
	        return parent::can($permission, $member);
	}
	catch ( \RuntimeException $e )
	{
		if ( method_exists( get_parent_class(), __FUNCTION__ ) )
		{
			return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
		}
		else
		{
			throw $e;
		}
	}
    }

    /**
     * Check if there is any application form assigned => if TRUE, we disable the 'create new topic' button
     */
    public function isApplicationFormAssigned()
    {
	try
	{
	        if ($this->_applicationFormAssigned === NULL)
	        {
	            try
	            {
	                $bwCreateTopic = \IPS\applicationform\Position::$bitOptions['options']['options']['bw_create_topic'];
	                $this->_applicationFormAssigned = (\IPS\Db::i()->select('*', 'applicationform_position', array(static::$databaseColumnTargetForum . '=? AND ' . static::$databaseColumnOptions . '& ? = ?' , $this->id, $bwCreateTopic, $bwCreateTopic))->count()) > 0;
	            }
	            catch (\UnderflowException $e)
	            {
	                $this->_applicationFormAssigned = FALSE;
	            }
	        }
	        return $this->_applicationFormAssigned;
	}
	catch ( \RuntimeException $e )
	{
		if ( method_exists( get_parent_class(), __FUNCTION__ ) )
		{
			return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
		}
		else
		{
			throw $e;
		}
	}
    }

    /**
     * Has the member the permission to apply this application form
     */
    protected function canMemeberApply($member)
    {
	try
	{
	        if ($this->password !== NULL and (($member !== NULL and $member !== \IPS\Member::loggedIn()) or !$this->loggedInMemberHasPasswordAccess()))
	        {
	            return FALSE;
	        }
	
	        try
	        {
            // use read, because read has permission value '2' and apply uses this permission table column 'perm_2'
	            $applicationFormPermission = $this->getFirstAccessibleApplicationForm('read', $member) !== NULL;
	        }
	        catch (\UnderflowException $e)
	        {
	            $applicationFormPermission = FALSE;
	        }
	        return $applicationFormPermission;
	}
	catch ( \RuntimeException $e )
	{
		if ( method_exists( get_parent_class(), __FUNCTION__ ) )
		{
			return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
		}
		else
		{
			throw $e;
		}
	}
    }

    /**
     * Checks if user has the permission for the application form
     *
     * @param    string|NULL $permissionCheck The permission key to check for or NULL to not check permissions
     * @param    \IPS\Member|NULL $member The member to check permissions for or NULL for the currently logged in member
     * @param    mixed $where Additional WHERE clause
     * @return    array
     */
    public function getFirstAccessibleApplicationForm($permissionCheck = 'view', $member = NULL, $where = array())
    {
	try
	{
	        /* Permission check? */
	        $usingPerm = (in_array('IPS\Node\Permissions', class_implements(get_called_class())) and $permissionCheck !== NULL);
	        if ($usingPerm)
	        {
	            $member = $member ?: \IPS\Member::loggedIn();
	
	            if ($member->restrict_post) {
	                return FALSE;
	            }
	
	            $bwCreateTopic = \IPS\applicationform\Position::$bitOptions['options']['options']['bw_create_topic'];
	            $where[] = array('((' . \IPS\Db::i()->findInSet('core_permission_index.perm_' . static::$permissionMap[$permissionCheck], $member->groups) . ' OR ' . 'core_permission_index.perm_' . static::$permissionMap[$permissionCheck] . '=? )' . ' AND ' . \IPS\applicationform\Position::$databaseTable . '.' . static::$databaseColumnTargetForum . '=?  AND ' . \IPS\applicationform\Position::$databaseTable . '.' . static::$databaseColumnOptions . '& ? = ?)', '*', $this->_id, $bwCreateTopic, $bwCreateTopic);
	            if (static::$databaseColumnEnabledDisabled)
	            {
	                $where[] = array(static::$databasePrefix . static::$databaseColumnEnabledDisabled . '=1');
	            }
	        }
	
	        /* Select */
	        $select = \IPS\Db::i()->select(static::$databaseColumnId, \IPS\applicationform\Position::$databaseTable, $where);
	        if ($usingPerm)
	        {
	            $select->join('core_permission_index', array("core_permission_index.app=? AND core_permission_index.perm_type=? AND core_permission_index.perm_type_id=" . \IPS\applicationform\Position::$databaseTable . "." . static::$databaseColumnId, \IPS\applicationform\Position::$permApp, \IPS\applicationform\Position::$permType));
	        }
	
	        $obj = NULL;
	        /* Return */
	        if ( $select->count() > 0  )
	        {
	            $obj = \IPS\applicationform\Position::load( $select->first() );
	        }
	        return $obj;
	}
	catch ( \RuntimeException $e )
	{
		if ( method_exists( get_parent_class(), __FUNCTION__ ) )
		{
			return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
		}
		else
		{
			throw $e;
		}
	}
    }

    /**
     * Returns the URL of the application form
     */
    public function getApplicationFormUrl()
    {
	try
	{
        // use read, because read has permission value '2' and apply uses this permission table column 'perm_2'
	        $applicationForm = $this->getFirstAccessibleApplicationForm( 'read' );
	        return $applicationForm !== NULL ? $applicationForm->url() : '';
	}
	catch ( \RuntimeException $e )
	{
		if ( method_exists( get_parent_class(), __FUNCTION__ ) )
		{
			return call_user_func_array( 'parent::' . __FUNCTION__, func_get_args() );
		}
		else
		{
			throw $e;
		}
	}
    }
}
