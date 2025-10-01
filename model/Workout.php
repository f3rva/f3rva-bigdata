<?php 
namespace F3\Model;

class Workout implements \JsonSerializable {
	private $workoutId;
	private $backblastUrl;
	private $title;
	private $slug;
	private $ao;
	private $q;
	private $pax;
	private $paxCount;
	private $workoutDate;
	private $content;

	public function getWorkoutId(): mixed {
		return $this->workoutId;
	}
	
	public function setWorkoutId($workoutId): void {
		$this->workoutId = $workoutId;
	}

	public function getBackblastUrl(): mixed {
		return $this->backblastUrl;
	}
	
	public function setBackblastUrl($backblastUrl): void {
		$this->backblastUrl= $backblastUrl;
	}
	
	public function getTitle(): mixed {
		return $this->title;
	}
	
	public function setTitle($title): void {
		$this->title= $title;
	}
	
	public function getSlug(): mixed {
		return $this->slug;
	}

	public function setSlug($slug): void {
		$this->slug = $slug;
	}

	public function getAo(): mixed {
		return $this->ao;
	}
	
	public function setAo($ao): void {
		$this->ao = $ao;
	}
	
	public function getQ(): mixed {
		return $this->q;
	}
	
	public function setQ($q): void {
		$this->q = $q;
	}
	
	public function getPax(): mixed {
		return $this->pax;
	}
	
	public function setPax($pax): void {
		$this->pax = $pax;
	}
	
	public function getPaxCount(): mixed {
		return $this->paxCount;
	}
	
	public function setPaxCount($paxCount): void {
		$this->paxCount = $paxCount;
	}
	
	public function getWorkoutDate(): mixed {
		return $this->workoutDate;
	}
	
	public function setWorkoutDate($workoutDate): void {
		$this->workoutDate = $workoutDate;
	}

	public function getContent(): string {
		return $this->content;
	}

	public function setContent($content): void {
		$this->content = $content;
	}

	public function jsonSerialize(): mixed
	{
		return [
			'workoutId' => $this->workoutId,
			'backblastUrl' => $this->backblastUrl,
			'title' => $this->title,
			'slug' => $this->slug,
			'ao' => $this->ao,
			'q' => $this->q,
			'pax' => $this->pax,
			'paxCount' => $this->paxCount,
			'workoutDate' => $this->workoutDate,
			'content' => $this->content
		];
	}
}
