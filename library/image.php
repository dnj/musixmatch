<?php
namespace packages\musixmatch;
use \packages\base\IO\file;
class image{
	protected $api;
	protected $id;
	protected $sizes = array();
	protected $selectedSize;
	public function __construct(api $api){
		$this->api = $api;
	}
	public function addSize(string $url, int $width, int $height){
		$this->sizes[] = array(
			'url' => $url,
			'width' => $width,
			'height' => $height
		);
	}
	public function fromAPI(array $data): image{
		$this->id = isset($data['image_id']) ? $data['image_id'] : null;
		if(isset($data['image_format_list']) and is_array($data['image_format_list'])){
			foreach($data['image_format_list'] as $formatContainer){
				$format = $formatContainer['image_format'];
				$this->addSize($format['image_url'], $format['width'], $format['height']);
			}
		}
		return $this;
	}
	public function size($size, int $height = 0){
		$sizes = $this->sizes;
		usort($sizes, function($a, $b){
			return ($a['width'] * $a['height']) - ($b['width'] * $b['height']);
		});
		if($size == 'small'){
			$this->selectedSize= $sizes[0];
		}elseif($size == 'big'){
			$this->selectedSize= $sizes[count($sizes) - 1];
		}elseif(is_numeric($size) and $height > 0){
			foreach($sizes as $image){
				if($image['width'] == $size and $image['height'] == $height){
					$this->selectedSize = $image;
					break;
				}
			}
		}elseif(is_array($size)){
			foreach($size as $requestedSize){
				foreach($sizes as $image){
					if($image['width'] == $requestedSize[0] and $image['height'] == $requestedSize[1]){
						$this->selectedSize = $image;
						break 2;
					}
				}
			}
		}
		return $this;
	}
	public function get():file{
		if(!$this->selectedSize){
			throw new NoSizeSelectedException();
		}
		$tmpFile = new file\tmp();
		$this->api->getHttpClient()->get($this->selectedSize['url'], array(
			'save_as' => $tmpFile
		));
		return $tmpFile;
	}
	public function storeAs(file $file){
		return $this->get()->copyTo($file);
	}

	public function __set(string $key, $value){
		if(!property_exists($this, $key) or $key != 'id'){
			throw new Exception("{$key} is invalid property");
		}
		$this->$key = $value;
	}
	public function __get(string $key){
		if(isset($this->$key)){
			return $this->$key;
		}
		return null;
	}
}