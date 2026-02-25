<?php


namespace IPS\applicationform\modules\admin\forms;

/* To prevent PHP errors (extending class does not exist) revealing path */
use IPS\Member;
use IPS\Output;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
    header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * position
 */
class _position extends \IPS\Node\Controller
{
    /**
     * Node Class
     * @see \IPS\applicationform\Position
     */
    protected $nodeClass = 'IPS\applicationform\Position';

    /**
     * Execute
     *
     * @return	void
     */
    public function execute()
    {
        \IPS\Dispatcher::i()->checkAcpPermission( 'position_manage' );
        parent::execute();
    }

    protected function applications()
    {
        $nodeClass = $this->nodeClass;
        try
        {
            $node = $nodeClass::load( \IPS\Request::i()->id );
        }
        catch( \OutOfRangeException $e )
        {
            \IPS\Output::i()->error( 'node_error', '2S101/P', 404, '' );
        }

        $where = array( 'position_id=?', $node->_id );


        $table = new \IPS\Helpers\Table\Db( 'applicationform_applications', $this->url->setQueryString('do', 'applications')->setQueryString('id', $node->_id), $where );
		$table->limit = 1;
        /* Columns we need */
        $table->include = array( 'member_id', 'approved', 'topic_id', );
        $table->langPrefix = 'applications_';

        $table->mainColumn = 'name';
        $table->noSort	= array( 'photo' );

        /* Custom parsers */
        $table->parsers = array(
            'member_id' => function( $val, $row )
            {
                $member = \IPS\Member::load( $val );
				return \IPS\Theme::i()->getTemplate( 'global', 'core', 'global' )->basicUrl( $member->acpUrl(), TRUE, $member->name );
            },
            'topic_id' => function( $val, $row )
            {
            	try
				{
					$topic = \IPS\forums\Topic::load( $row['topic_id']);
					return \IPS\Theme::i()->getTemplate( 'global', 'core', 'global' )->basicUrl( $topic->url(), TRUE,  Member::loggedIn()->language()->addToStack( 'topic') );
				}
				catch ( \OutOfRangeException $e )
				{
					return \IPS\Member::loggedIn()->language()->addToStack( 'deleted' );
				}

            },
            'approved' => function ( $val, $row )
            {
                if ( $val)
                {
                    return \IPS\Member::loggedIn()->language()->addToStack( 'yes' );
                }
            },
            'votes' => function ( $val, $row )
            {
            	try
				{
					$topic = \IPS\forums\Topic::load( $row['topic_id']);
					$poll = $topic->getPoll();
					$votes = iterator_to_array($poll->getVotes());

					return $votes;
				}
				catch ( \OutOfRangeException $e )
				{
					return \IPS\Member::loggedIn()->language()->addToStack( 'deleted' );
				}
            }
        );

        /* Individual member actions */
        $table->rowButtons = function( $row ) use ( $node )
        {
            $member = \IPS\Member::load( $row['member_id'] );

            $return = array();

            if ( $node->primary_group OR $node->secondary_group )
            {
                if ( !$row['approved'])
                {
                    $return['apply'] = array(
                        'icon'		=> 'check-circle',
                        'title'		=> 'approve',
                        'link'		=> \IPS\Http\Url::internal( 'app=applicationform&module=forms&controller=position&do=approve&id=' . $row['id'])
                    );
                }
            }
            $return['edit'] = array(
                'icon'		=> 'pencil',
                'title'		=> 'edit',
                'link'		=> \IPS\Http\Url::internal( 'app=core&module=members&controller=members&do=edit&id=' . $member->member_id, 'admin' )
            );

            $return['contact'] = array(
                'icon'		=> 'envelope',
                'title'		=> 'message',
                'target' => 'blank',
                'link'		=> \IPS\Http\Url::internal( 'app=core&module=messaging&controller=messenger&do=compose&to=' . $member->member_id, 'front', 'messenger_compose', $member->members_seo_name )
            );

            return $return;
        };

        \IPS\Output::i()->title = $node->_title;
        \IPS\Output::i()->output = $table;
    }

    protected function approve()
    {
        $id = \IPS\Request::i()->id;
        try
        {
			$data = \IPS\applicationform\Position\Data::load( $id );
            $app = \IPS\applicationform\Position::load( $data->position_id );
            $data->changeState( \IPS\applicationform\Position\Data::STATUS_APPROVED );
        }
        catch ( \UnderflowException $e )
        {
            die('error');
        }

        \IPS\Output::i()->redirect( \IPS\Http\Url::internal('app=applicationform&module=forms&controller=position&do=applications&id=' . $app->id ), 'approved' );
    }

}