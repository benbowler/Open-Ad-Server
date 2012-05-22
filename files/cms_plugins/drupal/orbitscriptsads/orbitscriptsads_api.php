<?php
class OrbitAdsApi {
	function execute($task = '') {
		if (empty($task)) $task=$_GET['task'];
		switch ($task) {
			case 'getChannelsHtml':
				return $this->getChannelsHtml();
				break;
			case 'getPalettesHtml':
				return $this->getPalettesHtml();
				break;
		}
	}
	
	function redirect() {
		exit;
	}
	
	function testconnect() {
		$data=$this->request('get_sites_channels');
		variable_set('orbitscriptsads_errors', $this->errors);
		return $this->errors; 
	}
	
	function getChannelsHtml() {
		$data=$this->request('get_sites_channels');
		variable_set('orbitscriptsads_errors', $this->errors);
	    if (!$data) return false;
	    variable_set('orbitscriptsads_channels', $data);
		unset($data['userid']);
        foreach ($data as $siteId => $site) {
        	foreach ($site['channels'] as $channelId => $channel) {
        		$response[$site['name']][$channelId]=$channel['name'];
        	}
        }

		return $response;
	}
	
	function getPalettesHtml() {
        $data=$this->request('get_palettes');
        if (!$data) return false;	
        $this->data['palettes']=$data;
        foreach ($data as $id => $name) {
        		$response[$id] = $name;
        }
		return $response;
	}
	
	private function setupUrl() {
		$apikey = variable_get('orbitscriptsads_api_key', ORBITSCRIPTSADS_API_KEY_DEFAULT);
	    $baseurl = variable_get('orbitscriptsads_base_url', ORBITSCRIPTSADS_BASE_URL_DEFAULT);
	    $siteid = variable_get('orbitscriptsads_site_id', ORBITSCRIPTSADS_SITE_ID_DEFAULT);
		$this->siteurl = $baseurl;
		
		$url = $baseurl.'/xmlapi?apiKey='.$apikey;
		if (!empty($siteid)) $url .= '&siteId='.$siteid ;
		return $url.'&action=';
	}
	
	private function request($action) {
		$url=$this->setupUrl();
		$url.=$action;
		$xml = @simplexml_load_file($url);
		if (empty($xml)) {
			$this->errors=array('code' => -1,
						'msg' => 'Empty response'
			);
			return false;
		} else {
			$this->errors=array('code' => strval($xml->response->errors->error[0]->code),
			            'msg' =>  strval($xml->response->errors->error[0]->description)
			);
			if ($this->errors['code'] != 0)	return false;
		} 
		if ($action == 'get_sites_channels') {
			$result['userid']=strval($xml->response->data->sites->userid);
			foreach ($xml->response->data->sites->site as $site) {
				$result[strval($site->id)] = array ('name' => strval($site->name),
										'channels' => '');
				foreach ($site->channels->channel as $channel) {
					$result[strval($site->id)]['channels'][strval($channel->id)]=array (
						'name' => strval($channel->name),
						'dimension' => strval($channel->dimension),
					    'width' => strval($channel->width),
						'height' => strval($channel->height),
						'siteurl' => strval($this->siteurl)
						);
				}
			}
		} elseif($action == 'get_palettes') {
			foreach ($xml->response->data->palettes->palette as $palette) {
				$result[strval($palette->id)]=strval($palette->name);
			}
		}
		return $result;
	}

}
