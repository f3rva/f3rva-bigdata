<?php
namespace F3\Model;

class Member {
	private $memberId;
	private $f3Name;
	private $aliases;
	
	public function getMemberId() {
		return $this->memberId;
	}
	
	public function setMemberId($memberId) {
		$this->memberId = $memberId;
	}
	
	public function getF3Name() {
		return $this->f3Name;
	}
	
	public function setF3Name($f3Name) {
		$this->f3Name = $f3Name;
	}
	
	public function getAliases() {
		return $this->aliases;
	}
	
	public function setAliases($aliases) {
		$this->aliases = $aliases;
	}
}

?>