<?php require_once('db.php'); ?>

<?php

class MemberRepository {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getDatabase();
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
        $stmt->execute([strtoupper($f3Name), strtoupper($f3Name)]);
        
        return $stmt->fetch();
    }

    /**
     * Inserts the user into the database and returns an object with the meber id and f3name
     */
    public function save($f3name) {
        $stmt = $this->db->prepare('
            insert into MEMBER(F3_NAME) values (?)'
        );
        $stmt->execute([$f3Name]);
        
        return (object) array('memberId' => $pdo->lastInsertId(), 'f3Name' => $f3Name);
    }
}


?>