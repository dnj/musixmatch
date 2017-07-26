<?php
namespace packages\musixmatch;
use \UnexpectedValueException;
class collection{
	private $onPaginate;
	private $order = ['', 'asc'];
	private $totalCount;
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
		return (is_array($data) and count($data) > 0) ? $data[0] : null;
	}
	public function all(){
		return $this->paginate(1,100);
	}
	public function getTotalCount(){
		if(!$this->totalCount){
			$this->first();
		}
		return $this->totalCount;
	}
	public function setTotalCount(int $totalCount){
		if($totalCount < 0){
			throw new UnexpectedValueException($totalCount);
		}
		$this->totalCount = $totalCount;
	}
	public function __get(string $key){
		if($key == 'totalCount'){
			return $this->getTotalCount();
		}else{
			throw new UnexpectedValueException($key);
		}
	}
	public function __set(string $key, $value){
		if($key == 'totalCount'){
			$this->setTotalCount($value);
		}else{
			throw new UnexpectedValueException($key);
		}
	}

}