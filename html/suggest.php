<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    // echo "hello";
    include 'SpellCorrector.php';

    $key=$_GET['key'];
    $words = explode(" ", $key);
    $correctPhrase = "";
    $corrected = false;
    foreach ($words as $word) {
        $correctWord = SpellCorrector::correct($word);
        $correctPhrase .= " ";
        $correctPhrase .= $correctWord;
        if ($word != $correctWord) $corrected = true;
    }

    if (strlen($correctPhrase) >= 1) {
        $correctPhrase = substr($correctPhrase, 1);
    }

    $array = array();


    if ($corrected) $array[] = $correctPhrase;
    //get from auto complete server, constuct query

    $lastWord = array_pop($words);
    $beforeWords = "";
    foreach ($words as $word) {
        $beforeWords .= " ";
        $beforeWords .= $word;
    }

    if (strlen($beforeWords) >= 1) {
        $beforeWords = substr($beforeWords, 1);
    }

    $url = "http://localhost:8983/solr/csci572/suggest";
    $data = array('q' => $lastWord); 

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) { /* Handle error */ }

    $resultObj = json_decode($result, true);
    foreach ($resultObj["suggest"]["suggest"][$lastWord]["suggestions"] as $suggestion) {
        $array[] = $beforeWords . " " . $suggestion["term"];
    }

    // TODO limit number before return

    echo json_encode($array);
?>
