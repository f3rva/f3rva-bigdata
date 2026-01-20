<?php
namespace F3\Model;

class Summary {
	private $id;
	private $description;
	private $slug;
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
	
	public function getSlug() {
		return $this->slug;
	}
	
	public function setSlug($slug) {
		$this->slug = $slug;
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
}
