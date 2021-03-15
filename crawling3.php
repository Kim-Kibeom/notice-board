<html>
<body>
<meta charset='utf-8'>
<?php
//학사 공지 크롤러
include('simplehtmldom_1_9_1/simple_html_dom.php');
include('Snoopy-2.0.0.tar.gz/Snoopy.class.php');

$hostname = "localhost";
$username = "cse20151521";
$password = "cse20151521";
$dbname = "db_cse20151521";

$connect = mysqli_connect($hostname, $username, $password, $dbname);
mysqli_set_charset($connect, "utf8");

$sql = "drop table if exists rawData";
$connect->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS rawData (
    domain VARCHAR(50) NOT NULL,
    title VARCHAR(150) NOT NULL,
    url VARCHAR(250) NOT NULL,
    date VARCHAR(50) NOT NULL,
    keywords VARCHAR(100) NOT NULL
)";


if($connect->query($sql) === TRUE){
    //echo "<h1> Image detection result </h1><br>";
}
else {
    //echo "Table creation failed<br>";
}

$damain = $title = $link = $date = $keywords = "";
$stmt = $connect->prepare("INSERT INTO rawData (domain, title, url, date, keywords) values (?,?,?,?,?)");
$stmt->bind_param("sssss", $domain, $title, $link, $date, $keywords);

//장학 일반 학사 코로나
$urls = [
    'http://sogang.ac.kr/front/boardlist.do?currentPage=%d&menuGubun=1&siteGubun=1&bbsConfigFK=2&searchField=ALL&searchValue=&searchLowItem=ALL'
];

$domain = '학사';

for ($i = 0; $i < 1; $i++){
    //echo $domains[$i]."<br>";
    $page = 1;
    while(1){
        $url = sprintf($urls[$i], $page);

        $snoopy = new Snoopy;
        //$snoopy->agent = $_SERVER['HTTP_USER_AGENT'];
        //$snoopy->referer = $url;
        $snoopy->fetch($url);
        $result = $snoopy->results;

        $linkRex = '/"\/front\/boardview([^"]+)"/';
        preg_match_all($linkRex, $result, $links);
        $titleRex = '/<span class="text">(.*?)<\/span>/is';
        preg_match_all($titleRex, $result, $titles);
        $dateRex = '/([0-9]{4})\.([0-9]{2})\.([0-9]{2})/';
        preg_match_all($dateRex, $result, $dates);
        $emptyRex = '/<tr class="empty">(.*?)<\/tr>/is';
        preg_match_all($emptyRex, $result, $emptys);

        if(count($emptys[0]) != 0) break;

        $flag = false;
        //$domain = $domains[$i];
        for($idx = 0; $idx < count($links[0]); $idx++){
            $link = $links[0][$idx];
            $link = '"http://www.sogang.ac.kr'.substr($link,1);
            
            $title = $titles[0][$idx];
            $title = substr($title, 19, count($title)-8);
            $temp = $title;

            # cooking start 
            $temp = preg_replace('/[0-9]+/',"", $temp);
            $temp = str_replace(",", "", $temp, $count);
            $outlist = ['학년도','｣', '｢', ']','(', ')', '-', '_', '.', '//', '[', '★', '및', '를', ':', '중', '\'', '\'','・','‘','’','』','『', '~', '/', '+', 
            '년', '차', '만원', '$','까지', '&#', '에', '하는', '%', '&quot;','&',',','·','으로','|'];
            for($outno = 0; $outno < count($outlist); $outno++){
                $temp = str_replace($outlist[$outno], " ", $temp, $count);
            }
            $temp = explode(" ", $temp);
            $out = [];
            for($k = 0 ; $k < count($temp); $k++){
                if($temp[$k] != "") $out[] = $temp[$k];
            }
           
            $keywords = ""; 
            for($j = 0; $j < count($out); $j++){
                $keywords = $keywords." ".$out[$j];
                $suggestion[] = $out[$j];
            }
            # cooking end

            $date = $dates[0][$idx];
            if($page != 1 && substr($date, 0, 4) != "2020"){
                $flag = true;
                break;
            }
            
            $stmt->execute();
        }
        
        if($flag == true) break;
        //echo "<br>";
        $page++;
    }
}

$suggestion = array_unique($suggestion);
$str_suggestion = implode(' ', $suggestion);

$file = fopen("forAjax.txt", "a");
fwrite($file, $str_suggestion);
fclose($file);

$stmt->close();
mysqli_close($connect);
?>
</body>
</html>