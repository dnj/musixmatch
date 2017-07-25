<?php
namespace packages\musixmatch;
use \UnexpectedValueException;
class collection{
	private $onPaginate;
	private $order = ['', 'asc'];
	public function onPaginate(\Closure $source){
		$this->onPaginate = $source;
	}
	public function orderBy(string $key, string $order){
		if(!in_array($order, ['asc', 'desc'])){
			throw new UnexpectedValueException($order);
		}
		$this->order = [$key,$order];
		return $this;
	}
	public function paginate(int $page,int $items = 25){
		$source = $this->onPaginate;
		return $source($page, $items, $this->order);
	}
	public function first(){
		$data = $this->paginate(1,1);
		return is_array($data) ? $data[0] : null;
	}
	public function all(){
		return $this->paginate(1,100);
	}
}