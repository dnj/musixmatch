<?php
namespace packages\musixmatch;
class artist{
	protected $api;
	protected $id;
	protected $mbid;
	protected $name;
	protected $country;
	protected $rating;
	protected $genres;
	protected $updated_at;
	protected $share_url;
	protected $edit_url;
	protected $twitter_url;
	protected $image;
	public function __construct(api $api){
		$this->api = $api;
	}
	
	public function getByID(int $id):artist{
		return $this->get(array(
			'artist_id' => $id,
			'part' => 'artist_image'
		));
	}
	public function searchByName(string $name):collection{
		return $this->search(array(
			'q' => $name,
			'part' => 'artist_image'
		));
	}
	private function search(array $paramters):collection{
		$collection = new collection();
		$collection->onPaginate(function(int $page, int $ipp, array $order) use ($paramters) {
			switch($order[0]){
				case(''):
				case('rating'):
					$paramters['s_artist_rating'] = $order[0] != '' ? $order[1] : 'desc';
					break;
			}
			$paramters['page'] = $page;
			$paramters['page_size'] = $ipp;
			$result = $this->api->sendRequest("artist.search", $paramters);
			return $this->parseSearchResponse($result);
		});
		return $collection;
	}
	private function parseSearchResponse(array $response):array{
		if(!isset($response['artist_list'])){
			throw new \Exception("artist_list not isset");
		}
		$artists = array();
		foreach($response['artist_list'] as $artist_data){
			$artist = new static($this->api);
			$artists[] = $artist->fromAPI($artist_data['artist']);
		}
		return $artists;
	}
	private function get(array $paramters):artist{
		$result = $this->api->sendRequest("artist.get", $paramters);
		if(!isset($result['artist'])){
			throw new \Exception("artist not isset");
		}
		$artist = new static($this->api);
		return $artist->fromAPI($result['artist']);;
	}
	private function fromAPI(array $data){
		$this->id = isset($data['artist_id']) ? $data['artist_id'] : null;
		$this->mbid = isset($data['artist_mbid']) ? $data['artist_mbid'] : null;
		$this->name = isset($data['artist_name']) ? $data['artist_name'] : null;
		$this->country = isset($data['artist_country']) ? $data['artist_country'] : null;
		$this->rating = isset($data['artist_rating']) ? $data['artist_rating'] : null;
		$this->twitter_url = isset($data['artist_twitter_url']) ? $data['artist_twitter_url'] : null;
		$this->edit_url = isset($data['artist_edit_url']) ? $data['artist_edit_url'] : null;
		$this->share_url = isset($data['artist_share_url']) ? $data['artist_share_url'] : null;
		$this->share_url = isset($data['artist_share_url']) ? $data['artist_share_url'] : null;
		$this->updated_at = isset($data['updated_time']) ? new \DateTime($data['updated_time']) : null;
		if(isset($data['artist_image']['image'])){
			$this->image = new image($this->api);
			$this->image->fromAPI($data['artist_image']['image']);
		}
		return $this;
	}
	public function tracks():collection{
		return $this->api->track()->searchByArtist($this->id);
	}
	public function albums():collection{
		return $this->api->album()->searchByArtist($this->id);
	}
	public function __get($key){
		if(isset($this->$key)){
			return $this->$key;
		}elseif(in_array($key, ['tracks', 'albums'])){
			return $this->$key();
		}
		return null;
	}
}
