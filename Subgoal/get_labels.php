<?
  define("DB_HOST", "50.116.6.114");    // MySQL host name
  define("DB_USERNAME", "annotation-user");    // MySQL username
  define("DB_PASSWD", "3APGj4vGmdWcQ6fy");    // MySQL password
  define("DB_NAME", "HowtoAnnotation");    // MySQL database name. vt.sql uses the default video_learning name. So be careful.

  $mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWD, DB_NAME);
  if ($mysqli->connect_errno) {
      echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
  }


  $video_id = $_GET["vid"];

$cid_list = array(56 => "c01_v01",57 => "c01_v02",58 => "c01_v03",59 => "c01_v04",60 => "c01_v05",
          61 => "c02_v01",62 => "c02_v02",63 => "c02_v03",64 => "c02_v04",65 => "c02_v05",
          66 => "c03_v01",67 => "c03_v02",68 => "c03_v03",69 => "c03_v04",70 => "c03_v05",
          71 => "c04_v01",72 => "c04_v02",73 => "c04_v03",74 => "c04_v04",75 => "c04_v05",
          76 => "c05_v01",77 => "c05_v02",78 => "c05_v03",79 => "c05_v04",80 => "c05_v05");
$mid_list = array(81 => "m01_v01",82 => "m01_v02",83 => "m01_v03",84 => "m01_v04",85 => "m01_v05",
          86 => "m02_v01",87 => "m02_v02",88 => "m02_v03",89 => "m02_v04",90 => "m02_v05",
          91 => "m03_v01",92 => "m03_v02",93 => "m03_v03",94 => "m03_v04",95 => "m03_v05",
          96 => "m04_v01",97 => "m04_v02",98 => "m04_v03",99 => "m04_v04",100 => "m04_v05",
          101 => "m05_v01",102 => "m05_v02",103 => "m05_v03",104 => "m05_v04",105 => "m05_v05");
$pid_list = array(1  => "p01_v01",2  => "p01_v02",3  => "p01_v03",4  => "p01_v04",5  => "p01_v05",
          22 => "p02_v01",23 => "p02_v02",24 => "p02_v03",25 => "p02_v04",26 => "p02_v05",
          32 => "p03_v01",33 => "p03_v02",34 => "p03_v03",35 => "p03_v04",36 => "p03_v05",
          11 => "p04_v01",12 => "p04_v02",13 => "p04_v03",14 => "p04_v04",15 => "p04_v05",
          42 => "p05_v01",43 => "p05_v02",44 => "p05_v03",45 => "p05_v04",46 => "p05_v05");
  // echo substr($video_id, 0, 7);
  if ($video_id[0] == "c")
    $list = $cid_list;
  else if ($video_id[0] == "m")
    $list = $mid_list;
  else if ($video_id[0] == "p")
    $list = $pid_list;

  $key = array_search(substr($video_id, 3, 7), $list);
  $labels_array = array();
  $result = $mysqli->query("SELECT * FROM stage2_3 WHERE video_id LIKE '%$video_id%' ORDER BY det_label_index * 1 ASC");
  while ($row = $result->fetch_assoc()) {
    $labels_array[] = $row;   
  }
  $result->free();
  $mysqli->close();

  header('Content-Type: application/json');
  echo json_encode($labels_array);
?>