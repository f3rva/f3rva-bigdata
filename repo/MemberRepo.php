<?php
namespace F3\Repo;
require_once('Database.php');

/**
 * Member repository encapsulating all database access for a member.
 * 
 * @author bbischoff
 */
class MemberRepository {
	protected $db;

	public function __construct() {
		$this->db = Database::getInstance()->getDatabase();
	}

	public function findAll() {
		$stmt = $this->db->query('
			select m.MEMBER_ID, m.F3_NAME from MEMBER m
			order by m.F3_NAME asc
		');
		
		return $stmt->fetchAll();
	}
	
	/**
	 * Finds a member by the f3name in the member table or the alias table if available
	 */
	public function findByF3NameOrAlias($f3name) {
		$stmt = $this->db->prepare('
			select m.MEMBER_ID, m.F3_NAME from MEMBER m
				left outer join MEMBER_ALIAS ma ON m.MEMBER_ID=ma.MEMBER_ID
				where UPPER(m.F3_NAME)=? or UPPER(ma.F3_ALIAS)=?'
		);
		$upperName = strtoupper($f3name);
		$stmt->execute([$upperName, $upperName]);
		
		$result = $stmt->fetch();
		return $result;
	}	
	
	/**
	 * Inserts the user into the database and returns the id of the inserted member
	 */
	public function save($name) {
		$stmt = $this->db->prepare('
			insert into MEMBER(F3_NAME) values (?)
		');
		$stmt->execute([$name]);
		
		return $this->db->lastInsertId();		
	}
	
	/**
	 * Deletes a member
	 */
	public function delete($memberId) {
		$stmt = $this->db->prepare('
			delete from MEMBER
				where MEMBER_ID=?
		');
		
		$stmt->execute([$memberId]);
	}
	
	public function createAlias($memberId, $associatedMemberId) {
		$stmt = $this->db->prepare('
			insert into MEMBER_ALIAS(MEMBER_ID, F3_ALIAS)
				select m1.MEMBER_ID, m2.F3_NAME from MEMBER m1, MEMBER m2
			    where m1.MEMBER_ID = ? and m2.MEMBER_ID = ?
		');
		
		$stmt->execute([$memberId, $associatedMemberId]);
	}
	
	public function relinkWorkoutPax($memberId, $associatedMemberId) {
		$stmt = $this->db->prepare('
			update WORKOUT_PAX
				set MEMBER_ID=?
				where MEMBER_ID=?
		');
		
		$stmt->execute([$memberId, $associatedMemberId]);
	}
}


?>