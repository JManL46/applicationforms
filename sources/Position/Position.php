<?php

namespace IPS\applicationform;


use IPS\applicationform\Helper\Notification;
use IPS\Helpers\Form\DateRange;
use IPS\Helpers\Form\Number;
use IPS\Helpers\Form\Text;
use IPS\Helpers\Form\YesNo;
use IPS\Member;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}


class _Position extends \IPS\Node\Model implements
\IPS\Node\Permissions
{
    protected static $multitons;
    public static $databaseTable = 'applicationform_position';
    public static $nodeTitle = 'application_position';

	public static $subnodeClass = 'IPS\applicationform\Position\Field';
	public static $titleLangPrefix = 'applicationform_position_';
	public static $descriptionLangSuffix = '_desc';
	public static $databaseColumnOrder = "position";
	
	protected $_url = NULL;
	public static $seoTitleColumn = 'name_seo';
	public static $urlBase = 'app=applicationform&module=applications&controller=form&id=';
	public static $urlTemplate = 'application_form';

    public static $bitOptions = [
		'options'	=> [
			'options'	=> [
				'bw_open_poll'	=> 1,
				'bw_apply_only_once' => 2,
				'bw_create_topic' => 4,
				'bw_log_data' => 8,				// deprecated, TODO remove in 2.2
				'bw_vertical' => 16,
				'bw_create_topic_reply' => 32,
				'bw_create_pm' => 64,
				'bw_create_post' => 128,

			]
		]
	];


	protected static $restrictions = array(
		'app'		=> 'applicationform',
		'module'	=> 'forms',
		'all' 		=> 'position_manage'
	);

	/**
	 * @brief	[Node] App for permission index
	 */
	public static $permApp = 'applicationform';

	/**
	 * @brief	[Node] Type for permission index
	 */
	public static $permType = 'form';

	/**
	 * @brief	The map of permission columns
	 */
	public static $permissionMap = array(
		'view' 				=> 'view',
		'reply'				=> 2,
		'manage'			=> 3,
	);

	protected $dataToLog = array();

	/**
	 * @brief	[Node] Prefix string that is automatically prepended to permission matrix language strings
	 */
	public static $permissionLangPrefix = 'appformperm_';


	/**
	 * Get SEO name
	 *
	 * @return	string
	 */
	public function get_name_seo()
	{
		if( !$this->_data['name_seo'] )
		{
			$this->name_seo	= \IPS\Http\Url\Friendly::seoTitle( \IPS\Lang::load( \IPS\Lang::defaultLanguage() )->get( 'applicationform_position_' . $this->id ) );
			$this->save();
		}

		return $this->_data['name_seo'] ?: \IPS\Http\Url\Friendly::seoTitle( \IPS\Lang::load( \IPS\Lang::defaultLanguage() )->get( 'applicationform_position_' . $this->id ) );
	}

	protected function get_instructions()
	{
		if ( static::$titleLangPrefix and Member::loggedIn()->language()->checkKeyExists( static::$titleLangPrefix . $this->id . '_instructions') )
		{
			return \IPS\Member::loggedIn()->language()->addToStack( static::$titleLangPrefix . $this->id . '_instructions' );
		}
		return NULL;
	}

	protected function get_submitmsg()
	{
		if ( static::$titleLangPrefix and Member::loggedIn()->language()->checkKeyExists( static::$titleLangPrefix . $this->id . '_submitmsg') )
		{
			return \IPS\Member::loggedIn()->language()->addToStack( static::$titleLangPrefix . $this->id . '_submitmsg' );
		}
		return \IPS\Member::loggedIn()->language()->addToStack("submitted" );
	}



    public function form(&$form)
    {
		$groups = [];
		foreach ( \IPS\Member\Group::groups() as $group )
		{
			$groups[ $group->g_id ] = $group->name;
		}
		unset( $groups[ \IPS\Settings::i()->guest_group ] );

		$form->addTab( 'application_form' );
	    $form->add( new \IPS\Helpers\Form\Translatable( 'position_name', NULL, TRUE, array( 'app' => 'applicationform', 'key' => ( $this->id ? "applicationform_position_{$this->id}" : NULL ) ) ) );

        $form->add( new \IPS\Helpers\Form\Upload( 'position_icon', $this->icon ? \IPS\File::get( 'applicationforms_Icons', $this->icon ) : NULL, FALSE, array( 'image' => TRUE, 'storageExtension' => 'applicationforms_Icons' ), NULL, NULL, NULL, 'position_icon' ) );

	    $form->add( new \IPS\Helpers\Form\Translatable( 'position_description', NULL, FALSE, array(
	    	'app' => 'applicationform',
			'key' => ( $this->id ? "applicationform_position_{$this->id}_desc" : NULL ),
			'editor' => array( 'app' => 'applicationform', 'key' => 'Position',
			'autoSaveKey' => ( $this->id ? "applicationform-position-{$this->id}" : "applicationform-new-position" ),
				'attachIds' => $this->id ? array( $this->id, NULL, 'description' ) : NULL, 'minimize' => 'position_description_placeholder' ) ) ) );

	     $form->add( new \IPS\Helpers\Form\Translatable( 'position_instructions', NULL, FALSE, array( 'app' => 'applicationform', 'key' => ( $this->id ? "applicationform_position_{$this->id}_instructions" : NULL ), 'editor' => array( 'app' => 'applicationform', 'key' => 'Position',
			'autoSaveKey' => ( $this->id ? "applicationform-position-{$this->id}-ins" : "applicationform-new-position-ins" ), 'attachIds' => $this->id ? array( $this->id, NULL, 'instructions' ) : NULL, 'minimize' => 'position_description_instructions' ) ) ) );

	     $form->add( new \IPS\Helpers\Form\Translatable( 'position_submitmsg', NULL, FALSE, array(
	    	'app' => 'applicationform',
			'key' => ( $this->id ? "applicationform_position_{$this->id}_submitmsg" : NULL ),
			'editor' => array( 'app' => 'applicationform', 'key' => 'Position',
			'autoSaveKey' => ( $this->id ? "applicationform-position-{$this->id}-submitmsg" : "applicationform-new-position-submitmsg" ),
				'attachIds' => $this->id ? array( $this->id, NULL, 'submitmsg' ) : NULL, 'minimize' => 'position_description_submitmsg' ) ) ) );

	    $form->add( new \IPS\Helpers\Form\YesNo( 'position_bw_apply_only_once', $this->id ? $this->options['bw_apply_only_once'] : FALSE ) );

	    $formOutputTypes = [
			0 => 'horizontal',
			1 => 'vertical'
		];

	    $form->add( new \IPS\Helpers\Form\Radio( 'position_bw_vertical', $this->options['bw_vertical'] == 0 ? 0:1 , FALSE, ['options' => $formOutputTypes]));
		$form->add( new \IPS\Helpers\Form\Url( 'position_redirect_target', $this->redirect_target , FALSE ) );
		$form->addTab( 'application_benefits' );
		$form->add( new \IPS\Helpers\Form\Select( 'position_primary_group', $this->primary_group ?: '*', FALSE, array( 'options' => $groups, 'unlimited' => '*', 'unlimitedLang' => 'applications_do_not_change', 'unlimitedToggles' => array( 'p_return_primary' ), 'unlimitedToggleOn' => FALSE ) ) );
		$form->add( new \IPS\Helpers\Form\Select( 'position_secondary_group', $this->secondary_group ?: '*', FALSE, array( 'options' => $groups, 'unlimited' => '*', 'unlimitedLang' => 'applications_do_not_change', 'unlimitedToggles' => array( 'p_return_primary' ), 'unlimitedToggleOn' => FALSE ) ) );

		$form->add( new YesNo( 'position_remove_promotion', $this->groupdemotion_time ? TRUE : FALSE, FALSE, ['togglesOn'=> ['position_groupdemotion_time']], NULL, NULL, NULL, 'position_remove_promotion' ) );
		$form->add( new Number( 'position_groupdemotion_time', $this->groupdemotion_time, FALSE, [ ], NULL, NULL, 'days', 'position_groupdemotion_time' ) );

		$form->addTab( 'application_actions' );



		foreach ( \IPS\Application::allExtensions( 'applicationform', 'ApplicationAction', TRUE, NULL, NULL, FALSE ) as $ext )
		{

			$ext::form( $form, $this );
		}

        $form->canSaveAndReload = true;
    }



	public function formatFormValues( $values )
	{

		foreach ( \IPS\Application::allExtensions( 'applicationform', 'ApplicationAction', TRUE, NULL, NULL, FALSE ) as $ext )
		{
			$ext::createFromForm( $values, $this );
		}

		/* Bit options */
		foreach ( array_keys( static::$bitOptions['options']['options'] ) as $k )
		{
			if ( isset( $values[ "position_{$k}" ] ) )
			{
				$this->options[ $k ] = $values[ "position_{$k}" ];
				unset( $values[ "position_{$k}" ] );
			}
		}

		if( isset( $values['position_primary_group'] ) )
		{
			$values['position_primary_group'] = $values['position_primary_group'] == '*' ? 0 : $values['position_primary_group'];
		}

		if( isset( $values['position_secondary_group'] ) )
		{

			$values['position_secondary_group'] = $values['position_secondary_group'] == '*' ? 0 : $values['position_secondary_group'];
		}

		if ( isset( $values['position_target_forum'] ) and is_object( $values['position_target_forum'] ) )
		{
			$values['position_target_forum'] = ( $values['position_target_forum'] ) ? $values['position_target_forum']->id : 0;
		}

		if ( $values['position_remove_promotion'] == 0)
		{
			$values['position_groupdemotion_time'] = 0;
		}
		else
		{
			$values['position_groupdemotion_time'] = isset( $values['position_groupdemotion_time'] ) ? $values['position_groupdemotion_time'] : 0;
		}


		unset( $values['position_remove_promotion'] );





			/* Remove position_ prefix */
    		$_values = $values;
    		$values = array();
    		foreach ( $_values as $k => $v )
    		{
    			if( mb_substr( $k, 0, 9 ) === 'position_' )
    			{
    				$values[ mb_substr( $k, 9 ) ] = $v;
    			}
    			else
    			{
    				$values[ $k ]	= $v;
    			}
    		}

    		if ( !$this->id )
            {
            	$this->save();
            }


		\IPS\File::claimAttachments( 'applicationform-new-position', $this->id, NULL, 'description', TRUE );
		\IPS\File::claimAttachments( 'applicationform-new-position-ins', $this->id, NULL, 'instructions', TRUE );
		\IPS\File::claimAttachments( 'applicationform-new-position-submitmsg', $this->id, NULL, 'submitmsg', TRUE );


            	foreach ( array(
            	    'name' => "applicationform_position_{$this->id}",
            	    'description' => "applicationform_position_{$this->id}_desc",
            	    'instructions' => "applicationform_position_{$this->id}_instructions",
            	    'submitmsg' => "applicationform_position_{$this->id}_submitmsg",
            	    ) as $fieldKey => $langKey )
            	{
            		if ( array_key_exists( $fieldKey, $values ) )
            		{
            			\IPS\Lang::saveCustom( 'applicationform', $langKey, $values[ $fieldKey ] );

            			if ( $fieldKey === 'name' )
            			{
            				$this->name_seo = \IPS\Http\Url\Friendly::seoTitle( $values[ $fieldKey ][ \IPS\Lang::defaultLanguage() ] );
           					$this->save();
           				}
            				unset( $values[ $fieldKey ] );
            			}
            		}


		return $values;
	}


	/**
	 * [Node] Perform actions after saving the form
	 *
	 * @param	array	$values	Values from the form
	 * @return	void
	 */
	public function postSaveForm( $values )
	{
		$this->onSaveAndDelete();
	}

	public function saveForm( $values )
    {
        $oldIcon = \IPS\File::get( 'applicationforms_Icons', $this->icon );

        if ( isset( $values ) AND isset($oldIcon) AND ( !isset($values['icon']) OR $oldIcon->filename !== $values['icon']->filename ))
        {
            try
            {
                $oldIcon->delete();
            }
            catch( \Exception $ex ) { }
        }

        parent::saveForm( $values );
    }

	/**
	 * Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{

		foreach ( \IPS\Application::allExtensions( 'applicationform', 'ApplicationAction', TRUE, NULL, NULL, FALSE ) as $ext )
		{
			$ext::onFormDelete( $this );
		}


		\IPS\Db::i()->delete( 'applicationform_applications', ['position_id=?', $this->id ] );

		\IPS\File::unclaimAttachments( 'applicationform_Position', $this->id );
        try
        {
            \IPS\File::get( 'applicationforms_Icons', $this->icon )->delete();
        }
        catch( \Exception $ex ) { }

		parent::delete();

		foreach ( array(
                    	    'name' => "applicationform_position_{$this->id}",
                    	    'description' => "applicationform_position_{$this->id}_desc",
					  'instructions' => "applicationform_position_{$this->id}_instructions",
					  'submitmsg' => "applicationform_position_{$this->id}_submitmsg",
                    	    ) as $fieldKey => $langKey )
        {
                {
                    \IPS\Lang::deleteCustom( 'applicationform', $langKey );
                }
	    }

		/* remove menu tab */
		$items = array();
		foreach( \IPS\Db::i()->select( '*', 'core_menu', array( 'app=? AND extension=?', 'applicationform', 'Node' ) ) as $item )
		{
			$json = json_decode( $item['config'], TRUE );

			if ( isset( $json['applicationsforms_position_id'] ) )
			{
				if ( $json['applicationsforms_position_id'] == $this->id )
				{
					$items[] = $item['id'];
				}
			}
		}

		if ( count( $items ) )
		{
			\IPS\Db::i()->delete( 'core_menu', \IPS\Db::i()->in( 'id', $items ) );
			unset( \IPS\Data\Store::i()->frontNavigation );
		}

		$this->onSaveAndDelete();
	}

	protected function onSaveAndDelete()
	{
		unset( \IPS\Data\Store::i()->frontNavigation );
	}

    public function __clone()
	{
		if ( $this->skipCloneDuplication === TRUE )
		{
			return;
		}

        $oldIcon = $this->icon;
		$oldId = $this->id;
		parent::__clone();

		foreach ( array(
                            	    'name' => "applicationform_position_{$this->id}",
                            	    'description' => "applicationform_position_{$this->id}_desc",
					  				'instructions' => "applicationform_position_{$this->id}_instructions",
					  				'submitmsg' => "applicationform_position_{$this->id}_submitmsg",
                            	    ) as $fieldKey => $langKey )
                            	{
            {
                $oldLangKey = str_replace( $this->id, $oldId, $langKey );
                \IPS\Lang::saveCustom( 'applicationform', $langKey, iterator_to_array( \IPS\Db::i()->select( 'word_custom, lang_id', 'core_sys_lang_words', array( 'word_key=?', $oldLangKey ) )->setKeyField( 'lang_id' )->setValueField('word_custom') ) );
            }
	    }

        if ( $oldIcon )
        {
            try
            {
                $icon = \IPS\File::get( 'applicationforms_Icons', $oldIcon );
                $newIcon = \IPS\File::create( 'applicationforms_Icons', $icon->originalFilename, $icon->contents() );
                $this->icon = (string) $newIcon;
            }
            catch ( \Exception $e )
            {
                $this->icon = NULL;
            }

            $this->save();
        }
	    $this->onSaveAndDelete();
    }

	public function get_form()
	{
		$form = new \IPS\Helpers\Form();
		if ( $this->options['bw_vertical'] )
		{
			$form->class = 'ipsForm_vertical';
		}

		foreach ( $this->fields AS $field )
		{
			$form->add( $field );
		}

		return $form;
	}

	public static $fieldsLookup = [];

	/**
	 * @param $values
	 * @return array
	 */
	public function prepareValues ( $values )
	{
		$_values = $values;
		$values = [];

		foreach ( $_values as $id => $data )
		{
			$id = explode('_', $id);
			$field = \IPS\applicationform\Position\Field::load( $id[1] );
			$helper = $field->buildHelper();
			$helperClass = get_class($helper);
			$readableValue = $field->displayValue( $helper->stringValue($data) );

			switch ($helperClass )
			{
				case 'IPS\Helpers\Form\Member':
					$readableValue = \IPS\Member::load( $readableValue )->name;
					break;
				case 'IPS\Helpers\Form\Date':
				    if (is_int( $readableValue ) )
                    {
                        $ts = \IPS\DateTime::ts( $readableValue) ->fullYearLocaleDate();
                        $readableValue = (string) $ts;
                    }

					break;
			}
			static::$fieldsLookup[$field->_title] =  $id[1];
			$values[ $field->_title ] = $readableValue;
		}

		return $values;
	}

	public function handleForm( array $values )
	{
		foreach ( \IPS\Application::allExtensions( 'applicationform', 'ApplicationAction', TRUE, NULL, NULL, FALSE ) as $ext )
		{
			$ext::onSubmit( $this, $values );
		}

		/* Log Data */
		$id = \IPS\Db::i()->insert( 'applicationform_applications', $this->dataToLog );

		/* Send Notification */
		\IPS\applicationform\Helper\Notification::sendNotificationForSubmission( $id );


		\IPS\applicationform\Application::fireEvent( 'applicationform.application.submitted', $this );
	}


	public function prepareDataForSave($values )
	{
		$_values = $values;
		$values = [];

		foreach ( $_values as $id => $data )
		{
			$id = explode('_', $id);
			$field = \IPS\applicationform\Position\Field::load( $id[1] );
			$helper = $field->buildHelper();
			$readableValue = $helper->stringValue($data);
			if ( $field->type === 'Editor' )
			{
				$field->claimAttachments( $id[1] );
			}

			$values[$id[1]] = $readableValue;
		}

		return $values;
	}

    /**
     * @return array
     */
	public function get_fields()
	{
		$return = [];
		foreach ( $this->children() as $field )
		{
			$return[$field->id] = $field->buildHelper( $field->value );
		}

		return $return;
	}

	public static function topicHasApplication( \IPS\forums\Topic $topic )
	{
		try
		{
			$pid= \IPS\Db::i()->select('position_id', 'applicationform_applications', ['topic_id=?', $topic->tid] )->first();
			return static::load( $pid );
		}
		catch ( \UnderflowException $e )
		{
			return FALSE;
		}
	}

	public function getButtons($url, $subnode = FALSE)
	{
		if ( $this->hasChildren() )
		{
			$myButton['view'] = array(
				'icon'	=> 'eye ',
				'title'	=> 'view',
				'link'	=> $url->setQueryString( ['do' => 'applications', 'id' => $this->_id ] ),
			);
			return array_merge( $myButton , parent::getButtons($url, $subnode) );
		}

		return parent::getButtons($url, $subnode);
	}

	public function can( $permission, $member=NULL )
	{
		if ( !$this->hasChildren() )
		{
			return FALSE;
		}
		return parent::can($permission, $member);
	}

	/**
	 * @return bool
	 *
	 */
	public function canApply( &$errorReason = NULL )
	{
		if ( $this->options[ 'bw_apply_only_once'] AND $this->alreadyApplied() )
		{
			$errorReason ='app_already_applied';
			return FALSE;
		}

		if ( !$this->can('reply') )
		{
			$errorReason ='app_no_reply_perm';
			return FALSE;
		}

		return $this->can('view');
	}

	public function modcpUrl()
	{
		return \IPS\Http\Url::internal( "app=core&module=modcp&controller=modcp&tab=application_approval&action=viewApplications&id={$this->id}", 'front' );
	}

	public function get_openApplications()
	{
		return  \IPS\Db::i()->select( 'count(*)', 'applicationform_applications', ['position_id=? AND approved=0',  $this->_id ] )->first();
	}

	public function get__badge()
	{
		if ( $this->hasChildren() === FALSE )
		{
			return array(
				0	=> 'ipsBadge ipsBadge_negative',
				1	=> 'application_not_usable',
			);
		}
	}

	public function alreadyApplied()
	{
		try
		{
			$count = \IPS\Db::i()->select( 'count(*)', 'applicationform_applications', ['member_id=? AND position_id=?', \IPS\Member::loggedIn()->member_id , $this->_id ] )->first();
			if ( $count < 1)
			{
				return FALSE;
			}
			return TRUE;
		}
		catch ( \UnderflowException $e )  {
			return FALSE;
		}
	}



	public function addDataToLog( $name, $value )
	{
		if ( !isset($this->dataToLog[$name]) )
		{
			$this->dataToLog[$name] = $value;
		}
	}

}






class _AlreadyAppliedException extends \LogicException
{

}