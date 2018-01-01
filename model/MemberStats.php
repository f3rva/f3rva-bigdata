<?php
namespace F3\Model;

class MemberStats {
	private $memberId;
	private $numWorkouts;
	private $numQs;
	
	public function getMemberId() {
		return $this->memberId;
	}
	
	public function setMemberId($memberId) {
		$this->memberId = $memberId;
	}
	
	public function getNumWorkouts() {
		return $this->numWorkouts;
	}
	
	public function setNumWorkouts($numWorkouts) {
		$this->numWorkouts= $numWorkouts;
	}
	
	public function getNumQs() {
		return $this->numQs;
	}
	
	public function setNumQs($numQs) {
		$this->numQs = $numQs;
	}
	
	public function getWorkoutToQRatio() {
		return $this->numQs / $this->numWorkouts;
	}
}

?>