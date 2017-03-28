<?php require_once('db.php'); ?>

<?php

class WorkoutRepository {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getDatabase();
    }

    public function find($id) {
        $stmt = $this->db->prepare('
            select w.WORKOUT_ID, w.WORKOUT_DATE, w.TITLE, ao.DESCRIPTION as AO, mq.F3_NAME as Q, mp.F3_NAME as PAX from WORKOUT w
	        join WORKOUT_PAX wp on w.WORKOUT_ID = wp.WORKOUT_ID
                join MEMBER mq on w.Q = mq.MEMBER_ID
                join MEMBER mp on wp.MEMBER_ID = mp.MEMBER_ID
                left outer join WORKOUT_AO wao on w.WORKOUT_ID = wao.WORKOUT_ID
                left outer join AO ao on wao.AO_ID = ao.AO_ID
                where w.WORKOUT_ID=?');
        $stmt->execute([$id]);
        
        return $stmt->fetchAll();
    }

    public function findAll() {
        $stmt = $this->db->query('
            select w.WORKOUT_ID, w.WORKOUT_DATE, w.TITLE, w.BACKBLAST_URL, ao.DESCRIPTION as AO, mq.F3_NAME as Q, count(mp.F3_NAME) as PAX from WORKOUT w
                join WORKOUT_PAX wp on w.WORKOUT_ID = wp.WORKOUT_ID
                join MEMBER mq on w.Q = mq.MEMBER_ID
                join MEMBER mp on wp.MEMBER_ID = mp.MEMBER_ID
                left outer join WORKOUT_AO wao on w.WORKOUT_ID = wao.WORKOUT_ID
                left outer join AO ao on wao.AO_ID = ao.AO_ID
                group by w.WORKOUT_ID, ao.DESCRIPTION
                order by w.WORKOUT_DATE desc, ao.DESCRIPTION asc');
        
        return $stmt->fetchAll();
    }
}


?>