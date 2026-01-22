<?php
namespace F3\Model;

class AO implements \JsonSerializable {
	private $id;
	private $description;
	private $slug;
	
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

	public function jsonSerialize(): mixed
	{
		return [
			'id' => $this->id,
			'description' => $this->description,
			'slug' => $this->slug
		];
	}
}
