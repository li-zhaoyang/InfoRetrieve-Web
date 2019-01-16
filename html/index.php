<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
  require_once('SpellCorrector.php');
  require_once('simple_html_dom.php');
  // echo SpellCorrector::correct('octabr');
?>

<?php

function printc( $data ) {
    $output = $data;
    if ( is_array( $output ) )
        $output = implode( ',', $output);

    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}


// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;



if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/csci572/');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  // $additionalParameters = array( 'fl' => 'id,title,og_url,description', 'sort' => 'pageRankFile desc' );
  $additionalParameters = array( 'fl' => 'id,title,og_url,description');
  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    $results = $solr->search($query, 0, $limit, $additionalParameters);
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
    <title>PHP Solr Client Example</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js">        </script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>    
<script src="http://netsh.pp.ua/upwork-demo/1/js/typeahead.js"></script>
        <script>
    $(document).ready(function(){
    $('input.q').typeahead({
        name: 'q',
        remote:'suggest.php?key=%QUERY',
        limit : 10
    });
    });
    console.log("here")
    
    </script>
    <link href="typeaheadjs.css" rel="stylesheet">
  </head>

  <body>
    <form  accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input id="q" name="q" class="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/><input type="submit"/>
      
    </form>
<?php
  // might show corrected query term
    $words = explode(" ", $query);

    $correctPhrase = "";
    $corrected = false;

    foreach ($words as $word) {

        $correctWord = SpellCorrector::correct($word);

        $correctPhrase .= " ";

        $correctPhrase .= $correctWord;

        if ($word != $correctWord) $corrected = true;

    }
    if ($corrected) {
    $correctQuery = htmlspecialchars($correctPhrase, ENT_QUOTES, 'utf-8');
?>  
	<p>Do you mean <a href="?q=<?php echo $correctPhrase; ?>"><?php echo $correctQuery; ?></a>?</p>
<?php
    }

?>
<?php

// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>

    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
?>
      <li>
        <table style="border: 1px solid black; text-align: left">
<?php
    $url = "about:blank";
    foreach ($doc as $field => $value)
    {
      if ($field == 'og_url') {
        $url = $value;
      }
    }
    printc($url);
?>

<?php
	//generate snippets
    $text = file_get_html($url)->plaintext;
    $sentences = preg_split( "/(\.|\n)/", $text );
    $words = explode(" ", $query);
    // echo $sentences[0]
	$found = false;
	$snippet = "";
	
	//try to find whole phrase
    foreach ($sentences as $sentence) {
		if (stripos($sentence, $query) !== false) {
		    $found = true;
			$snippet = $sentence;
			break;
		}
	  }
	if (!$found) {
		//if whole phrase not found, try to find first sentence that contains all words in phrase
		foreach ($sentences as $sentence) {
			$allFound = true;
			foreach ($words as $word) {
				if (stripos($sentence, $word) == false) {
					$allFound = false;
					break;
				}
			if ($allFound) {
				$found = true;
				$snippet = $sentence;
				break;
			}
		}
  }
  }
	if (!$found) {
		//if still not found, try to find first sentence that contains all phrase.
		foreach ($sentences as $sentence) {
			$foundOne = false;
			foreach ($words as $word) {
				if (stripos($sentence, $word) !== false) {
					$foundOne = true;
					$found = true;
					$snippet = $sentence;
					break;
				}
			}
			if ($foundOne = true) break;
		}
	}
	if (strlen($snippet) > 160) {
		$snippet = substr($snippet, -160);
	}

?>
<?php
    // iterate document fields / values
    foreach ($doc as $field => $value)
    {
?>
          <tr>
            <th><?php echo htmlspecialchars($field=='og_url'?'url':$field, ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php 
              if ($field == 'title' || $field == 'og_url') {
                $out = htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); 
                echo "<a href='$url'>$out</a>";
              } else {
                echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); 
              }
            ?></td>
          </tr>
<?php
    }
?>
<?php
	if ($found) {
?>
		<tr>
		<th>Snippet</th>
		<td><?php echo $snippet; ?></td>
		</tr>	
<?php
	}
?>
        </table>
      </li>
<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>

