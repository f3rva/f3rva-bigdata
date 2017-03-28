<?php 
namespace F3\Model;

class Workout {
	private $workoutId;
	private $backblastUrl;
	private $title;
	private $ao;
	private $q;
	private $pax;
	private $workoutDate;

	public function getWorkoutId() {
		return $this->workoutId;
	}
	
	public function setWorkoutId($workoutId) {
		$this->workoutId = $workoutId;
	}

	public function getBackblastUrl() {
		return $this->backblastUrl;
	}
	
	public function setBackblastUrl($backblastUrl) {
		$this->backblastUrl= $backblastUrl;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function setTitle($title) {
		$this->title= $title;
	}
	
	public function getAo() {
		return $this->ao;
	}
	
	public function setAo($ao) {
		$this->ao = $ao;
	}
	
	public function getQ() {
		return $this->q;
	}
	
	public function setQ($q) {
		$this->q = $q;
	}
	
	public function getPax() {
		return $this->pax;
	}
	
	public function setPax($pax) {
		$this->pax = $pax;
	}

	public function getWorkoutDate() {
		return $this->workoutDate;
	}
	
	public function setWorkoutDate($workoutDate) {
		$this->workoutDate = $workoutDate;
	}
}

?>