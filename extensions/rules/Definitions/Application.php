<?php
/**
 * @brief		Rules extension: Application
 * @package		Rules for IPS Social Suite
 * @since		19 Nov 2017
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\applicationform\extensions\rules\Definitions;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Rules definitions extension: Application
 */
class _Application
{

	/**
	 * @brief	The default option group title to list events, conditions, and actions from this class
	 */
	public $defaultGroup = 'Application';


	public function events()
	{
		$events = array
		(
			'submission_approved' => array
			( 
				/**
				 * Option Group
				 *
				 * Customize the option group heading that this event is listed under in the ACP
				 *
				 * If this setting is omitted, then this option will be listed under the title of the 
				 * $defaultGroup property of this class definition.
				 */
				'group' => 'Application',
				
				/**
				 * Event Arguments
				 *
				 * Define the arguments that are sent to your event trigger
				 */
				'arguments' => array
				( 
					/**
					 * Argument/Variable Names
					 *
					 * Arguments need to be defined in the same order that they are provided
					 * by the event trigger.
					 *
					 * The names you define will be available as PHP variables
					 * when using PHP code in the rules configurations.
					 * eg: $variable1
					 *
					 * Note: since they are used as variable names, you may only
					 * use alphanumerics and underscores in the name.
					 *
					 * The description of the variable should be stored as a language string
					 * in your lang.php with a key in the following format:
					 *
					 * 'applicationform_Application_event_{event_key}_{variable1}' => 'Description of the event variable'					 
					 */
					'application' => array
					(
						/**
						 * Argument Type
						 *
						 * Define the type of this argument as it will be passed to the event
						 * trigger. This allows actions to determine whether this argument
						 * is compatible with the action callback.
						 *
						 * string: this argument will always be a string ( or possibly null )
						 * int: this argument will always be an integer ( or possibly null )
						 * bool: this argument will always be boolean ( or possibly null )
						 * float: this argument will always be a floating point number ( or possibly null )
						 * array: this argument will always be an array ( or possibly null )
						 * object: this argument will always be an object ( or possibly null )
						 * mixed: this argument will contain mixed values ( or possibly null )
						 */
						'argtype' 	=> 'object',
						
						/**
						 * Class Association
						 *
						 * This is used to help rules determine whether this argument can be used 
						 * with conditions/actions that require objects. For example, if your argument
						 * is a member object, you should define it's class as \IPS\Member. This allows 
						 * rules to make it available as an event argument when the operation
						 * specifically needs a member object.
						 *
						 * If your argument will be an object, use the actual class of your object here.
						 * Otherwise, simply omit this setting or set it to NULL.
						 */
						'class'		=> '\IPS\applicationform\Position\Data',
												
						/**
						 * NULLABLE
						 *
						 * Set to TRUE if this argument may be NULL when the event is triggered.
						 * This gives the user the opportunity to set a default value for it when
						 * they create their rule.
						 *
						 * If the argument will always have a value that is not null, you 
						 * can omit this setting or leave it set it to FALSE
						 */
						'nullable'	=> FALSE,
					),
				),
			),
			'submission_denied' => array
			(
				/**
				 * Option Group
				 *
				 * Customize the option group heading that this event is listed under in the ACP
				 *
				 * If this setting is omitted, then this option will be listed under the title of the
				 * $defaultGroup property of this class definition.
				 */
				'group' => 'Application',

				/**
				 * Event Arguments
				 *
				 * Define the arguments that are sent to your event trigger
				 */
				'arguments' => array
				(
					/**
					 * Argument/Variable Names
					 *
					 * Arguments need to be defined in the same order that they are provided
					 * by the event trigger.
					 *
					 * The names you define will be available as PHP variables
					 * when using PHP code in the rules configurations.
					 * eg: $variable1
					 *
					 * Note: since they are used as variable names, you may only
					 * use alphanumerics and underscores in the name.
					 *
					 * The description of the variable should be stored as a language string
					 * in your lang.php with a key in the following format:
					 *
					 * 'applicationform_Application_event_{event_key}_{variable1}' => 'Description of the event variable'
					 */
					'application' => array
					(
						/**
						 * Argument Type
						 *
						 * Define the type of this argument as it will be passed to the event
						 * trigger. This allows actions to determine whether this argument
						 * is compatible with the action callback.
						 *
						 * string: this argument will always be a string ( or possibly null )
						 * int: this argument will always be an integer ( or possibly null )
						 * bool: this argument will always be boolean ( or possibly null )
						 * float: this argument will always be a floating point number ( or possibly null )
						 * array: this argument will always be an array ( or possibly null )
						 * object: this argument will always be an object ( or possibly null )
						 * mixed: this argument will contain mixed values ( or possibly null )
						 */
						'argtype' 	=> 'object',

						/**
						 * Class Association
						 *
						 * This is used to help rules determine whether this argument can be used
						 * with conditions/actions that require objects. For example, if your argument
						 * is a member object, you should define it's class as \IPS\Member. This allows
						 * rules to make it available as an event argument when the operation
						 * specifically needs a member object.
						 *
						 * If your argument will be an object, use the actual class of your object here.
						 * Otherwise, simply omit this setting or set it to NULL.
						 */
						'class'		=> '\IPS\applicationform\Position\Data',

						/**
						 * NULLABLE
						 *
						 * Set to TRUE if this argument may be NULL when the event is triggered.
						 * This gives the user the opportunity to set a default value for it when
						 * they create their rule.
						 *
						 * If the argument will always have a value that is not null, you
						 * can omit this setting or leave it set it to FALSE
						 */
						'nullable'	=> FALSE,
					),

				),
			),
		);
		
		return $events;
	}
	
	/**
	 * Conditional Operations
	 *
	 * You can define your own conditional operations which can be
	 * added to rules as conditions.
	 *
	 * @return 	array		Array of conditions definitions
	 */
	public function conditions()
	{
		return array();
	}

	/**
	 * Triggerable Actions
	 *
	 * @return 	array		Array of action definitions
	 */
	public function actions()
	{
		$actions = array();
		
		return $actions;
	}

	public function checkApplied( $arg1, $values, $arg_map, $operation )
	{
		//TODO
	}
	
}