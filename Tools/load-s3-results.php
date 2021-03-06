<?php 

define("FFMPEG_PATH", "/opt/local/bin/ffmpeg");	// path of ffmpeg

define("DB_HOST", "50.116.6.114");	// MySQL host name
define("DB_USERNAME", "annotation-user");	// MySQL username
define("DB_PASSWD", "3APGj4vGmdWcQ6fy");	// MySQL password
define("DB_NAME", "HowtoAnnotation");	// MySQL database name. vt.sql uses the default video_learning name. So be careful.

$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWD, DB_NAME);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$entries = array_merge(file("real/s3_1/external_hit.results"), file("real/s3_2/external_hit.results"));
echo "total: " . sizeof($entries) . " entries<br>";

?>
<html>
<head>
<link rel="stylesheet" href="js/bootstrap.min.css" type="text/css" />
<script src="js/jquery-1.7.2.min.js"></script>	
<script src="js/sorttable.js"></script>
	<style type="text/css">
		table, td {
			border: 1px solid black;
		}
	</style>
<script>
	sorttable.makeSortable(document.getElementById('dataTable'));
</script>
</head>
<body>
	<table class="sortable" id="dataTable">
		<tr>
			<th class="sorttable_nosort">ID</th>
			<th>Worker ID</th>
			<th>Video ID</th>
			<th>Video Slug</th>
			<th>Cluster ID</th>
			<th>Bef Index</th>
			<th>Aft Index</th>
			<th>Bef noop</th>
			<th>Aft noop</th>
		</tr>
<?php
	$vids = array();
	$labels = array();
	$result = $mysqli->query("SELECT * FROM stage2_3");
	while($responses = $result->fetch_assoc()){
		$vids[$responses["id"]] = array(
			"vid" => $responses["video_id"],
			"label" => $responses["det_label"],
			"time" => $responses["det_label_index"]
			);
		// $vid = $responses["video_id"];
		// echo "<td>" . $vid . "</td>";
		// echo "<td>" . $labels . "</td>";
	}




$mysqli = new mysqli(DB_HOST, "toolscape-user", "G8hsDe5r4jDtFAYa", "video_learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$video_data = array();
$result = $mysqli->query("SELECT * FROM videos WHERE in_chi2014_set<>''");
// if ($result->num_rows != 1)
// 	echo "query error";
// $video = $result->fetch_assoc();
while($responses = $result->fetch_assoc()){
	$video_data[$responses["in_chi2014_set"]] = array(
		"video_id" => $responses["in_chi2014_set"],
		"slug" => $responses["slug"],
		"duration" => $responses["duration"],
		"title" => $responses["title"],
		"url" => $responses["url"]
	);
	// $vid = $responses["video_id"];
	// echo "<td>" . $vid . "</td>";
	// echo "<td>" . $labels . "</td>";
}


$count = 0;
foreach($entries as $i => $entry) {	
	// 30: video ID
	// 31 Answer.allAfterIndices	NOT USED
	// 33 Answer.allBeforeIndices	NOT USED
	// 35 Answer.before-noop	
	// 32 Answer.beforeIndex	
	// 29 Answer.afterIndex	
	// 34 Answer.after-noop

	// Deprecated!	
	// 30: cluster ID
	// 31 Answer.allAfterIndices	NOT USED
	// 32 Answer.allBeforeIndices	NOT USED
	// 33 Answer.before-noop	
	// 34 Answer.beforeIndex	
	// 29 Answer.afterIndex	
	// 35 Answer.after-noop


	$output_string = "";
	$data = explode("\t", $entry);
	if ($data[19] == "\"workerid\"") // ignore header
		continue;

	$count = $count + 1;
	// filter wrong results. skip if rere
	// if ($data[19] == "\"workerid\"" || $data[28] == "\"y\"" || $data[19] == "" || in_array($data[19], $blackList))
	// 	continue;
	// removing quotes
	$worker_id = substr($data[19], 1, -1);
	$cluster_id = substr($data[30], 1, -1);
	$after_index = substr($data[29], 1, -1);
	$before_index = substr($data[32], 1, -1);
	$after_noop = substr($data[34], 1, -1);
	$before_noop = substr($data[35], 1, -2);

	// $labels = explode(",", substr($data[30], 1, -2)); // getting rid of quotes and split
	// $labels_result = "";
	// $labels_result_file = "";
	$video_id = substr($vids[$cluster_id]["vid"], 3, 7);
	// echo $video_id;
	// if (!isset ($entry_array[$vid])){
	// 	$entry_array[$vid] = array();
	// 	$entry_array[$vid]["count"] = 1;
	// } else
	// 	$entry_array[$vid]["count"] = $entry_array[$vid]["count"] + 1;	
	echo "<tr><td>{$count}</td><td>{$worker_id}</td><td>{$video_id}</td><td>{$video_data[$video_id]['slug']}</td>" .
		"<td>{$cluster_id}</td>" .
		"<td>" . intval($before_index) . "</td>" .
		"<td>" . intval($after_index) . "</td>" .
		"<td>{$before_noop}</td>" .
		"<td>{$after_noop}</td>";	
	echo "</tr>";

	if ($before_noop != "on")
		$before_noop = "off";
	if ($after_noop != "on")
		$after_noop = "off";
	// $data[32] = trim(preg_replace('/\s+/', ' ', $data[32]));
	$output_string = $count . "\t\t" . 
					$worker_id . "\t\t" .
					$video_id . "\t\t" .
					$video_data[$video_id]['slug'] . "\t\t" .
					$cluster_id . "\t\t" .
					$vids[$cluster_id]["vid"] . "\t\t" . 
					$vids[$cluster_id]["time"] . "\t\t" . 
					$vids[$cluster_id]["label"] . "\t\t" .
					intval($before_index) . "\t\t" .
					intval($after_index) . "\t\t" .
					$before_noop . "\t\t" .
					$after_noop . "\t\t" .
					"\n";
	file_put_contents("s3.data", $output_string, FILE_APPEND);
}

?>
	</table>
</body>
</html>