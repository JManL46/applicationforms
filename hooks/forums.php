//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    exit;
}

class applicationform_hook_forums extends _HOOK_CLASS_
{

    /* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'forumButtons' => 
  array (
    0 => 
    array (
      'selector' => 'li.ipsToolList_primaryAction',
      'type' => 'replace',
      'content' => '<li class=\'ipsToolList_primaryAction\'>
        {{if $forum->can(\'add\') }}
  			{{if $forum->isApplicationFormAssigned() }}
  			    {{if $forum->can(\'apply\') }}
  		            <a class="ipsButton ipsButton_medium ipsButton_important ipsButton_fullWidth" href="{$forum->getApplicationFormUrl()}" title=\'{lang="forum_applicationform_create_desc"}\'>{lang="forum_applicationform_create"}</a>
                {{endif}}
        	{{else}}
              {{if $forum->forums_bitoptions[\'bw_enable_answers\']}}
                  <a class="ipsButton ipsButton_medium ipsButton_important ipsButton_fullWidth" href="{$forum->url()->setQueryString( \'do\', \'add\' )}" title=\'{lang="ask_a_question_desc"}\'>{lang="ask_a_question"}</a>
              {{else}}
                  <a class="ipsButton ipsButton_medium ipsButton_important ipsButton_fullWidth" href="{$forum->url()->setQueryString( \'do\', \'add\' )}" title=\'{lang="start_new_topic_desc"}\'>{lang="start_new_topic"}</a>
              {{endif}}
  			{{endif}}
        {{endif}}
    </li>',
    ),
  ),
  'forumTable' => 
  array (
    0 => 
    array (
      'selector' => 'div.ipsBox[data-tableid=\'topics\'] > form > div.ipsType_center > a.ipsButton.ipsButton_primary.ipsButton_medium',
      'type' => 'replace',
      'content' => '{{if $table->container()->isApplicationFormAssigned() }}
	{{if $table->container()->can(\'apply\') }}
		<a class="ipsButton ipsButton_primary ipsButton_medium" href="{$table->container()->getApplicationFormUrl()}" title=\'{lang="forum_applicationform_create_desc"}\'>{lang="forum_applicationform_create"}</a>
	{{endif}}
{{else}}
<a href=\'{$table->container()->url()->setQueryString( \'do\', \'add\' )}\' class=\'ipsButton ipsButton_primary ipsButton_medium\'>
    {{if $table->container()->forums_bitoptions[\'bw_enable_answers\']}}
    	{lang="submit_first_question"}
    {{else}}
    	{lang="submit_first_topic"}
    {{endif}}
</a>
{{endif}}',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */


}
