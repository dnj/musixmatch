<?php
namespace packages\musixmatch;
use \DateTime;
class lyrics{
	protected $api;
	protected $id;
	protected $isInstrumental;
	protected $language;
	protected $updated_at;
	protected $texts = [];
	public function __construct(api $api){
		$this->api = $api;
	}
	public function fromLyricsAPI(array $data):lyrics{
		$this->id = isset($data['lyrics_id']) ? $data['lyrics_id'] : null;
		$this->language = isset($data['lyrics_language']) ? $data['lyrics_language'] : null;
		$this->updated_at = isset($data['updated_time']) ? new DateTime($data['updated_time']) : null;
		$this->isInstrumental = (isset($data['instrumental']) and $data['instrumental']);
		if(isset($data['lyrics_body'])){
			$lines = explode("\n", $data['lyrics_body']);
			foreach($lines as $line){
				$line = trim($line);
				if($line){
					$this->addText($line);
				}
			}
		}
		return $this;
	}
	public function fromSubtitleAPI(array $data):lyrics{
		$this->id = isset($data['subtitle_id']) ? $data['subtitle_id'] : null;
		$this->language = isset($data['subtitle_language']) ? $data['subtitle_language'] : null;
		$this->updated_at = isset($data['updated_time']) ? new DateTime($data['updated_time']) : null;
		$this->isInstrumental = (isset($data['instrumental']) and $data['instrumental']);
		if(isset($data['subtitle_body'])){
			$lines = explode("\n", $data['subtitle_body']);
			foreach($lines as $line){
				$line = trim($line);
				if(!preg_match("/\[(\d+:\d+.\d+)\]\s+(.*)/", $line, $matches)){
					continue;
				}
				$time = explode(":", $matches[1]);
				$time = $time[0] * 60 + intval($time[1]);
				$this->addText($matches[2], $time);
			}
		}
		return $this;
	}
	public function translateFromAPI(array $data):lyrics{
		foreach($data as $translation_data){
			$translation = $translation_data['translation'];
			$this->translate($translation['matched_line'], $translation['description']);
		}
		return $this;
	}
	public function addText(string $text, int $time = 0){
		$this->texts[] = array(
			'text' => $text,
			'time' => $time
		);
	}
	public function translate(string $original, string $translation){
		foreach($this->texts as $key => $text){
			if($text['text'] == $original){
				$this->texts[$key]['translate'] = $translation;
			}
		}
	}
	public function __set(string $key, $value){
		if(!property_exists($this, $key) or $key == 'api'){
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