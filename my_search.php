<?php
header("Content-Type: application/json");
//ini_set('display_errors',1); 
//error_reporting(E_ALL);

$hostname = "localhost";
$username = "cse20151521";
$password = "cse20151521";
$dbname = "db_cse20151521";

$domain = [ '장학', '일반', '학사', '코로나' ];

$connect = mysqli_connect($hostname, $username, $password, $dbname);
mysqli_set_charset($connect, "utf8");

$input=$_GET['input'];
$q = $_SERVER['QUERY_STRING'];
parse_str($q, $arr);
while(strpos($q, 'ckb')!= false){
    $pos = strpos($q, 'ckb');
    $cont = $q[$pos+4];
    $domains[] = $domain[(int)$cont-1];
    $q = substr($q, $pos+1);
}

$sample_sql = "SELECT * FROM rawData WHERE domain='%s' AND keywords LIKE '%s%s%s'";

for($i = 0; $i < count($domains); $i++){
    $sql = sprintf($sample_sql, $domains[$i], '%', $input, '%');
    $res = $connect->query($sql) or die("wrong query");
    if(!($res->num_rows > 0)) {
        $result['domain'] = $domains[$i];
        $result['title'] = "검색 결과가 없습니다.";
        $result['date'] = "";
        $out[$domains[$i]][] = $result;
        continue;
    } 
    while($row = $res->fetch_assoc()){
        $result = [];
        $result['domain'] = $row['domain'];
        $result['title'] = "<a href=".$row['url']." target=\"_blank\">".$row['title']."</a>";
        $result['date'] = $row['date'];
        
        $out[$domains[$i]][] = $result;
    }
}

echo json_encode($out);
?>