<?php
$file = fopen("forAjax.txt", "r");
$suggestion = fread($file, filesize("forAjax.txt"));
fclose($file);

$suggestion = explode(" ", $suggestion);
$suggestion = array_unique($suggestion);

for($i = 0;  $i < count($suggestion); $i++){
    if(mb_strlen($suggestion[$i],'utf-8') <= 1) unset($suggestion[$i]);
}
$input = $_REQUEST["input"];

$hint = "";

if($input != ""){
    $len = strlen($input);
    foreach($suggestion as $s){
        if(stristr(substr($s, 0, $len), $input)){
            if($hint ===""){
                $hint .= "$s";
            } else{
                $hint .= ", $s";
            }
        }
    }
}

echo $hint === "" ? "일치하는 결과가 없습니다." : $hint;
?>
