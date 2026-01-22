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
	
	public function deleteWorkoutDetails($workoutId) {
		$stmt = $this->db->prepare(query: '
			delete from WORKOUT_DETAILS
				where WORKOUT_ID=?
		');
		$stmt->execute(params: [$workoutId]);
	}

	public function deleteWorkout($workoutId) {
			$stmt = $this->db->prepare('
			delete from WORKOUT
			where WORKOUT_ID=?
		');
		$stmt->execute([$workoutId]);
	}
			
	public function find($id) {
		$query = $this->replaceFindBySingularPlaceholders(whereClauses: "w.WORKOUT_ID=?");
		$stmt = $this->db->prepare($query);
		$stmt->execute([$id]);
		
		$result = $stmt->fetchAll();
		return $result;
	}

	public function findByDateAndSlug($date, $slug): mixed {
		$query = $this->replaceFindBySingularPlaceholders(whereClauses: "w.WORKOUT_DATE=? and w.SLUG=?");
		$stmt = $this->db->prepare($query);

		$stmt->execute(params: [$date, $slug]);
		
		$result = $stmt->fetchAll();
		return $result;
	}

	private function replaceFindBySingularPlaceholders($whereClauses): string {
		$query = '
			select
					w.WORKOUT_ID,
					w.WORKOUT_DATE,
					w.TITLE,
					w.AUTHOR,
					w.SLUG,
					w.BACKBLAST_URL,
					ao_agg.AO_IDS,
					ao_agg.AO_DESCRIPTIONS AS AO,
					q_agg.Q_IDS,
					q_agg.Q_NAMES AS Q,
					wd.HTML_CONTENT -- HTML_CONTENT is outside the aggregation as it is 1:1
			from
					WORKOUT w
			-- Subquery 1: Aggregate Qs
			left outer join
					(
							select
									wq.WORKOUT_ID,
									GROUP_CONCAT(mq.MEMBER_ID SEPARATOR \', \') AS Q_IDS,
									GROUP_CONCAT(mq.F3_NAME SEPARATOR \', \') AS Q_NAMES
							from
									WORKOUT_Q wq
							inner join
									MEMBER mq ON wq.MEMBER_ID = mq.MEMBER_ID
							group by
									wq.WORKOUT_ID
					) q_agg ON w.WORKOUT_ID = q_agg.WORKOUT_ID
			-- Subquery 2: Aggregate AOs
			left outer join
					(
							select
									wao.WORKOUT_ID,
									GROUP_CONCAT(ao.AO_ID SEPARATOR \', \') AS AO_IDS,
									GROUP_CONCAT(ao.DESCRIPTION SEPARATOR \', \') AS AO_DESCRIPTIONS,
									GROUP_CONCAT(ao.SLUG SEPARATOR \', \') AS AO_SLUGS
							from
									WORKOUT_AO wao
							inner join
									AO ao ON wao.AO_ID = ao.AO_ID
							group by
									wao.WORKOUT_ID
					) ao_agg ON w.WORKOUT_ID = ao_agg.WORKOUT_ID
			-- Join to WORKOUT_DETAILS (should be 1:1 or 1:0, so it will not cause duplicates)
			left outer join
					WORKOUT_DETAILS wd ON w.WORKOUT_ID = wd.WORKOUT_ID
			where
					::{WHERE_CLAUSE}::
			-- Grouping ensures a single record for the workout, even if details were 1:many
			group by
					w.WORKOUT_ID,
					w.WORKOUT_DATE,
					w.TITLE,
					w.AUTHOR,
					w.SLUG,
					w.BACKBLAST_URL,
					ao_agg.AO_IDS,
					ao_agg.AO_DESCRIPTIONS,
					ao_agg.AO_SLUGS,
					q_agg.Q_IDS,
					q_agg.Q_NAMES,
					wd.HTML_CONTENT; -- Include the 1:1/1:0 column in the GROUP BY
		';

		return str_replace('::{WHERE_CLAUSE}::', $whereClauses, $query);
	}

		public function findAll($limit = 20, $offset = 0): array {
		$stmt = $this->db->prepare('
			select
					w.WORKOUT_ID,
					w.WORKOUT_DATE,
					w.TITLE,
					w.AUTHOR,
					w.SLUG,
					w.BACKBLAST_URL,
					ao_agg.AO_IDS,
					ao_agg.AO_DESCRIPTIONS AS AO,
					q_agg.Q_IDS,
					q_agg.Q_NAMES AS Q,
					COUNT(mp.F3_NAME) AS PAX_COUNT
			from
					WORKOUT w
			-- Subquery 1: Aggregate Qs
			left outer join
					(
							select
									wq.WORKOUT_ID,
									GROUP_CONCAT(mq.MEMBER_ID SEPARATOR \', \') AS Q_IDS,
									GROUP_CONCAT(mq.F3_NAME SEPARATOR \', \') AS Q_NAMES
							from
									WORKOUT_Q wq
							inner join
									MEMBER mq ON wq.MEMBER_ID = mq.MEMBER_ID
							group by
									wq.WORKOUT_ID
					) q_agg ON w.WORKOUT_ID = q_agg.WORKOUT_ID
			-- Subquery 2: Aggregate AOs
			left outer join
					(
							select
									wao.WORKOUT_ID,
									GROUP_CONCAT(ao.AO_ID SEPARATOR \', \') AS AO_IDS,
									GROUP_CONCAT(ao.DESCRIPTION SEPARATOR \', \') AS AO_DESCRIPTIONS
							from
									WORKOUT_AO wao
							inner join
									AO ao ON wao.AO_ID = ao.AO_ID
							group by
									wao.WORKOUT_ID
					) ao_agg ON w.WORKOUT_ID = ao_agg.WORKOUT_ID
			-- Join for PAX count
			left outer join
					WORKOUT_PAX wp ON w.WORKOUT_ID = wp.WORKOUT_ID
			left outer join
					MEMBER mp ON wp.MEMBER_ID = mp.MEMBER_ID
			group by
					w.WORKOUT_ID,
					w.WORKOUT_DATE,
					w.TITLE,
					w.AUTHOR,
					w.SLUG,
					w.BACKBLAST_URL,
					ao_agg.AO_IDS,
					ao_agg.AO_DESCRIPTIONS,
					ao_agg.AO_SLUGS,
					q_agg.Q_IDS,
					q_agg.Q_NAMES
			order by
					w.WORKOUT_DATE DESC,
					ao_agg.AO_DESCRIPTIONS ASC
			limit ? offset ?
		');
		$stmt->execute([$limit, $offset]);

		return $stmt->fetchAll();
	}

	public function findAllByDateRange($startDate, $endDate, $limit = 20, $offset = 0) {
		$stmt = $this->db->prepare('
			select
					w.WORKOUT_ID,
					w.WORKOUT_DATE,
					w.TITLE,
					w.AUTHOR,
					w.SLUG,
					w.BACKBLAST_URL,
					ao_agg.AO_IDS,
					ao_agg.AO_DESCRIPTIONS AS AO,
					q_agg.Q_IDS,
					q_agg.Q_NAMES AS Q,
					COUNT(mp.F3_NAME) AS PAX_COUNT
			from
					WORKOUT w
			-- Subquery 1: Aggregate Qs
			left outer join
					(
							select
									wq.WORKOUT_ID,
									GROUP_CONCAT(mq.MEMBER_ID SEPARATOR \', \') AS Q_IDS,
									GROUP_CONCAT(mq.F3_NAME SEPARATOR \', \') AS Q_NAMES
							from
									WORKOUT_Q wq
							inner join
									MEMBER mq ON wq.MEMBER_ID = mq.MEMBER_ID
							group by
									wq.WORKOUT_ID
					) q_agg ON w.WORKOUT_ID = q_agg.WORKOUT_ID
			-- Subquery 2: Aggregate AOs
			left outer join
					(
							select
									wao.WORKOUT_ID,
									GROUP_CONCAT(ao.AO_ID SEPARATOR \', \') AS AO_IDS,
									GROUP_CONCAT(ao.DESCRIPTION SEPARATOR \', \') AS AO_DESCRIPTIONS,
									GROUP_CONCAT(ao.SLUG SEPARATOR \', \') AS AO_SLUGS
							from
									WORKOUT_AO wao
							inner join
									AO ao ON wao.AO_ID = ao.AO_ID
							group by
									wao.WORKOUT_ID
					) ao_agg ON w.WORKOUT_ID = ao_agg.WORKOUT_ID
			-- Join for PAX count
			left outer join
					WORKOUT_PAX wp ON w.WORKOUT_ID = wp.WORKOUT_ID
			left outer join
					MEMBER mp ON wp.MEMBER_ID = mp.MEMBER_ID
			where
					w.WORKOUT_DATE between ? and ?
			group by
					w.WORKOUT_ID,
					w.WORKOUT_DATE,
					w.TITLE,
					w.AUTHOR,
					w.SLUG,
					w.BACKBLAST_URL,
					ao_agg.AO_IDS,
					ao_agg.AO_DESCRIPTIONS,
					ao_agg.AO_SLUGS,
					q_agg.Q_IDS,
					q_agg.Q_NAMES
			order by
					w.WORKOUT_DATE DESC,
					ao_agg.AO_DESCRIPTIONS ASC
			limit ? offset ?
		');
		$stmt->execute([$startDate, $endDate, $limit, $offset]);

		return $stmt->fetchAll();
	}

	private function replaceFindByPluralPlaceholders($joins, $whereClauses): string {
		$query = '
			select
					w.WORKOUT_ID,
					w.WORKOUT_DATE,
					w.TITLE,
					w.AUTHOR,
					w.SLUG,
					w.BACKBLAST_URL,
					ao_agg.AO_IDS,
					ao_agg.AO_DESCRIPTIONS AS AO,
					q_agg.Q_IDS,
					q_agg.Q_NAMES AS Q,
					pax_count_agg.PAX_COUNT
			from
					WORKOUT w
			-- 1. Filter Workouts by the specified PAX (This join is REQUIRED)
			inner join
					::{JOINS}::
			-- 2. Aggregate Qs
			left outer join
					(
							select
									wq.WORKOUT_ID,
									GROUP_CONCAT(mq.MEMBER_ID SEPARATOR \', \') AS Q_IDS,
									GROUP_CONCAT(mq.F3_NAME SEPARATOR \', \') AS Q_NAMES
							from
									WORKOUT_Q wq
							inner join
									MEMBER mq ON wq.MEMBER_ID = mq.MEMBER_ID
							group by
									wq.WORKOUT_ID
					) q_agg ON w.WORKOUT_ID = q_agg.WORKOUT_ID
			-- 3. Aggregate AOs
			left outer join
					(
							select
									wao.WORKOUT_ID,
									GROUP_CONCAT(ao.AO_ID SEPARATOR \', \') AS AO_IDS,
									GROUP_CONCAT(ao.DESCRIPTION SEPARATOR \', \') AS AO_DESCRIPTIONS,
									GROUP_CONCAT(ao.SLUG SEPARATOR \', \') AS AO_SLUGS
							from
									WORKOUT_AO wao
							inner join
									AO ao ON wao.AO_ID = ao.AO_ID
							group by
									wao.WORKOUT_ID
					) ao_agg ON w.WORKOUT_ID = ao_agg.WORKOUT_ID
			-- 4. Get the Total PAX Count for the Workout
			left outer join
					(
							select
									WORKOUT_ID,
									COUNT(MEMBER_ID) AS PAX_COUNT
							from
									WORKOUT_PAX
							group by
									WORKOUT_ID
					) pax_count_agg ON w.WORKOUT_ID = pax_count_agg.WORKOUT_ID
			where
					::{WHERE_CLAUSE}::
			group by
					w.WORKOUT_ID,
					w.WORKOUT_DATE,
					w.TITLE,
					w.AUTHOR,
					w.SLUG,
					w.BACKBLAST_URL,
					ao_agg.AO_IDS,
					ao_agg.AO_DESCRIPTIONS,
					ao_agg.AO_SLUGS,
					q_agg.Q_IDS,
					q_agg.Q_NAMES,
					pax_count_agg.PAX_COUNT
			order by 
					w.WORKOUT_DATE DESC,
					ao_agg.AO_DESCRIPTIONS ASC
		';

		$query = str_replace('::{JOINS}::', $joins, $query);
		$query = str_replace('::{WHERE_CLAUSE}::', $whereClauses, $query);

		return $query;
	}	
	
	public function findAllByAO($aoId, $limit = 20, $offset = 0): array {
		$query = $this->replaceFindByPluralPlaceholders(
			joins: 'WORKOUT_AO wao_filter ON w.WORKOUT_ID = wao_filter.WORKOUT_ID', 
			whereClauses: 'wao_filter.AO_ID = ?');
		
		$query .= ' limit ? offset ?';

		$stmt = $this->db->prepare($query);
		$stmt->execute([$aoId, $limit, $offset]);

		return $stmt->fetchAll();
	}

	public function findAllByAoDescription($name, $limit = 20, $offset = 0): array {
		$query = $this->replaceFindByPluralPlaceholders(
			joins: 'WORKOUT_AO wao_filter ON w.WORKOUT_ID = wao_filter.WORKOUT_ID JOIN AO ao_filter ON wao_filter.AO_ID = ao_filter.AO_ID', 
			whereClauses: 'upper(ao_filter.DESCRIPTION) = upper(?)');
		
		$query .= ' limit ? offset ?';

		$stmt = $this->db->prepare($query);
		$stmt->execute([$name, $limit, $offset]);

		return $stmt->fetchAll();
	}

	public function findAllByAoSlug($slug, $limit = 20, $offset = 0): array {
		$query = $this->replaceFindByPluralPlaceholders(
			joins: 'WORKOUT_AO wao_filter ON w.WORKOUT_ID = wao_filter.WORKOUT_ID JOIN AO ao_filter ON wao_filter.AO_ID = ao_filter.AO_ID', 
			whereClauses: 'upper(ao_filter.SLUG) = upper(?)');
		
		$query .= ' limit ? offset ?';

		$stmt = $this->db->prepare($query);
		$stmt->execute([$slug, $limit, $offset]);

		return $stmt->fetchAll();
	}

	public function findAllByQ($qId) {
		$query = $this->replaceFindByPluralPlaceholders(
			joins: 'WORKOUT_Q wq_filter ON w.WORKOUT_ID = wq_filter.WORKOUT_ID', 
			whereClauses: 'wq_filter.MEMBER_ID = ?');
			
		$stmt = $this->db->prepare($query);
		$stmt->execute([$qId]);

		return $stmt->fetchAll();
	}
	
	public function findAllByPax($paxId) {
		$query = $this->replaceFindByPluralPlaceholders(
			joins: 'WORKOUT_PAX wp_filter ON w.WORKOUT_ID = wp_filter.WORKOUT_ID', 
			whereClauses: 'wp_filter.MEMBER_ID = ?');
			
		$stmt = $this->db->prepare($query);
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
	
	public function findRecentWorkoutAttendeesByAO($aoId, $numMonths) {
		$stmt = $this->db->prepare('
			select w.WORKOUT_ID, w.WORKOUT_DATE, mp.MEMBER_ID as MEMBER_ID, mp.F3_NAME as PAX from WORKOUT w
			left outer join WORKOUT_PAX wp on w.WORKOUT_ID = wp.WORKOUT_ID
			left outer join MEMBER mp on wp.MEMBER_ID = mp.MEMBER_ID
			left outer join WORKOUT_AO wao on w.WORKOUT_ID = wao.WORKOUT_ID
			left outer join AO ao on wao.AO_ID = ao.AO_ID
			where w.WORKOUT_ID in (
				select w.WORKOUT_ID from WORKOUT w
					left outer join WORKOUT_AO wao on w.WORKOUT_ID = wao.WORKOUT_ID
					left outer join AO ao on wao.AO_ID = ao.AO_ID
					where ao.AO_ID = ?
					and w.WORKOUT_DATE >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
			)
			order by w.WORKOUT_DATE desc, PAX asc
		');

		$stmt->execute([$aoId, $numMonths]);
			
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
			select AO_ID, DESCRIPTION, SLUG from AO where AO_ID=?
		');
		$stmt->execute([$aoId]);
		
		return $stmt->fetch();
	}

	public function findAoBySlug($slug) {
		$stmt = $this->db->prepare('
			select AO_ID, DESCRIPTION, SLUG from AO where upper(SLUG) = upper(?)
		');
		$stmt->execute([$slug]);
		
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
		select wc.AO_ID, wc.DESCRIPTION, wc.SLUG, avg(wc.count) as AVERAGE from (
			select wa.AO_ID, ao.DESCRIPTION, ao.SLUG, count(*) as count from WORKOUT_PAX wp
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
			group by wc.AO_ID, wc.DESCRIPTION, wc.SLUG
			order by AVERAGE desc
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
	
	public function findTopQsByAO($aoId, $count, $offset) {
		$stmt = $this->db->prepare('
			select ao.DESCRIPTION as AO, m.F3_NAME as Q, count(wq.WORKOUT_ID) as Q_COUNT from WORKOUT_Q wq
				join MEMBER m on wq.MEMBER_ID = m.MEMBER_ID
				join WORKOUT_AO wa on wq.WORKOUT_ID = wa.WORKOUT_ID
				join AO ao on wa.AO_ID = ao.AO_ID
				where ao.AO_ID = ?
				group by wq.MEMBER_ID, wa.AO_ID
				order by Q_COUNT desc, Q asc
				limit ? offset ?;
		');

		$stmt->execute([$aoId, $count, $offset]);
		
		return $stmt->fetchAll();
	}

	public function findTopPaxByAO($aoId, $count, $offset) {
		$stmt = $this->db->prepare('
			select ao.DESCRIPTION as AO, m.F3_NAME as PAX, count(wp.WORKOUT_ID) as PAX_COUNT from WORKOUT_PAX wp
				join MEMBER m on wp.MEMBER_ID = m.MEMBER_ID
				join WORKOUT_AO wa on wp.WORKOUT_ID = wa.WORKOUT_ID
				join AO ao on wa.AO_ID = ao.AO_ID
				where ao.AO_ID = ?
				group by wp.MEMBER_ID, wa.AO_ID
				order by PAX_COUNT desc, PAX asc
				limit ? offset ?;		
		');

		$stmt->execute([$aoId, $count, $offset]);
		
		return $stmt->fetchAll();
	}

	public function findMostRecentWorkoutDate() {
		$stmt = $this->db->prepare('
			select max(WORKOUT_DATE) as MAX_DATE 
			from WORKOUT w
			where w.WORKOUT_DATE <= NOW()
		');
		$stmt->execute();
		
		return $stmt->fetch()["MAX_DATE"];
	}
	
	public function save($title, $author, $slug, $dateArray, $url): bool|string {
		$stmt = $this->db->prepare(query: '
			insert into WORKOUT(TITLE, AUTHOR, SLUG, WORKOUT_DATE, BACKBLAST_URL) values (?, ?, ?, ?, ?)
		');
		
		$dateStr = $this->getDateString(dateArray: $dateArray);

		$stmt->execute(params: [$title, $author, $slug, $dateStr, $url]);
		
		return $this->db->lastInsertId();
	}
	
	public function saveWorkoutDetails($workoutId, $body): void {
		$stmt = $this->db->prepare(query: '
			insert into WORKOUT_DETAILS(WORKOUT_ID, HTML_CONTENT) values (?, ?)
		');
		
		$stmt->execute(params: [$workoutId, $body]);
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
	
	public function update($workoutId, $title, $author, $slug, $dateArray, $url): void {
		$stmt = $this->db->prepare(query: '
			update WORKOUT set TITLE=?, AUTHOR=?, SLUG=?, WORKOUT_DATE=?, BACKBLAST_URL=?
				where WORKOUT_ID=?
		');
		
		$dateStr = $this->getDateString(dateArray: $dateArray);
		
		$stmt->execute(params: [$title, $author, $slug, $dateStr, $url, $workoutId]);
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
