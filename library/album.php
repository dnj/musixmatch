<?php
namespace packages\musixmatch;
use \DateTime;
class album{
	protected $api;
	protected $id;
	protected $mbid;
	protected $name;
	protected $rating;
	protected $track_count;
	protected $type;
	protected $cover;
	protected $artist_id;
	protected $artist_mbid;
	protected $artist_name;
	protected $genres = [];
	protected $released_at;
	protected $updated_at;
	public function __construct(api $api){
		$this->api = $api;
	}
	
	public function getByID(int $id):album{
		return $this->get(array(
			'album_id' => $id
		));
	}
	public function searchByArtist(int $artist):collection{
		$collection = new collection();
		$collection->onPaginate(function(int $page, int $ipp, array $order) use ($artist) {
			$paramters = array(
				'artist_id' => $artist,
				'page' => $page,
				'page_size' => $ipp,
				'g_album_name' => 1
			);
			switch($order[0]){
				case('released_at'):
					$paramters['s_release_date'] = $order[1];
					break;
			}
			$result = $this->api->sendRequest("artist.albums.get", $paramters);
			return $this->parseSearchResponse($result);
		});
		return $collection;
	}

	public function tracks():collection{
		return $this->api->track()->searchByAlbum($this->id);
	}
	public function artist():artist{
		return $this->api->artist()->getByID($this->artist_id);
	}
	public function __get(string $key){
		if(isset($this->$key)){
			return $this->$key;
		}elseif(in_array($key, ['tracks', 'artist'])){
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

	private function parseSearchResponse(array $response):array{
		if(!isset($response['album_list'])){
			throw new \Exception("album_list not isset");
		}
		$albums = array();
		foreach($response['album_list'] as $album_data){
			$album = new static($this->api);
			$albums[] = $album->fromAPI($album_data['album']);
		}
		return $albums;
	}
	private function get(array $paramters):album{
		$result = $this->api->sendRequest("album.get", $paramters);
		if(!isset($result['album'])){
			throw new \Exception("album not isset");
		}
		$album = new static($this->api);
		return $album->fromAPI($result['album']);;
	}
	private function fromAPI(array $data){
		$this->id = isset($data['album_id']) ? $data['album_id'] : null;
		$this->mbid = isset($data['album_mbid']) ? $data['album_mbid'] : null;
		$this->name = isset($data['album_name']) ? $data['album_name'] : null;
		$this->rating = isset($data['album_rating']) ? $data['album_rating'] : null;
		$this->track_count = isset($data['album_track_count']) ? intval($data['album_track_count']) : null;
		$this->type = isset($data['album_release_type']) ? $data['album_release_type'] : null;
		$this->artist_id = isset($data['artist_id']) ? $data['artist_id'] : null;
		$this->artist_mbid = isset($data['artist_mbid']) ? $data['artist_mbid'] : null;
		$this->artist_name = isset($data['artist_name']) ? $data['artist_name'] : null;
		$this->released_at = isset($data['album_release_date']) ? new DateTime($data['album_release_date']) : null;
		$this->updated_at = isset($data['updated_time']) ? new DateTime($data['updated_time']) : null;
		$sizes = [];
		foreach(array_keys($data) as $key){
			if(substr($key, 0, strlen('album_coverart_')) == 'album_coverart_' and $data[$key]){
				$size = array(
					'url' => $data[$key]
				);
				list($size['width'], $size['height']) = explode('x', substr($key, strlen('album_coverart_')));
				$sizes[] = $size;
			}
		}
		if($sizes){
			$this->cover = new image($this->api);
			$this->cover->id = "album-".$this->id;
			foreach($sizes as $size){
				$this->cover->addSize($size['url'], $size['width'], $size['height']);
			}
		}
		if(isset($data['primary_genres']['music_genre_list'])){
			foreach($data['primary_genres']['music_genre_list'] as $genre_data){
				$genre = new genre($this->api);
				$genre->fromAPI($genre_data['music_genre']);
				$this->genres[] = $genre;
			}
		}
		return $this;
	}
}
