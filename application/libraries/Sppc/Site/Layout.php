<?php

require_once 'Sppc/Db/Table/Row/Abstract.php';

class Sppc_Site_Layout extends Sppc_Db_Table_Row_Abstract {
    /**
     * @see Sppc_Db_Table_Row_Abstract::toString()
     *
     * @return string
     */
    public function toString() {
        $data = array (
                'id' => $this->getId(),
                'width' => $this->getWidth(),
                'height' => $this->getHeight(),
                'sizesW' => array (
                        $this->getSizeW1(),
                        $this->getSizeW2(),
                        $this->getSizeW3() ),
                'sizesH' => array (
                        $this->getSizeH1(),
                        $this->getSizeH2(),
                        $this->getSizeH3() ) );

        // zones section:
        $zones = array ();
        $select = $this->select();
        $select->order('order');
        foreach ( $this->findDependentRowset( 'Sppc_Site_Layout_ZoneModel',null,$select ) as $zone ) {
            $zones[] = array (
                    'id' => $zone->getId(),
                    'colspan' => $zone->getColspan(),
                    'rowspan' => $zone->getRowspan() );
        }
        $data['zones'] = $zones;

        $site = $this->findParentRow ( 'Sppc_SiteModel' );
        $siteChannelModel = new Sppc_Site_ChannelModel();
        // channels section:
        $channels = array();
        foreach ($this->findDependentRowset('Sppc_Site_Layout_ChannelModel') as $layoutChannel) {
            /*@var $layoutChannel Zend_Db_Table_Row*/
            // Ugly but work:
            $channel = $layoutChannel->findParentRow('Sppc_ChannelModel');
            $select = $siteChannelModel->select();
            $select->where('id_site=?',$site->getId());
            $select->where('id_channel=?',$channel->getId());
            $select->where('status=?','active');
            $siteChannelRow = $siteChannelModel->fetchRow($select);
            if(is_null($siteChannelRow)) {
                continue;
            }

            $dimension = $channel->findParentRow ( 'Sppc_DimensionModel' );
            $channels[] = array(
                'id'=>$channel->getId(),
                'title'=>$channel->getName(),
                'zone'=>$layoutChannel->getIdSiteLayoutZone(),
                'x'=> $layoutChannel->getX(),
                'y'=> $layoutChannel->getY(),
                'width'=> $layoutChannel->getWidth(),
                'height'=> $layoutChannel->getHeight(),
                'id_dimension' =>  $channel->getIdDimension(),
                'ad_type' => $channel->getAdType(),
                'max_slots_count' => $dimension->getMaxAdSlots()
            );
        }

        $data['channels']=$channels;

        return json_encode( $data );
    }
}