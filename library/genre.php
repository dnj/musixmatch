<?php
namespace packages\musixmatch;
use \Exception;
class genre{
	protected $api;
	protected $id;
	protected $name;
	protected $fullName;
	protected $parent_id;
	public function __construct(api $api){
		$this->api = $api;
	}
	
	public function fromAPI(array $data){
		$this->id = isset($data['music_genre_id']) ? $data['music_genre_id'] : null;
		$this->name = isset($data['music_genre_name']) ? $data['music_genre_name'] : null;
		$this->fullName = isset($data['music_genre_name_extended']) ? $data['music_genre_name_extended'] : null;
		$this->parent_id = isset($data['track_soundcloud_id']) ? intval($data['track_soundcloud_id']) : null;
		return $this;
	}
	public function tracks():array{
		return $this->api->tracks()->searchByGenre($this->id);
	}

	public function __get(string $key){
		if(isset($this->$key)){
			return $this->$key;
		}elseif(in_array($key, ['tracks'])){
			return $this->$key();
		}
		return null;
	}
	public function __set(string $key, $value){
		if(!property_exists($this, $key) or $key == 'api'){
			throw new Exception("{$key} is invalid property");
		}
		$this->$key = $value;
	}
}