<?php

namespace IPS\applicationform;

/* To prevent PHP errors (extending class does not exist) revealing path */
use IPS\Node\Model;
use IPS\Patterns\ActiveRecord;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}


interface Events
{
	CONST SUBMISSION_DENIED = "applicationform.application.denied";
	CONST SUBMISSION_APPROVED = "applicationform.application.approved";
	CONST SUBMISSION_END = "applicationform.application.end";
	CONST SUBMISSION_SUBMITTED = "applicationform.application.submitted";


}