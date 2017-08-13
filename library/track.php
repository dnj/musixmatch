<?php
namespace packages\musixmatch;
use \DateTime;
class track{
	static public $iso639_2_T = array(
		'abk' => 'ab','aar' => 'aa','afr' => 'af','aka' => 'ak','sqi' => 'sq','amh' => 'am','ara' => 'ar','arg' => 'an','hye' => 'hy','asm' => 'as','ava' => 'av',
		'ave' => 'ae','aym' => 'ay','aze' => 'az','bam' => 'bm','bak' => 'ba','eus' => 'eu','bel' => 'be','ben' => 'bn','bih' => 'bh','bis' => 'bi','bos' => 'bs',
		'bre' => 'br','bul' => 'bg','mya' => 'my','cat' => 'ca','cha' => 'ch','che' => 'ce','nya' => 'ny','zho' => 'zh','chv' => 'cv','cor' => 'kw','cos' => 'co',
		'cre' => 'cr','hrv' => 'hr','ces' => 'cs','dan' => 'da','div' => 'dv','nld' => 'nl','dzo' => 'dz','eng' => 'en','epo' => 'eo','est' => 'et','ewe' => 'ee',
		'fao' => 'fo','fij' => 'fj','fin' => 'fi','fra' => 'fr','ful' => 'ff','glg' => 'gl','kat' => 'ka','deu' => 'de','ell' => 'el','grn' => 'gn','guj' => 'gu',
		'hat' => 'ht','hau' => 'ha','heb' => 'he','her' => 'hz','hin' => 'hi','hmo' => 'ho','hun' => 'hu','ina' => 'ia','ind' => 'id','ile' => 'ie','gle' => 'ga',
		'ibo' => 'ig','ipk' => 'ik','ido' => 'io','isl' => 'is','ita' => 'it','iku' => 'iu','jpn' => 'ja','jav' => 'jv','kal' => 'kl','kan' => 'kn','kau' => 'kr',
		'kas' => 'ks','kaz' => 'kk','khm' => 'km','kik' => 'ki','kin' => 'rw','kir' => 'ky','kom' => 'kv','kon' => 'kg','kor' => 'ko','kur' => 'ku','kua' => 'kj',
		'lat' => 'la','ltz' => 'lb','lug' => 'lg','lim' => 'li','lin' => 'ln','lao' => 'lo','lit' => 'lt','lub' => 'lu','lav' => 'lv','glv' => 'gv','mkd' => 'mk',
		'mlg' => 'mg','msa' => 'ms','mal' => 'ml','mlt' => 'mt','mri' => 'mi','mar' => 'mr','mah' => 'mh','mon' => 'mn','nau' => 'na','nav' => 'nv','nde' => 'nd',
		'nep' => 'ne','ndo' => 'ng','nob' => 'nb','nno' => 'nn','nor' => 'no','iii' => 'ii','nbl' => 'nr','oci' => 'oc','oji' => 'oj','chu' => 'cu','orm' => 'om',
		'ori' => 'or','oss' => 'os','pan' => 'pa','pli' => 'pi','fas' => 'fa','pol' => 'pl','pus' => 'ps','por' => 'pt','que' => 'qu','roh' => 'rm','run' => 'rn',
		'ron' => 'ro','rus' => 'ru','san' => 'sa','srd' => 'sc','snd' => 'sd','sme' => 'se','smo' => 'sm','sag' => 'sg','srp' => 'sr','gla' => 'gd','sna' => 'sn',
		'sin' => 'si','slk' => 'sk','slv' => 'sl','som' => 'so','sot' => 'st','spa' => 'es','sun' => 'su','swa' => 'sw','ssw' => 'ss','swe' => 'sv','tam' => 'ta',
		'tel' => 'te','tgk' => 'tg','tha' => 'th','tir' => 'ti','bod' => 'bo','tuk' => 'tk','tgl' => 'tl','tsn' => 'tn','ton' => 'to','tur' => 'tr','tso' => 'ts',
		'tat' => 'tt','twi' => 'tw','tah' => 'ty','uig' => 'ug','ukr' => 'uk','urd' => 'ur','uzb' => 'uz','ven' => 've','vie' => 'vi','vol' => 'vo','wln' => 'wa',
		'cym' => 'cy','wol' => 'wo','fry' => 'fy','xho' => 'xh','yid' => 'yi','yor' => 'yo','zha' => 'za','zul' => 'zu'
	);

	protected $api;
	protected $id;
	protected $mbid;
	protected $spotify_id;
	protected $soundcloud_id;
	protected $xboxmusic_id;
	protected $name;
	protected $rating;
	protected $length;
	protected $isInstrumental;
	protected $isExplicit;
	protected $hasLyrics;
	protected $hasSubtitle;
	protected $favourites;
	protected $commontrack_id;
	protected $language;
	protected $lyrics_id;
	protected $subtitle_id;
	protected $album_id;
	protected $album_name;
	protected $album_cover;
	protected $artist_id;
	protected $artist_mbid;
	protected $artist_name;
	protected $artists = [];
	protected $genres = [];
	protected $translates = [];
	protected $share_url;
	protected $edit_url;
	protected $released_at;
	protected $updated_at;
	public function __construct(api $api){
		$this->api = $api;
	}
	
	public function getByID(int $id):track{
		return $this->get(array(
			'track_id' => $id,
			'part' => 'track_lyrics_translation_status'
		));
	}
	public function searchByName(string $name):collection{
		return $this->search(array(
			'q_track' => $name,
			'part' => 'track_lyrics_translation_status'
		));
	}

	public function searchByArtist(int $artist):collection{
		return $this->search(array(
			'f_artist_id' => $artist,
			'part' => 'track_lyrics_translation_status'
		));
	}

	public function searchByAlbum(int $album):collection{
		$collection = new collection();
		$collection->onPaginate(function(int $page, int $ipp, array $order) use ($album, $collection) {
			$result = $this->api->sendRequest("album.tracks.get", array(
				'album_id' => $album,
				'page' => $page,
				'page_size' => $ipp,
				'part' => 'track_lyrics_translation_status'
			));
			$collection->setTotalCount($this->api->getHeader('available'));
			return $this->parseSearchResponse($result);
		});
		return $collection;
	}

	public function searchByGenre(int $genre):collection{
		return $this->search(array(
			'f_music_genre_id' => $genre,
			'part' => 'track_lyrics_translation_status'
		));
	}
	public function commontrack():track{
		if(!$this->commontrack_id){
			return $this;
		}
		return $this->getByID($this->commontrack_id);
	}
	public function artist():artist{
		return $this->api->artist()->getByID($this->artist_id);
	}
	public function lyrics():lyrics{
		$result = $this->api->sendRequest("track.lyrics.get", array(
			'track_id' => $this->id
		));
		if(!isset($result['lyrics'])){
			throw new \Exception("lyrics not isset");
		}
		if(!$this->language){
			$this->language = $result['lyrics']['lyrics_language'];
		}
		$lyrics = new lyrics($this->api);
		return $lyrics->fromLyricsAPI($result['lyrics']);
	}
	public function subtitle():lyrics{
		$result = $this->api->sendRequest("track.subtitle.get", array(
			'track_id' => $this->id
		));
		if(!isset($result['subtitle'])){
			throw new \Exception("subtitle not isset");
		}
		if(!$this->language){
			$this->language = $result['subtitle']['subtitle_language'];
		}
		$lyrics = new lyrics($this->api);
		return $lyrics->fromSubtitleAPI($result['subtitle']);
	}
	public function translate(string $language):lyrics{
		$lyrics = $this->hasSubtitle ? $this->subtitle() : $this->lyrics();
		
		$result = $this->api->sendRequest("crowd.track.translations.get", array(
			'track_id' => $this->id,
			'page_size' => 100,
			'page' => 1,
			'selected_language' => $language,
			'translation_fields_set' => 'minimal'
		));
		if(!isset($result['translations_list'])){
			throw new \Exception("translations_list not isset");
		}
		return $lyrics->translateFromAPI($result['translations_list']);
	}
	public function hasTranslationTo(string $language):bool{
		foreach($this->translates as $translation){
			if($translation['language'] == $language){
				return true;
			}
		}
		return false;
	}
	public function __get(string $key){
		if(isset($this->$key)){
			return $this->$key;
		}elseif(in_array($key, ['commontrack', 'lyrics', 'subtitle', 'album', 'artist'])){
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

	private function search(array $paramters):collection{
		$collection = new collection();
		$collection->onPaginate(function(int $page, int $ipp, array $order) use ($paramters, $collection) {
			switch($order[0]){
				case('rating'):
					$paramters['s_track_rating'] = $order[1];
					break;
			}
			$paramters['page'] = $page;
			$paramters['page_size'] = $ipp;
			$result = $this->api->sendRequest("track.search", $paramters);
			$collection->setTotalCount($this->api->getHeader('available'));
			return $this->parseSearchResponse($result);
		});
		return $collection;
	}
	private function parseSearchResponse(array $response):array{
		if(!isset($response['track_list'])){
			throw new \Exception("track_list not isset");
		}
		$tracks = array();
		foreach($response['track_list'] as $track_data){
			$track = new static($this->api);
			$tracks[] = $track->fromAPI($track_data['track']);
		}
		return $tracks;
	}
	private function get(array $paramters):track{
		$result = $this->api->sendRequest("track.get", $paramters);
		if(!isset($result['track'])){
			throw new \Exception("track not isset");
		}
		$track = new static($this->api);
		return $track->fromAPI($result['track']);
	}
	private function fromAPI(array $data){
		$this->id = isset($data['track_id']) ? $data['track_id'] : null;
		$this->mbid = isset($data['track_mbid']) ? $data['track_mbid'] : null;
		$this->spotify_id = isset($data['track_spotify_id']) ? intval($data['track_spotify_id']) : null;
		$this->soundcloud_id = isset($data['track_soundcloud_id']) ? intval($data['track_soundcloud_id']) : null;
		$this->xboxmusic_id = isset($data['track_xboxmusic_id']) ? intval($data['track_xboxmusic_id']) : null;
		$this->name = isset($data['track_name']) ? $data['track_name'] : null;
		$this->rating = isset($data['track_rating']) ? $data['track_rating'] : null;
		$this->length = isset($data['track_length']) ? intval($data['track_length']) : null;
		$this->isInstrumental = (isset($data['instrumental']) and $data['instrumental']);
		$this->isExplicit = (isset($data['explicit']) and $data['explicit']);
		$this->hasLyrics = (isset($data['has_lyrics']) and $data['has_lyrics']);
		$this->hasSubtitle = (isset($data['has_subtitles']) and $data['has_subtitles']);
		$this->favourites = isset($data['num_favourite']) ? $data['num_favourite'] : null;
		$this->commontrack_id = isset($data['commontrack_id']) ? $data['commontrack_id'] : null;
		$this->lyrics_id = isset($data['lyrics_id']) ? $data['lyrics_id'] : null;
		$this->subtitle_id = isset($data['subtitle_id']) ? $data['subtitle_id'] : null;
		$this->album_id = isset($data['album_id']) ? $data['album_id'] : null;
		$this->album_name = isset($data['album_name']) ? $data['album_name'] : null;
		$this->artist_id = isset($data['artist_id']) ? $data['artist_id'] : null;
		$this->artist_mbid = isset($data['artist_mbid']) ? $data['artist_mbid'] : null;
		$this->artist_name = isset($data['artist_name']) ? $data['artist_name'] : null;
		$this->edit_url = isset($data['track_edit_url']) ? $data['track_edit_url'] : null;
		$this->share_url = isset($data['track_share_url']) ? $data['track_share_url'] : null;
		$this->released_at = isset($data['first_release_date']) ? new DateTime($data['first_release_date']) : null;
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
			$this->album_cover = new image($this->api);
			$this->album_cover->id = "album-".$this->album_id;
			foreach($sizes as $size){
				$this->album_cover->addSize($size['url'], $size['width'], $size['height']);
			}
		}
		if(isset($data['primary_genres']['music_genre_list'])){
			foreach($data['primary_genres']['music_genre_list'] as $genre_data){
				$genre = new genre($this->api);
				$genre->fromAPI($genre_data['music_genre']);
				$this->genres[] = $genre;
			}
		}
		if(isset($data['track_lyrics_translation_status'])){
			foreach($data['track_lyrics_translation_status'] as $translation){
				if(!$this->language and isset(self::$iso639_2_T[$translation['from']])){
					$this->language = self::$iso639_2_T[$translation['from']];
				}
				if(isset(self::$iso639_2_T[$translation['to']])){
					$this->translates[] = array(
						'language' => self::$iso639_2_T[$translation['to']],
						'perc' => $translation['perc'],
					);
				}
			}
		}
		return $this;
	}
}
