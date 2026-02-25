<?php

namespace IPS\applicationform;


use IPS\Node\Model;

if (!defined('\IPS\SUITE_UNIQUE_KEY'))
{
	header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
	exit;
}


abstract class _FormType extends  Model
{

	public static function loadByType( $permissionCheck = 'view', $member = NULL )
	{
		$class = get_called_class();
		return static::roots( $permissionCheck, $member, array( 'formclass=?',  $class) );
	}


	abstract function getName();

	function getTypeSpecificExtensionMethods()
	{
		return [];
	}

	public static function isSpecificTopic( \IPS\forums\Topic $topicId )
	{
		return \IPS\Db::i()->select( 'count(*)', 'applicationform_applications', array(
			'topic_id=?', $topicId->tid ) )->first() > 0;
	}
}