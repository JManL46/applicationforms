//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class applicationform_hook_topics extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'topic' => 
  array (
    0 => 
    array (
      'selector' => '#elClubContainer > div.ipsClearfix > ul.ipsToolList.ipsToolList_horizontal.ipsClearfix.ipsSpacer_both > li.ipsResponsive_hidePhone',
      'type' => 'replace',
      'content' => '<li class=\'ipsResponsive_hidePhone\'>
	{{if $topic->container()->isApplicationFormAssigned() }}
		{{if $topic->container()->can(\'apply\') }}
			<a class="ipsButton ipsButton_link ipsButton_medium ipsButton_fullWidth" href="{$topic->container()->getApplicationFormUrl()}" title=\'{lang="forum_applicationform_create_desc"}\'>{lang="forum_applicationform_create"}</a>
		{{endif}}
	{{else}}
   		{{if $topic->container()->forums_bitoptions[\'bw_enable_answers\']}}
  			<a href="{$topic->container()->url()->setQueryString( \'do\', \'add\' )}" class=\'ipsButton ipsButton_link ipsButton_medium ipsButton_fullWidth\' title=\'{lang="ask_a_question_desc"}\'>{lang="ask_a_question"}</a>
  		{{else}}
  			<a href="{$topic->container()->url()->setQueryString( \'do\', \'add\' )}" class=\'ipsButton ipsButton_link ipsButton_medium ipsButton_fullWidth\' title=\'{lang="start_new_topic_desc"}\'>{lang="start_new_topic"}</a>
  		{{endif}}
  {{endif}}
</li>',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */


}
