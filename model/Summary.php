<?php
namespace F3\Model;

class Summary {
	private $id;
	private $description;
	private $value;
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function setDescription($description) {
		$this->description = $description;
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
}
