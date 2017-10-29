<?php 
namespace F3\Model;

class Workout implements \JsonSerializable {
	private $workoutId;
	private $backblastUrl;
	private $title;
	private $ao;
	private $q;
	private $pax;
	private $paxCount;
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
	
	public function getPaxCount() {
		return $this->paxCount;
	}
	
	public function setPaxCount($paxCount) {
		$this->paxCount = $paxCount;
	}
	
	public function getWorkoutDate() {
		return $this->workoutDate;
	}
	
	public function setWorkoutDate($workoutDate) {
		$this->workoutDate = $workoutDate;
	}
	
	public function jsonSerialize()
	{
		return [
				'workout' => [
						'workoutId' => $this->workoutId,
						'backblastUrl' => $this->backblastUrl,
						'title' => $this->title,
						'ao' => $this->ao,
						'q' => $this->q,
						'pax' => $this->pax,
						'paxCount' => $this->paxCount,
						'workoutDate' => $this->workoutDate
				]
		];
	}
}

?>