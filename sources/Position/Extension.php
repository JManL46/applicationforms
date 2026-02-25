<?php

namespace IPS\applicationform\Position;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}


abstract class _Extension
{

	static $fields = NULL;

	public static function getFieldsDescriptionForTitleField( \IPS\applicationform\Position $position  )
	{
		$fields = $position->fields;

		$desc = 'Replaceable placeholders:<br>';
		$desc.= 'title: '.  ' - %1$s <br>';
		$desc .= 'membername: '.  ' - %2$s <br>';		//TODO langstrings	OR replace this with global replacable tags from editor
		foreach ( $fields as $id => $field )
		{
			/* Not usable fields have to be stripped here */
			if ( $field instanceof \IPS\Helpers\Form\Upload )
			{
				//Do we need here something?
			}
			else
			{
				$key = 'applicationformfield_' . $id;

				$name = \IPS\Member::loggedIn()->language()->addToStack( $key);
				$desc .=   $name .  ' - [' . $key . ']  <br>';
			}
		}


		return $desc;
	}

	//TODO
	public static function getEditorTagsToReplace( \IPS\applicationform\Position $position )
	{
		$fields = $position->fields;
		foreach ( $fields as $id => $field )
		{

		}
	}

	public static function replaceTags( \IPS\applicationform\Position $position )
	{
		$fields = $position->fields;
		foreach ( $fields as $id => $field )
		{

		}

	}


	public static function onEnd( \IPS\applicationform\Position\Data $data )
	{

	}
}