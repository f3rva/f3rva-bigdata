<?php
namespace F3\Service;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}
require_once(__ROOT__ . '/model/Member.php');
require_once(__ROOT__ . '/model/MemberStats.php');
require_once(__ROOT__ . '/repo/Database.php');
require_once(__ROOT__ . '/repo/MemberRepo.php');

use F3\Model\Member;
use F3\Repo\Database;
use F3\Repo\MemberRepository;
use F3\Model\MemberStats;

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
	
	public function getMembers() {
		$membersResult = $this->memberRepo->findAll();
		$membersArray = array();
		
		foreach ($membersResult as $memberResult) {
			$member = $this->createMember($memberResult["MEMBER_ID"], $memberResult["F3_NAME"]);
			$membersArray[$member->getMemberId()] = $member;
		}
		
		return $membersArray;
	}
	
	public function getMember($name) {
		$memberResult = $this->memberRepo->findByF3NameOrAlias($name);
		$member = null;
		
		if ($memberResult) {
			$member = $this->createMember($memberResult['MEMBER_ID'], $memberResult['F3_NAME']);
		}
		
		return $member;
	}
	
	public function getMemberById($memberId) {
		$memberResult = $this->memberRepo->find($memberId);
		$aliases = $this->memberRepo->findAliases($memberId);
		$member = null;
		
		if ($memberResult) {
			$member = $this->createMember($memberResult['MEMBER_ID'], $memberResult['F3_NAME']);
			
			$aliasArray = array();
			if ($aliases) {
				foreach($aliases as $alias) {
					$aliasArray[$alias['F3_ALIAS']] = $alias['F3_ALIAS'];
				}
			}
			$member->setAliases($aliasArray);
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
	
	public function getMemberStats($memberId) {
		$statsResult = $this->memberRepo->findMemberStats($memberId);
		
		$stats = new MemberStats();
		$stats->setMemberId($memberId);
		$stats->setNumWorkouts($statsResult["NUM_WORKOUTS"]);
		$stats->setNumQs($statsResult["NUM_QS"]);
		$stats->setQRatio($statsResult["Q_RATIO"]);
		
		return $stats;
	}
	
	public function assignAlias($memberId, $associatedMemberId) {
		$db = Database::getInstance()->getDatabase();
		try {
			$db->beginTransaction();
			
			// create an audit trail with the previous entries, in case we need to revert
			$this->memberRepo->createAliasAuditTrail($associatedMemberId);

			// create the alias if it doesn't already exist
			if (!$this->memberRepo->findExistingAlias($memberId, $associatedMemberId)) {
				$this->memberRepo->createAlias($memberId, $associatedMemberId);
			}
			
			// remove any duplicate members from workouts (the bleeder effect)
			$dupResult = $this->memberRepo->findDuplicateWorkoutMembers($memberId, $associatedMemberId);
			foreach ($dupResult as $dup) {
				$this->memberRepo->removeMemberFromWorkout($dup['WORKOUT_ID'], $associatedMemberId);
			}
			
			// re-link workout pax records
			$this->memberRepo->relinkWorkoutPax($memberId, $associatedMemberId);
			
			// re-link workout q records
			$this->memberRepo->relinkWorkoutQ($memberId, $associatedMemberId);
			
			// re-assign existing aliases
			$this->memberRepo->relinkMemberAlias($memberId, $associatedMemberId);
			
			// delete from member
			$this->memberRepo->delete($associatedMemberId);
			
			$db->commit();
		}
		catch (\Exception $e) {
			$db->rollBack();
			error_log($e);
		}
	}
	
	private function createMember($memberId, $f3Name) {
		$member = new Member();
		$member->setMemberId($memberId);
		$member->setF3Name($f3Name);
		
		return $member;
	}
}
?>