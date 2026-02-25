<?php
/**
 * @brief		File Storage Extension: Icons
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	Application Forms
 * @since		07 Aug 2018
 */

namespace IPS\applicationform\extensions\core\FileStorage;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * File Storage Extension: Icons
 */
class _Icons
{
	/**
	 * Count stored files
	 *
	 * @return	int
	 */
	public function count()
	{
		return \IPS\Db::i()->select( 'COUNT(*)', 'applicationform_position', 'icon IS NOT NULL' )->first();
	}
	
	/**
	 * Move stored files
	 *
	 * @param	int			$offset					This will be sent starting with 0, increasing to get all files stored by this extension
	 * @param	int			$storageConfiguration	New storage configuration ID
	 * @param	int|NULL	$oldConfiguration		Old storage configuration ID
	 * @throws	\UnderflowException					When file record doesn't exist. Indicating there are no more files to move
	 * @return	void|int							An offset integer to use on the next cycle, or nothing
	 */
	public function move( $offset, $storageConfiguration, $oldConfiguration=NULL )
	{
        $applicationForm = \IPS\applicationform\Position::load( \IPS\Db::i()->select( '*', 'applicationform_position', 'icon IS NOT NULL', 'id', array( $offset, 1 ) )->first() );

		try
        {
            $applicationForm->icon = \IPS\File::get( $oldConfiguration ?: 'applicationform_Icons', $applicationForm->icon )->move( $storageConfiguration );
            $applicationForm->save();
        }
        catch( \Exception $e )
        {
            /* Any issues are logged */
        }
	}

	/**
	 * Check if a file is valid
	 *
	 * @param	string	$file		The file path to check
	 * @return	bool
	 */
	public function isValidFile( $file )
	{
        try
        {
            \IPS\Db::i()->select( 'id', 'applicationform_position', array( 'icon=?', $file ) )->first();
            return TRUE;
        }
        catch ( \UnderflowException $e )
        {
            return FALSE;
        }
	}

	/**
	 * Delete all stored files
	 *
	 * @return	void
	 */
	public function delete()
	{
        foreach( \IPS\Db::i()->select( '*', 'applicationform_position', "icon IS NOT NULL" ) as $application )
        {
            try
            {
                \IPS\File::get( 'applicationforms_Icons', $application['icon'] )->delete();
            }
            catch( \Exception $e ){}
        }
	}
}