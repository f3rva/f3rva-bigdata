<?php 

function initialize() {
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
    //$url = 'http://freegeoip.net/json/' . $_SERVER["REMOTE_ADDR"];
    //$url = 'http://www.example.com';
    //$url = 'http://f3nation.com/2017/03/14/the-duck/';
 
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
    
    // query to get the Q
    $qNode = $xpath->query("//ul/li/strong[text()='QIC:']")->item(0);
    $qStr = trim($qNode->nextSibling->nodeValue);
    
    // query to get the PAX
    $paxNode = $xpath->query("//ul/li/strong[text()='The PAX:']")->item(0);
    $paxStr = trim($paxNode->nextSibling->nodeValue);
    
    // query to get the tags
    $tags = $xpath->query('//div[@class="categories"]/a/text()');
    $tagsArray = array();
    foreach($tags as $tagNode){
        $tagsArray[] = $tagNode->nodeValue;
    }
    $tagsStr = implode("|", $tagsArray);
    
    // create an object to return;
    return (object) array('q' => $qStr, 'pax' => $paxStr, 'tags' => $tagsStr);
}

function create_response($data, $additionalInfo) {
    // desired format {"value1":"sarah","value2":"justin","value3":"bella"};
    $value1 = $data->post->author . "|" . $data->post->title . "|" . $data->post->url . "|" . $data->post->publishedDate;
    $value2 = $additionalInfo->q . "|" . $additionalInfo->pax;
    $value3 = $additionalInfo->tags;
    return (object) array('value1' => $value1, 'value2' => $value2, 'value3' => $value3);
}

function trigger_maker($data) {
    // post to maker to trigger the rest of the chain
    $url = "https://maker.ifttt.com/trigger/f3_backblast_posted/with/key/d2Ev9SwwKfCfvAGGcJnjsS";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); 
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($data))                                                                       
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $html = curl_exec($ch);
    //echo $html;
    curl_close($ch);
}

initialize();
$data = validateInput();
error_log('request: ' . json_encode($data));
$additionalInfo = parse_post($data->post->url);
$makerData = json_encode(create_response($data, $additionalInfo));
trigger_maker($makerData);
echo $makerData;

?>