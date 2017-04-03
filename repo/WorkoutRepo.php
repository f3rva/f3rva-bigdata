<?php
namespace F3\Repo;
require_once('Database.php'); 

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
    
    public function find($id) {
        $stmt = $this->db->prepare('
            select w.WORKOUT_ID, w.WORKOUT_DATE, w.TITLE, w.BACKBLAST_URL, ao.AO_ID, ao.DESCRIPTION as AO, mq.F3_NAME as Q from WORKOUT w
                left outer join MEMBER mq on w.Q = mq.MEMBER_ID
                left outer join WORKOUT_AO wao on w.WORKOUT_ID = wao.WORKOUT_ID
                left outer join AO ao on wao.AO_ID = ao.AO_ID
                where w.WORKOUT_ID=?
		');
        $stmt->execute([$id]);
        
        $result = $stmt->fetchAll();
        return $result;
    }

    public function findAll() {
        $stmt = $this->db->query('
            select w.WORKOUT_ID, w.WORKOUT_DATE, w.TITLE, w.BACKBLAST_URL, ao.AO_ID, ao.DESCRIPTION as AO, mq.F3_NAME as Q, count(mp.F3_NAME) as PAX_COUNT from WORKOUT w
                join WORKOUT_PAX wp on w.WORKOUT_ID = wp.WORKOUT_ID
                left outer join MEMBER mq on w.Q = mq.MEMBER_ID
                join MEMBER mp on wp.MEMBER_ID = mp.MEMBER_ID
                left outer join WORKOUT_AO wao on w.WORKOUT_ID = wao.WORKOUT_ID
                left outer join AO ao on wao.AO_ID = ao.AO_ID
                group by w.WORKOUT_ID, ao.AO_ID, ao.DESCRIPTION
                order by w.WORKOUT_DATE desc, ao.DESCRIPTION asc
		');
        
        return $stmt->fetchAll();
    }

    public function findPax($id) {
    	$stmt = $this->db->prepare('
			select wp.WORKOUT_ID, wp.MEMBER_ID, m.F3_NAME from WORKOUT_PAX wp
				join MEMBER m on wp.MEMBER_ID = m.MEMBER_ID
				where wp.WORKOUT_ID=?;
		');
    	$stmt->execute([$id]);
    	
    	return $stmt->fetchAll();
    }
    
    public function save($title, $qId, $url) {
    	$stmt = $this->db->prepare('
			insert into WORKOUT(TITLE, WORKOUT_DATE, Q, BACKBLAST_URL) values (?, NOW(), ?, ?)
		');
    	
    	$stmt->execute([$title, $qId, $url]);
    	
    	return $this->db->lastInsertId();
    }
    
    public function saveWorkoutMember($workoutId, $memberId) {
    	$stmt = $this->db->prepare('
			insert into WORKOUT_PAX(WORKOUT_ID, MEMBER_ID) values (?, ?)
		');
    	
    	$stmt->execute([$workoutId, $memberId]);
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
}

?>