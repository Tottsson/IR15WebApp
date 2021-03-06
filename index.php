<?php
	require_once("include/SolrPHPClient/Apache/Solr/Service.php");

	error_reporting(-1);
	ini_set("display_errors", "On");

	define("IMAGES_PER_PAGE", 20);

	$page = isset($_GET["p"]) && is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>IR15: Image Search</title>
		
		<meta charset="utf-8" />
		
		<link rel="stylesheet" type="text/css" href="stylesheets/main.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Source+Sans+Pro">
		
		<script type="text/javascript" src="javascripts/main.js"></script>
	</head>
	
	<body>
		<h1>Image Search</h1>
<?php
		//$solr = new Apache_Solr_Service("ec2-52-5-117-168.compute-1.amazonaws.com", 8983, "/solr/gettingstarted_shard1_replica2");
		$solr = new Apache_Solr_Service("localhost", 8983, "/solr/ir15");

		if (!$solr->ping()) {
			echo '<p class="error">The Solr service is not responding</p>';
		} else {
			$query = isset($_GET["q"]) ? trim($_GET["q"]) : "";

			echo '
				<form action="" method="get">
					<input type="text" name="q" value="' . $query . '" autofocus onfocus="this.value = this.value;" />
					<input type="submit" value="Search" />
				</form>
			';

			if (!empty($query)) {
				try {
					$first_res = ($page - 1) * IMAGES_PER_PAGE;

					//$results = $solr->search($query, 0, 20);
					//$results = $solr->search($query . " AND url:[* TO *]", $first_res, IMAGES_PER_PAGE);
					$results = $solr->search(strtolower($query), $first_res, IMAGES_PER_PAGE);

					if ($results->getHttpStatus() == 200) {
						//print_r($results->getRawResponse());

						$num_results = $results->response->numFound;
						$num_pages = intval(($num_results - 1) / IMAGES_PER_PAGE) + 1;
						
						if ($num_results > 0) {
							echo '<section class="results">';

							foreach ($results->response->docs as $doc) { 
								//echo "$doc->id $doc->title <br />";

								if (isset($doc->url) && !empty($doc->url)) {
									echo '<div class="image"><a href="' . $doc->url . '" target="_blank"><img src="' . $doc->url . '" alt="" /></a></div>';
								}
							}

							echo '</section>';

							if ($num_pages > 1) {
								$url_query = urlencode($query);

								echo '<div class="pager">';
									if ($page > 1)					echo '<a href="?q=' . $url_query . '&amp;p=' . ($page - 1) . '">&larr;&nbsp;Previous</a>';
									else							echo '<span class="disabled">&larr;&nbsp;Previous</span>';
									
									if ($page - 2 > 1)				echo '<a href="?q=' . $url_query . '&amp;p=1">1</a>';
									if ($page - 3 > 1)				echo '<span class="disabled">...</span>';
									
									if ($page >= 3)					echo '<a href="?q=' . $url_query . '&amp;p=' . ($page - 2) . '">' . ($page - 2) . '</a>';
									if ($page >= 2)					echo '<a href="?q=' . $url_query . '&amp;p=' . ($page - 1) . '">' . ($page - 1) . '</a>';
									
									echo '<span>' . $page . '</span>';
									
									if ($page + 1 <= $num_pages)	echo '<a href="?q=' . $url_query . '&amp;p=' . ($page + 1 ) . '">' . ($page + 1) . '</a>';
									if ($page + 2 <= $num_pages)	echo '<a href="?q=' . $url_query . '&amp;p=' . ($page + 2 ) . '">' . ($page + 2) . '</a>';
									
									if ($page + 3 < $num_pages)		echo '<span class="disabled">...</span>';
									if ($page + 2 < $num_pages)		echo '<a href="?q=' . $url_query . '&amp;p=' . $num_pages . '">' . $num_pages . '</a>';
									
									if ($page < $num_pages)			echo '<a href="?q=' . $url_query . '&amp;p=' . ($page + 1) . '">Next&nbsp;&rarr;</a>';
									else							echo '<span class="disabled">Next&nbsp;&rarr;</span>';
								echo '</div>';
							}
						}
					} else {
						echo $results->getHttpStatusMessage();
					}
				} catch (Exception $e) {
					echo '<br /><span style="font-weight: bold;">Search exception:</span> ' . $e->__toString();
				}
			}
		}
?>

	</body>
</html>