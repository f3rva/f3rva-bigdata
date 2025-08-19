<?php
namespace F3\Service;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}
require_once(__ROOT__ . '/model/AliasRequest.php');
require_once(__ROOT__ . '/model/AliasRequestStatus.php');
require_once(__ROOT__ . '/model/Member.php');
require_once(__ROOT__ . '/model/MemberStats.php');
require_once(__ROOT__ . '/model/Response.php');
require_once(__ROOT__ . '/repo/Database.php');
require_once(__ROOT__ . '/repo/MemberRepo.php');

use F3\Model\AliasRequest;
use F3\Model\AliasRequestStatus;
use F3\Model\Member;
use F3\Model\MemberStats;
use F3\Model\Response;
use F3\Repo\Database;
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
	
	public function getMembers(): array {
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
			
			// approve alias request if it exists
			$this->memberRepo->updateAliasRequest($memberId, $associatedMemberId, AliasRequestStatus::APPROVED);
			
			// delete from member
			$this->memberRepo->delete($associatedMemberId);

			$db->commit();
		}
		catch (\Exception $e) {
			$db->rollBack();
			error_log($e);
		}
	}

	/**
	 * Rejects an alias request
	 * @param mixed $memberId							the parent member id
	 * @param mixed $associatedMemberId		the alias member id
	 * @return void
	 */
	public function rejectAlias($memberId, $associatedMemberId) {
		$db = Database::getInstance()->getDatabase();
		try {
			$db->beginTransaction();
			
			// reject the alias request
			$this->memberRepo->updateAliasRequest($memberId, $associatedMemberId, AliasRequestStatus::REJECTED);
			
			$db->commit();
		}
		catch (\Exception $e) {
			$db->rollBack();
			error_log($e);
		}
	}

	/**
	 * Requests an alias for a member
	 * @param mixed $primaryMemberId	the parent member id
	 * @param mixed $aliasMemberId		the alias member id
	 * @return int	0 for success, 1 for duplicate, 2 for other error
	 */
	public function requestAlias($primaryMemberId, $aliasMemberId): int {
		// create a new record in the database as a staging area for requested aliases
		// this will be used to track the request and allow for approval
		// the request will be sent to the Nantan for approval
		// the Nantan will have the ability to approve or deny the request

		$return = Response::SUCCESS;
		$db = Database::getInstance()->getDatabase();

		try {
			$db->beginTransaction();
			
			$this->memberRepo->requestAlias($primaryMemberId, $aliasMemberId);

			$db->commit();
		}
		catch (\Exception $e) {
			$db->rollBack();
			if ($e->getCode() == 23000) {
				$return = Response::DUPLICATE;
			}
			else {
				error_log(message: $e);
				$return = Response::ERROR;
			}
		}

		return $return;
	}

	/**
	 * Summary of getAliasesByStatus
	 * @param \F3\Model\AliasRequestStatus $status
	 * @return array
	 */
	public function getAliasesByStatus($status): array {
		$aliasesResult = $this->memberRepo->findAliasesByStatus($status);
		$aliasesArray = array();
		
		foreach ($aliasesResult as $aliasResult) {
			$member = $this->createMember($aliasResult["MEMBER_ID"], $aliasResult["F3_NAME"]);
			$alias = $this->createMember($aliasResult["ALIAS_ID"], $aliasResult["ALIAS_NAME"]);
			$aliasRequestStatus = AliasRequestStatus::enumFrom($aliasResult["STATUS"]);
			$aliasRequest = new AliasRequest($member, $alias, $aliasRequestStatus);
			$aliasesArray[] = $aliasRequest;
		}
		
		return $aliasesArray;
	}
	
	/**
	 * Summary of createMember
	 * @param mixed $memberId
	 * @param mixed $f3Name
	 * @return \F3\Model\Member
	 */
	private function createMember($memberId, $f3Name): Member {
		$member = new Member();
		$member->setMemberId($memberId);
		$member->setF3Name($f3Name);
		
		return $member;
	}
}
