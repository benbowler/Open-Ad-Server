<?php
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Orbit API Controller
 */
class OrbitscriptsControllerOrbitscriptsApi extends OrbitscriptsController {
	
	function execute($task = '') {
		if (empty($task)) $task=$_GET['task'];
		switch ($task) {
			case 'getChannelsHtml':
				if (!$this->getChannelsHtml()) return false;
				break;
			case 'getPalettesHtml':
				if (!$this->getPalettesHtml()) return false;
				break;
		}
		$model = $this->getModel('orbitscriptsads');
		
		foreach ($this->data as $name=>$value) {
			$model->setParam($name,$value);
		}
	}
	
	function redirect() {
		exit;
	}
	
	function testconnect() {
		$data=$this->request('get_sites_channels');
		return $this->errors; 
	}
	
	function getChannelsHtml() {
		$data=$this->request('get_sites_channels');
		if (!$data) return false;
		$this->data['channels']=serialize($data);
		unset($data['userid']);
		$response = '<select id="channel" name="channel">';
        foreach ($data as $siteId => $site) {
        	foreach ($site['channels'] as $channelId => $channel) {
        		$response .='<option value="'.$channelId.'">'.$channel['name'].'</option>';
        	}
        }
		$response .= '</select>';
		echo $response;
		return true;
	}
	
	function getPalettesHtml() {
        $data=$this->request('get_palettes');
        if (!$data) return false;
        $this->data['palettes']=serialize($data);
		$response = '<select id="palette" name="palette">';
        foreach ($data as $id => $name) {
        		$response .='<option value="'.$id.'">'.$name.'</option>';
        }
		$response .= '</select>';
		echo $response;
		return true;
	}
	
	private function setupUrl() {
		$params = &JComponentHelper::getParams( 'com_orbitscriptsads' );
		$apikey = $params->get('apikey');
		$baseurl = $params->get('siteurl');
		$this->siteurl = $baseurl;
		$siteid = $params->get('siteid');
		$url = $baseurl.'/xmlapi?apiKey='.$apikey;
		if (!empty($siteid)) $url .= '&siteId='.$siteid ;
		
		return $url.'&action=';
	}
	
	private function request($action) {
		$url=$this->setupUrl();
		$url.=$action;
		$xml = @file_get_contents($url);
		if (empty($xml)) {
			$this->errors=array('code' => -1,
						'msg' => 'Empty response'
			);
			return false;
		} else {
			$xml = $this->parseXml($xml);
			$this->errors=array('code' => $xml->document->response[0]->errors[0]->error[0]->code[0]->data(),
			            'msg' =>  $xml->document->response[0]->errors[0]->error[0]->description[0]->data()
			);
			if ($this->errors['code'] != 0)	return false;
		} 
		if ($action == 'get_sites_channels') {
			$result['userid']=$xml->document->response[0]->data[0]->sites[0]->userid[0]->data();
			$sites = $xml->document->response[0]->data[0]->sites[0]->children();
			unset($sites[0]);
			foreach ($sites as $site) {
				$result[$site->id[0]->data()] = array ('name' => $site->name[0]->data(),
										'channels' => '');
				foreach ($site->channels[0]->children() as $channel) {
					$result[$site->id[0]->data()]['channels'][$channel->id[0]->data()]=array (
						'name' => $channel->name[0]->data(),
						'dimension' => $channel->dimension[0]->data(),
					    'width' => $channel->width[0]->data(),
						'height' => $channel->height[0]->data(),
						'siteurl' => $this->siteurl
						);
				}
			}
		} elseif($action == 'get_palettes') {
			foreach ($xml->document->response[0]->data[0]->palettes[0]->children() as $palette) {
				$result[$palette->id[0]->data()]=$palette->name[0]->data();
			}
		}
		return $result;
	}
	
	private function parseXml($xml) {
		$res = new JSimpleXML;
		$res->loadString($xml);
		return $res;
	}
}