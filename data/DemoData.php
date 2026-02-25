<?php


$application = new \IPS\applicationform\Position();
$application->save();


foreach ( array(
			  'name' => "applicationform_position_{$application->id}",
			  'description' => "applicationform_position_{$application->id}_desc",
			  'instructions' => "applicationform_position_{$application->id}_instructions",
			  'submitmsg' => "applicationform_position_{$application->id}_submitmsg",
		  ) as $fieldKey => $langKey )
{
	\IPS\Lang::saveCustom( 'applicationform', $langKey, "TEST" );

	if ( $fieldKey === 'name' )
	{
		$application->name_seo = \IPS\Http\Url\Friendly::seoTitle( "test" );
		$application->save();
	}
}

$field = new \IPS\applicationform\Position\Field();