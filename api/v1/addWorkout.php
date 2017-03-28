<?php 
define('__ROOT__', dirname(dirname(dirname(dirname(__FILE__))))); 
require(__ROOT__ . '/repo/memberRepo.php'); 
require(__ROOT__ . '/repo/workoutRepo.php'); 
?>

<?

function init_response() {
    // initialize
    header('Content-Type: application/json');
}

function validateInput() {
    // parse the input into json
    $jsonStr = file_get_contents('php://input');
    $json = json_decode($jsonStr);

    if ($json == null) {
        exit_error(400, 5400, "invalid input received: " . $jsonStr);
    }
    
    return $json;
}

function exit_error($status, $code, $message) {
    error_log('error: (' . $status . ') - ' . $code . ': ' . $message);
    http_response_code($status);
    echo '{ "errorCode": ', $code, ', "errorMessage": "', $message, '" }';
    exit(0);
}

function parse_post($url) {
    // call to get the contents of the post
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $html = curl_exec($ch);
    curl_close($ch);
 
    // parse the html contents to a DOM object
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $xpath = new DOMXPath($doc);
    
    // query to get the workout date
    $dateNode = $xpath->query("//ul/li/strong[text()='When:']")->item(0);
    $dateStr = trim($dateNode->nextSibling->nodeValue);
    
    // query to get the Q
    $qNode = $xpath->query("//ul/li/strong[text()='QIC:']")->item(0);
    $qStr = trim($qNode->nextSibling->nodeValue);
    
    // query to get the PAX
    $paxNode = $xpath->query("//ul/li/strong[text()='The PAX:']")->item(0);
    $paxStr = trim($paxNode->nextSibling->nodeValue);
    $paxArray = array_map('trim', explode(',', $paxStr));
    
    // query to get the tags
    $tags = $xpath->query('//div[@class="categories"]/a[@rel="tag"]/text()');
    $tagsArray = array();
    foreach($tags as $tagNode){
        $tagsArray[] = $tagNode->nodeValue;
    }
    
    // create an object to return;
    return (object) array('q' => $qStr, 'pax' => $paxArray, 'tags' => $tagsArray);
}

// select the member or add him if he doesn't exist
function select_add_member($f3Name) {
    $memberRepo = new MemberRepository();
    $memberResult = $memberRepo->findByF3NameOrAlias($f3name);
    
    // found
    if ($memberResult) {
        // found an existing member
        $member = (object) array('memberId' => $memberResult['MEMBER_ID'], 'f3Name' => $memberResult['F3_NAME']);
    }
    else {
        // not found, create
        $stmt = $pdo->prepare('insert into MEMBER(F3_NAME) values (?)');
        $stmt->execute([$f3Name]);
        
        $member = (object) array('memberId' => $pdo->lastInsertId(), 'f3Name' => $f3Name);
    }
    
    return $member;
}

// select the ao or add it if it doesn't exist
function select_add_ao($pdo, $aoDescription) {
    $stmt = $pdo->prepare('select AO_ID, DESCRIPTION from AO where upper(DESCRIPTION) = ?');
    $stmt->execute([strtoupper($aoDescription)]);
    $aoResult = $stmt->fetch();
    
    // found
    if ($aoResult) {
        // found an existing AO
        $ao = (object) array('aoId' => $aoResult['AO_ID'], 'description' => $aoResult['DESCRIPTION']);
    }
    else {
        // not found, create
        $stmt = $pdo->prepare('insert into AO(DESCRIPTION) values (?)');
        $stmt->execute([$aoDescription]);
        
        $ao = (object) array('aoId' => $pdo->lastInsertId(), 'description' => $aoDescription);
    }
    
    return $ao;
}

function add_workout_ao($pdo, $workoutId, $aoId) {
    $stmt = $pdo->prepare('insert into WORKOUT_AO(WORKOUT_ID, AO_ID) values (?, ?)');
    
    $stmt->execute([$workoutId, $aoId]);
}

function add_aos($pdo, $workoutId, $tags) {
    foreach ($tags as $tag) {
        $ao = select_add_ao($pdo, $tag);
        add_workout_ao($pdo, $workoutId, $ao->aoId);
    }
}

function add_workout_pax($pdo, $workoutId, $member) {
    $stmt = $pdo->prepare('insert into WORKOUT_PAX(WORKOUT_ID, MEMBER_ID) values (?, ?)');
    
    $stmt->execute([$workoutId, $member->memberId]);
}

function add_pax($pdo, $workoutId, $pax) {
    foreach ($pax as $paxMember) {
        $member = select_add_member($pdo, $paxMember);
        add_workout_pax($pdo, $workoutId, $member);
    }
}

function add_workout($pdo, $data, $additionalInfo) {
    // find and insert the q
    $q = select_add_member($pdo, $additionalInfo->q);
    // TODO:  if this failed we failed
    
    // insert the workout
    $stmt = $pdo->prepare('insert into WORKOUT(TITLE, WORKOUT_DATE, Q, BACKBLAST_URL) values (?, NOW(), ?, ?)');
    
    $stmt->execute([$data->post->title, $q->memberId, $data->post->url]);
    $workoutId = $pdo->lastInsertId();

    // add the aos
    add_aos($pdo, $workoutId, $additionalInfo->tags);
    
    // add the pax members
    add_pax($pdo, $workoutId, $additionalInfo->pax);
}

init_response();
$data = validateInput();
error_log('request: ' . json_encode($data));
$additionalInfo = parse_post($data->post->url);
error_log('additionalInfo: ' . json_encode($additionalInfo));

try {
    $pdo = init_db();
    $pdo->beginTransaction();
    
    add_workout($pdo, $data, $additionalInfo);
  
    $pdo->commit();
} catch(PDOException $ex) {
    $pdo->rollBack();
    error_log('rolled back: ' . $ex->getMessage());
}

echo json_encode($additionalInfo);

?>