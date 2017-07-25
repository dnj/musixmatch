<?php
namespace packages\musixmatch;
use \Exception;
use \packages\base\options;
use \packages\base\http;
use \packages\base\json;
class api{
	private $options = array(
		'gateway' => 'https://api.musixmatch.com/ws/1.1/',
		'ssl_verify' => true,
		'signature' => false,
		'apikey' => 'c0f1452a922e366033db671828693d9c'
	);
	private $client;
	public function __construct(array $options = array()){
		$dbOptions = options::get('packages.musixmatch.api');
		if(!is_array($dbOptions)){
			$dbOptions = array();
		}
		$this->options = array_replace($this->options, $dbOptions, $options);
	}
	public function sendRequest(string $path, array $parameters = array()){
		$parameters['format'] = 'json';
		foreach(['app_id', 'usertoken', 'guid', 'apikey'] as $key){
			if(isset($this->options[$key]) and $this->options[$key]){
				$parameters[$key] = $this->options[$key];
			}
		}
		if(isset($this->options['signature']['secret']) and $this->options['signature']['secret']){
			$parameters = $this->signature($path, $parameters);
		}
		$response = $this->getHttpClient()->get($path, array(
			'query' => $parameters
		));
		$result = json\decode($response->getBody());
		if(!$result){
			throw new Exception('Json Parse');
		}
		if(isset($result['message']['body'])){
			return $result['message']['body'];
		}elseif(isset($result['message']['header']['status_code'])){
			return $result['message']['header']['status_code'];
		}
		return false;
	}
	public function artist(){
		return new artist($this);
	}
	public function track(){
		return new track($this);
	}
	public function album(){
		return new album($this);
	}
	public function getHttpClient(): http\client{
		if(!$this->client){
			$httpOptions = array(
				'base_uri' => $this->options['gateway'],
				'ssl_verify' => $this->options['ssl_verify']
			);
			if(isset($this->options['proxy']) and $this->options['proxy']){
				$httpOptions['proxy'] = $this->options['proxy'];
			}
			$this->client = new http\client($httpOptions);
		}
		return $this->client;
	}
	private function signature(string $path, array $parameters = array()):array{
		$url = $this->options['gateway'].$path.'?'.http_build_query($parameters);
		$url .= date('Ymd');
		$parameters['signature'] = base64_encode(hash_hmac('sha1', $url, $this->options['signature']['secret'], true));
		$parameters['signature_protocol'] = 'sha1';
		return $parameters;
	}
}