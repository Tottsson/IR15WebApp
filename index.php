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
		error_reporting(-1);
		ini_set("display_errors", "On");

		require_once("include/SolrPHPClient/Apache/Solr/Service.php");

		$solr = new Apache_Solr_Service("ec2-52-5-117-168.compute-1.amazonaws.com", 8983, "/solr/gettingstarted_shard1_replica2");

		if (!$solr->ping()) {
			echo '<p class="error">The Solr service is not responding</p>';
		} else {
			$query = isset($_GET["query"]) ? trim($_GET["query"]) : "";

			echo '
				<form action="" method="get">
					<input type="text" name="query" value="' . $query . '" autofocus onfocus="this.value = this.value;" />
					<input type="submit" value="Search" />
				</form>
			';

			if (!empty($query)) {
				try {
					$results = $solr->search($query, 0, 100);

					if ($results->getHttpStatus() == 200) {
						//print_r($results->getRawResponse());

						$num_results = $results->response->numFound;

						if ($num_results > 0) {
							echo '<section class="results">';

							foreach ($results->response->docs as $doc) { 
								//echo "$doc->id $doc->title <br />";
								if (isset($doc->image) && !empty($doc->image)) {
									echo '<div class="image"><a href="' . $doc->image . '" target="_blank"><img src="' . $doc->image . '" alt="" /></a></div>';
								}
							}

							echo '</section>';
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