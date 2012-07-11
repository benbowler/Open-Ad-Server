<?php
require_once 'Sppc/Db/Table/Abstract.php';

class Sppc_Site_LayoutModel extends Sppc_Db_Table_Abstract {
    /**
     * Table name
     * @var string
     */
    protected $_name = 'site_layouts';
    protected $_rowClass = "Sppc_Site_Layout";
    /**
     * Reference map
     * @var array
     */
    protected $_referenceMap = array (
            'Site' => array (
                    'columns' => 'id_site',
                    'refTableClass' => 'Sppc_SiteModel',
                    'refColumns' => 'id_site',
                    'onUpdate' => self::CASCADE,
                    'onDelete' => self::CASCADE ) );


    /**
     * Dependent tables
     * @var array
     */
    protected $_dependentTables = array (
            'Sppc_Site_Layout_ZoneModel',
            'Sppc_Site_Layout_ChannelModel',
            'Sppc_Site_Layout_IntextModel' );

    /**
     * Find layout for site
     *
     * @param Sppc_Db_Table_Row_Abstract $site
     * @return Sppc_Site_Layout
     */
    public function findBySite(Sppc_Db_Table_Row_Abstract $site) {
        $select = $this->select();
        $select->where( 'id_site=?', $site->getId() );
        return $this->fetchRow( $select );
    }
    /**
     * Update site layout
     *
     * @param Sppc_Db_Table_Row_Abstract $site
     * @param string $json
     * @return Sppc_Site_Layout
     */
    public function updateFromJson(Sppc_Db_Table_Row_Abstract $site, $json) {
        $jsonLayout = json_decode( $json );

        if (is_null( $jsonLayout ) || ($jsonLayout === false)) {
            throw new Sppc_Exception( 'Decode json layout error: ' . $json );
        }
        $siteLayout = $this->findBySite( $site );
        if (is_null( $siteLayout )) {
            $siteLayout = $this->createRow();
        }

        $siteLayout->setIdSite( $site->getId() );
        if (isset( $jsonLayout->width )) {
            $siteLayout->setWidth( $jsonLayout->width );
        }
        if (isset( $jsonLayout->height )) {
            $siteLayout->setHeight( $jsonLayout->height );
        }
        if (isset( $jsonLayout->sizesW ) && is_array( $jsonLayout->sizesW )) {
            for ($i = 0; ($i < 3) && ($i < count( $jsonLayout->sizesW )); $i ++) {
                $siteLayout->{'setSizeW' . ($i + 1)}( $jsonLayout->sizesW[$i] );
            }
        }
        if (isset( $jsonLayout->sizesH ) && is_array( $jsonLayout->sizesH )) {
            for ($i = 0; ($i < 3) && ($i < count( $jsonLayout->sizesH )); $i ++) {
                $siteLayout->{'setSizeH' . ($i + 1)}( $jsonLayout->sizesH[$i] );
            }
        }
        $siteLayout->save();

        $siteLayoutZoneModel = new Sppc_Site_Layout_ZoneModel( );
        $siteLayoutZoneModel->delete( array (
                'id_site_layout=?' => $siteLayout->getId() ) );
        if (isset( $jsonLayout->zones ) && is_array( $jsonLayout->zones )) {
            // parse and save new zones:
            foreach ( $jsonLayout->zones as $key => $jsonZone ) {
                $zone = $siteLayoutZoneModel->createRow();
                $zone->setIdSiteLayout( $siteLayout->getId() );

                if (! isset( $jsonZone->id )) {
                    $zone->setIdJsonZone( $siteLayout->getId() .'_' . $key );
                }
                else {
                    $zone->setIdJsonZone( $jsonZone->id );
                }

                if (isset( $jsonZone->colspan )) {
                    $zone->setColspan( $jsonZone->colspan );
                }
                if (isset( $jsonZone->rowspan )) {
                    $zone->setRowspan( $jsonZone->rowspan );
                }
                $zone->setOrder( $key );
                $zone->save();
            }
        }

        // save layout channels:
        $channelModel = new Sppc_ChannelModel( );
        $siteLayoutChannelModel = new Sppc_Site_Layout_ChannelModel( );
        $siteLayoutChannelModel->delete( array (
                'id_site_layout=?' => $siteLayout->getId() ) );
        if (isset( $jsonLayout->channels ) && is_array( $jsonLayout->channels )) {
            foreach ( $jsonLayout->channels as $key => $jsonChannel ) {
				if ((! isset ( $jsonChannel->ad_type )) || ($jsonChannel->ad_type == "search")) {
					continue;
				}
                if (! isset( $jsonChannel->id )) {
                    continue;
                }
                $channel = $channelModel->findObjectById( $jsonChannel->id );
                if (is_null( $channel )) {
                    continue;
                }
                $siteChannel = $siteLayoutChannelModel->createRow();
                $siteChannel->setIdSiteLayout( $siteLayout->getId() );
                $siteChannel->setIdChannel( $channel->getId() );
                if (isset( $jsonChannel->x )) {
                    $siteChannel->setX( $jsonChannel->x );
                }
                if (isset( $jsonChannel->y )) {
                    $siteChannel->setY( $jsonChannel->y );
                }
                if (isset( $jsonChannel->width )) {
                    $siteChannel->setWidth( $jsonChannel->width );
                }
                if (isset( $jsonChannel->height )) {
                    $siteChannel->setHeight( $jsonChannel->height );
                }
                if (isset( $jsonChannel->zone )) {
                    $zone = $siteLayoutZoneModel->findByJsonZone( $siteLayout, $jsonChannel->zone );
                    if (!is_null($zone)) {
                        if ($zone->getIdSiteLayout() == $siteLayout->getId()) {
                            $siteChannel->setIdSiteLayoutZone( $zone->getId() );
                        }
                    }
                }
                $siteChannel->save();
            }
        }

        // In-Text
          if (isset ( $jsonLayout->channels ) && is_array ( $jsonLayout->channels )) {
         // store site search channels:
         try{
            $siteLayoutIntextModel = new Sppc_Site_Layout_IntextModel ( );
            $siteLayoutIntextModel->delete ( array (
               'id_site_layout=?' => $siteLayout->getId () ) );
            foreach ( $jsonLayout->channels as $key => $jsonChannel ) {
               if ((! isset ( $jsonChannel->ad_type )) || ($jsonChannel->ad_type != 'intext')) {
                  continue;
               }
               $siteLayoutIntext = $siteLayoutIntextModel->createRow ();
               $siteLayoutIntext->setIdSiteLayout ( $siteLayout->getId () );
               if (isset ( $jsonChannel->x )) {
                  $siteLayoutIntext->setX ( $jsonChannel->x );
               }
               if (isset ( $jsonChannel->y )) {
                  $siteLayoutIntext->setY ( $jsonChannel->y );
               }
               if (isset ( $jsonChannel->width )) {
                  $siteLayoutIntext->setWidth ( $jsonChannel->width );
               }
               if (isset ( $jsonChannel->height )) {
                  $siteLayoutIntext->setHeight ( $jsonChannel->height );
               }
               if (isset ( $siteLayoutIntext->zone )) {
                  $zone = $siteLayoutZoneModel->findByJsonZone ( $siteLayout, $jsonChannel->zone );
                  if (! is_null ( $zone )) {
                     if ($zone->getIdSiteLayout () == $siteLayout->getId ()) {
                        $siteLayoutIntext->setIdSiteLayoutZone ( $zone->getId () );
                     }
                  }
               }
               
               $siteLayoutIntext->save ();
               
               }
        
          }catch(Exception $e){
             
          }
      }
        return $siteLayout;
    }
}
