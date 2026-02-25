<?php
/**
 * @package		Forms
 * @author		<a href='http://www.devfuse.com'>DevFuse</a>
 * @copyright	(c) 2015 DevFuse
 */

namespace IPS\applicationform\Position;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Package
 */
class _Field extends \IPS\CustomField
{

	/**
	 * @brief	[CustomField] Upload storage extension
	 */
	protected static $uploadStorageExtension = 'applicationform_Fields';

    /**
     * @brief	[Node] Order Database Column
     */
    public static $databaseColumnOrder = 'position';
    /**
     * @brief	[ActiveRecord] Multiton Store
     */
    protected static $multitons;

    public static $parentNodeColumnId = 'application_id';

    /**
     * @brief	[ActiveRecord] Database Prefix
     */
    public static $databasePrefix = '';
    /**
     * @brief	[ActiveRecord] Database Table
     */
    public static $databaseTable = 'applicationform_fields';

    public static $langKey = 'applicationformfield';
    /**
     * @brief	[CustomField] Editor Options
     */
    public static $editorOptions = array( 'app' => 'applicationform', 'key' => 'Application' );

    /**
     * @brief	[Node] Parent Node Class
     */
    public static $parentNodeClass = 'IPS\applicationform\Position';

    public static $databaseColumnMap = array(
        'content'  => 'options',
        'not_null' => 'required',
        'group_id' => 'application_id',
    );


    /**
     * [Node] Format form values from add/edit form for save
     *
     * @param	array	$values	Values from the form
     * @return	array
     */
    public function formatFormValues( $values )
    {

        if ( !$this->id )
        {
            $this->save();
        }

        if ( isset($values['pf_group_id']  ))
        {
            $values['application_id'] = $values['pf_group_id']->_id;
            unset( $values['pf_group_id'] );
        }

		if ( isset( $values['pf_allow_attachments'] ) )
		{
			$values['allow_attachments']	= $values['pf_allow_attachments'];

		}
		unset( $values['pf_allow_attachments'] );

		if ( isset( $values['search_type']) )
		{
			unset($values['search_type']);
		}

        $values = parent::formatFormValues( $values );


		return $values;
    }

    public function form(&$form)
    {
        parent::form( $form );

		unset( $form->elements[''][1] );
		unset( $form->elements['']['pf_search_type'] );
		unset( $form->elements['']['pf_format'] );
		unset( $form->elements['']['pf_search_type_on_off'] );
	#	unset( $form->elements['']['pf_allow_attachments'] );
    }

}