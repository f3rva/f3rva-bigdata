<?php
namespace F3\Service;

define('__ROOT__', dirname(dirname(dirname(__FILE__))));
require_once(__ROOT__ . '/model/Member.php');
require_once(__ROOT__ . '/repo/MemberRepo.php');

use F3\Model\Member;
use F3\Repo\MemberRepository;

/**
 * Service class encapsulating business logic for members.
 *
 * @author bbischoff
 */
class MemberService {
	private $memberRepo;
	
	public function __construct() {
		$this->memberRepo = new MemberRepository();
	}
	
	public function getMember($name) {
		$memberResult = $this->memberRepo->findByF3NameOrAlias($name);
		$member = null;
		
		
		if ($memberResult) {
			$member = $this->createMember($memberResult['MEMBER_ID'], $memberResult['F3_NAME']);
		}
		
		return $member;
	}
	
	public function getOrAddMember($name) {
		$member = $this->getMember($name);

		if (is_null($member)) {
			$memberId = $this->memberRepo->save($name);
			$member = $this->createMember($memberId, $name);
		}
		
		return $member;
	}
	
	private function createMember($memberId, $f3Name) {
		$member = new Member();
		$member->setMemberId($memberId);
		$member->setF3Name($f3Name);
		
		return $member;
	}
}
?>