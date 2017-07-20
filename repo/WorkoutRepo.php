<?php
namespace F3\Repo;
require_once('Database.php'); 

use DateTime;
use DateTimeZone;

/**
 * Workout repository encapsulating all database access for a workout.
 *
 * @author bbischoff
 */
class WorkoutRepository {
	protected $db;

	public function __construct() {
		$this->db = Database::getInstance()->getDatabase();
	}
	
	public function deleteWorkoutAos($workoutId) {
		$stmt = $this->db->prepare('
			delete from WORKOUT_AO
				where WORKOUT_ID=?
		');
		$stmt->execute([$workoutId]);
	}
	
	public function deleteWorkoutMembers($workoutId) {
		$stmt = $this->db->prepare('
			delete from WORKOUT_PAX
				where WORKOUT_ID=?
		');
		$stmt->execute([$workoutId]);
	}
	
	public function deleteWorkoutQs($workoutId) {
		$stmt = $this->db->prepare('
			delete from WORKOUT_Q
				where WORKOUT_ID=?
		');
		$stmt->execute([$workoutId]);
	}
	
	public function find($id) {
		$stmt = $this->db->prepare('
			select w.WORKOUT_ID, w.WORKOUT_DATE, w.TITLE, w.BACKBLAST_URL, ao.AO_ID, ao.DESCRIPTION as AO, mq.MEMBER_ID as Q_ID, mq.F3_NAME as Q from WORKOUT w
				left outer join WORKOUT_AO wao on w.WORKOUT_ID = wao.WORKOUT_ID
				left outer join AO ao on wao.AO_ID = ao.AO_ID
				left outer join WORKOUT_Q wq on w.WORKOUT_ID = wq.WORKOUT_ID
				left outer join MEMBER mq on wq.MEMBER_ID = mq.MEMBER_ID
				where w.WORKOUT_ID=?
		');
		$stmt->execute([$id]);
		
		$result = $stmt->fetchAll();
		return $result;
	}

	public function findAll() {
		$stmt = $this->db->query('
			select w.WORKOUT_ID, w.WORKOUT_DATE, w.TITLE, w.BACKBLAST_URL, ao.AO_ID, ao.DESCRIPTION as AO, mq.MEMBER_ID as Q_ID, mq.F3_NAME as Q, count(mp.F3_NAME) as PAX_COUNT from WORKOUT w
				left outer join WORKOUT_PAX wp on w.WORKOUT_ID = wp.WORKOUT_ID
				left outer join WORKOUT_Q wq on w.WORKOUT_ID = wq.WORKOUT_ID
				left outer join MEMBER mq on wq.MEMBER_ID = mq.MEMBER_ID
				left outer join MEMBER mp on wp.MEMBER_ID = mp.MEMBER_ID
				left outer join WORKOUT_AO wao on w.WORKOUT_ID = wao.WORKOUT_ID
				left outer join AO ao on wao.AO_ID = ao.AO_ID
				group by w.WORKOUT_ID, ao.AO_ID, mq.MEMBER_ID, ao.DESCRIPTION
				order by w.WORKOUT_DATE desc, ao.DESCRIPTION asc
		');
		
		return $stmt->fetchAll();
	}

	public function findAllByAo($aoId) {
		$stmt = $this->db->prepare('
			select w.WORKOUT_ID, w.WORKOUT_DATE, w.TITLE, w.BACKBLAST_URL, ao.AO_ID, ao.DESCRIPTION as AO, mq.MEMBER_ID as Q_ID, mq.F3_NAME as Q, count(mp.F3_NAME) as PAX_COUNT from WORKOUT w
				left outer join WORKOUT_PAX wp on w.WORKOUT_ID = wp.WORKOUT_ID
				left outer join WORKOUT_Q wq on w.WORKOUT_ID = wq.WORKOUT_ID
				left outer join MEMBER mq on wq.MEMBER_ID = mq.MEMBER_ID
				left outer join MEMBER mp on wp.MEMBER_ID = mp.MEMBER_ID
				left outer join WORKOUT_AO wao on w.WORKOUT_ID = wao.WORKOUT_ID
				left outer join AO ao on wao.AO_ID = ao.AO_ID
				where ao.AO_ID = ?
				group by w.WORKOUT_ID, ao.AO_ID, mq.MEMBER_ID, ao.DESCRIPTION
				order by w.WORKOUT_DATE desc, ao.DESCRIPTION asc
		');
		$stmt->execute([$aoId]);
		
		return $stmt->fetchAll();
	}
	
	public function findAllByQ($qId) {
		$stmt = $this->db->prepare('
			select w.WORKOUT_ID, w.WORKOUT_DATE, w.TITLE, w.BACKBLAST_URL, ao.AO_ID, ao.DESCRIPTION as AO, mq.MEMBER_ID as Q_ID, mq.F3_NAME as Q, count(mp.F3_NAME) as PAX_COUNT from WORKOUT w
				left outer join WORKOUT_PAX wp on w.WORKOUT_ID = wp.WORKOUT_ID
				left outer join WORKOUT_Q wq on w.WORKOUT_ID = wq.WORKOUT_ID
				left outer join MEMBER mq on wq.MEMBER_ID = mq.MEMBER_ID
				left outer join MEMBER mp on wp.MEMBER_ID = mp.MEMBER_ID
				left outer join WORKOUT_AO wao on w.WORKOUT_ID = wao.WORKOUT_ID
				left outer join AO ao on wao.AO_ID = ao.AO_ID
				where wq.MEMBER_ID = ?
				group by w.WORKOUT_ID, ao.AO_ID, mq.MEMBER_ID, ao.DESCRIPTION
				order by w.WORKOUT_DATE desc, ao.DESCRIPTION asc
		');
		$stmt->execute([$qId]);
		
		return $stmt->fetchAll();
	}
	
	public function findAllByPax($paxId) {
		$stmt = $this->db->prepare('
			select w.WORKOUT_ID, w.WORKOUT_DATE, w.TITLE, w.BACKBLAST_URL, ao.AO_ID, ao.DESCRIPTION as AO, mq.MEMBER_ID as Q_ID, mq.F3_NAME as Q, count(mp.F3_NAME) as PAX_COUNT from WORKOUT w
				left outer join WORKOUT_PAX wp on w.WORKOUT_ID = wp.WORKOUT_ID
				left outer join WORKOUT_Q wq on w.WORKOUT_ID = wq.WORKOUT_ID
				left outer join MEMBER mq on wq.MEMBER_ID = mq.MEMBER_ID
				left outer join MEMBER mp on wp.MEMBER_ID = mp.MEMBER_ID
				left outer join WORKOUT_AO wao on w.WORKOUT_ID = wao.WORKOUT_ID
				left outer join AO ao on wao.AO_ID = ao.AO_ID
				where wp.MEMBER_ID = ?
				group by w.WORKOUT_ID, ao.AO_ID, mq.MEMBER_ID, ao.DESCRIPTION
				order by w.WORKOUT_DATE desc, ao.DESCRIPTION asc
		');
		$stmt->execute([$paxId]);
		
		return $stmt->fetchAll();
	}
	
	public function findCount($startDate, $endDate) {
		$sql = '
			select w.WORKOUT_ID, w.WORKOUT_DATE, w.TITLE, w.BACKBLAST_URL, ao.AO_ID, ao.DESCRIPTION as AO, count(mp.F3_NAME) as PAX_COUNT from WORKOUT w
				left outer join WORKOUT_PAX wp on w.WORKOUT_ID = wp.WORKOUT_ID
				left outer join MEMBER mp on wp.MEMBER_ID = mp.MEMBER_ID
				left outer join WORKOUT_AO wao on w.WORKOUT_ID = wao.WORKOUT_ID
				left outer join AO ao on wao.AO_ID = ao.AO_ID
		';
		
		$hasDates = !empty($startDate) && !empty($endDate);
		if ($hasDates) {
			$sql = $sql . '
				where w.WORKOUT_DATE between ? and ?
			';
		}
		
		$sql = $sql . '
				group by w.WORKOUT_ID, ao.AO_ID, ao.DESCRIPTION
				order by w.WORKOUT_DATE desc, ao.DESCRIPTION asc
		';
		$stmt = $this->db->prepare($sql);
		
		if ($hasDates) {
			$stmt->execute([$startDate, $endDate]);
		}
		else {
			$stmt->execute();
		}
		
		return $stmt->fetchAll();
	}
	
	public function findPax($id) {
		$stmt = $this->db->prepare('
			select wp.WORKOUT_ID, wp.MEMBER_ID, m.F3_NAME from WORKOUT_PAX wp
				join MEMBER m on wp.MEMBER_ID = m.MEMBER_ID
				where wp.WORKOUT_ID=?
		');
		$stmt->execute([$id]);
		
		return $stmt->fetchAll();
	}
	
	public function findWorkoutMember($workoutId, $memberId) {
		$stmt = $this->db->prepare('
			select wp.WORKOUT_ID, wp.MEMBER_ID, m.F3_NAME from WORKOUT_PAX wp
				join MEMBER m on wp.MEMBER_ID = m.MEMBER_ID
				where wp.WORKOUT_ID=? and wp.MEMBER_ID=?
		');
		$stmt->execute([$workoutId, $memberId]);
		
		return $stmt->fetch();
	}
	
	public function findWorkoutQ($workoutId, $memberId) {
		$stmt = $this->db->prepare('
			select wq.WORKOUT_ID, wq.MEMBER_ID, m.F3_NAME from WORKOUT_Q wq
				join MEMBER m on wq.MEMBER_ID = m.MEMBER_ID
				where wq.WORKOUT_ID=? and wq.MEMBER_ID=?
		');
		$stmt->execute([$workoutId, $memberId]);
		
		return $stmt->fetch();
	}
	
	public function findAo($aoId) {
		$stmt = $this->db->prepare('
			select AO_ID, DESCRIPTION from AO where AO_ID=?
		');
		$stmt->execute([$aoId]);
		
		return $stmt->fetch();
	}
	
	public function findWorkoutsGroupByDayOfWeek($startDate, $endDate) {
		$sql = '
			select DAYOFWEEK(w.WORKOUT_DATE) as DAY_ID, count(wp.MEMBER_ID) as PAX_COUNT from WORKOUT w
			join WORKOUT_PAX wp on w.WORKOUT_ID = wp.WORKOUT_ID
		';
		
		$hasDates = !empty($startDate) && !empty($endDate);
		if ($hasDates) {
			$sql = $sql . '
				where w.WORKOUT_DATE between ? and ?
			';
		}
		
		$sql = $sql . '
			group by DAYOFWEEK(w.WORKOUT_DATE)
		';
		$stmt = $this->db->prepare($sql);
		
		if ($hasDates) {
			$stmt->execute([$startDate, $endDate]);
		}
		else {
			$stmt->execute();
		}
		
		return $stmt->fetchAll();			
	}
	
	public function findAverageAttendanceByAO($startDate, $endDate) {
		$sql = '
			select wc.AO_ID, wc.DESCRIPTION, avg(wc.count) as AVERAGE from (
				select wa.AO_ID, ao.DESCRIPTION, count(*) as count from WORKOUT_PAX wp
				join WORKOUT_AO wa on wp.WORKOUT_ID = wa.WORKOUT_ID
			    join AO ao on wa.AO_ID = ao.AO_ID
			    join WORKOUT w on wa.WORKOUT_ID = w.WORKOUT_ID
		';
		
		$hasDates = !empty($startDate) && !empty($endDate);
		if ($hasDates) {
			$sql = $sql . '
				where w.WORKOUT_DATE between ? and ?
			';
		}
		
		$sql = $sql . '
				group by wp.WORKOUT_ID, wa.AO_ID
			    order by AO_ID asc
			) wc
			group by wc.AO_ID, wc.DESCRIPTION;
		';
		
		$stmt = $this->db->prepare($sql);
		
		if ($hasDates) {
			$stmt->execute([$startDate, $endDate]);
		}
		else {
			$stmt->execute();
		}
		
		return $stmt->fetchAll();
	}
	
	public function save($title, $dateArray, $url) {
		$stmt = $this->db->prepare('
			insert into WORKOUT(TITLE, WORKOUT_DATE, BACKBLAST_URL) values (?, ?, ?)
		');
		
		$dateStr = $this->getDateString($dateArray);

		$stmt->execute([$title, $dateStr, $url]);
		
		return $this->db->lastInsertId();
	}
	
	public function saveWorkoutMember($workoutId, $memberId) {
		if (!$this->findWorkoutMember($workoutId, $memberId)) {
			$stmt = $this->db->prepare('
				insert into WORKOUT_PAX(WORKOUT_ID, MEMBER_ID) values (?, ?)
			');
			
			$stmt->execute([$workoutId, $memberId]);
		}
	}
	
	public function saveWorkoutQ($workoutId, $memberId) {
		if (!$this->findWorkoutQ($workoutId, $memberId)) {
			$stmt = $this->db->prepare('
				insert into WORKOUT_Q(WORKOUT_ID, MEMBER_ID) values (?, ?)
			');
			
			$stmt->execute([$workoutId, $memberId]);
		}
	}
	
	public function saveWorkoutAo($workoutId, $aoId) {
		$stmt = $this->db->prepare('
			insert into WORKOUT_AO(WORKOUT_ID, AO_ID) values (?, ?)
		');
		
		$stmt->execute([$workoutId, $aoId]);
	}
	
	// select the ao or add it if it doesn't exist
	public function selectOrAddAo($aoDescription) {
		$stmt = $this->db->prepare('
			select AO_ID, DESCRIPTION from AO where upper(DESCRIPTION) = ?
		');
		$stmt->execute([strtoupper($aoDescription)]);
		$aoResult = $stmt->fetch();
		
		// found
		if ($aoResult) {
			// found an existing AO
			$ao = (object) array('aoId' => $aoResult['AO_ID'], 'description' => $aoResult['DESCRIPTION']);
		}
		else {
			// not found, create
			$stmt = $this->db->prepare('insert into AO(DESCRIPTION) values (?)');
			$stmt->execute([$aoDescription]);
			
			$ao = (object) array('aoId' => $this->db->lastInsertId(), 'description' => $aoDescription);
		}
		
		return $ao;
	}
	
	public function update($workoutId, $title, $dateArray, $url) {
		$stmt = $this->db->prepare('
			update WORKOUT set TITLE=?, WORKOUT_DATE=?, BACKBLAST_URL=?
				where WORKOUT_ID=?
		');
		
		$dateStr = $this->getDateString($dateArray);
		
		$stmt->execute([$title, $dateStr, $url, $workoutId]);
	}
	
	private function getDateString($dateArray) {
		// default to now if no date is available
		$dateStr = (new DateTime('now', new DateTimeZone('America/New_York')))->format('Y-m-d');
		
		if ($dateArray) {
			$dateStr = $dateArray['year'] . '-' . $dateArray['month'] . '-' . $dateArray['day'];
		}
		
		return $dateStr;
	}
}

?>