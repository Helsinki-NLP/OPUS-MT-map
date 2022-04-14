<?php



function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}


$srclang   = $_GET['src']    ? test_input($_GET['src'])    : 'deu';
$trglang   = $_GET['trg']    ? test_input($_GET['trg'])    : 'eng';
$benchmark = $_GET['test']   ? test_input($_GET['test'])   : 'flores101-devtest';
$metric    = $_GET['metric'] ? test_input($_GET['metric']) : 'bleu';
$langpair  = implode('-',[$srclang,$trglang]);


$leaderboard_url = 'https://raw.githubusercontent.com/Helsinki-NLP/OPUS-MT-leaderboard/master/scores';
$testsets = file(implode('/',[$leaderboard_url,'benchmarks.txt']));

foreach ($testsets as $testset){
    $parts = explode("\t",$testset);
    if ($parts[0] == $benchmark){
        echo("[$parts[0]]");
        $testlangs = rtrim($parts[1]);
    }
    else{
        $link = "index.php?src=$srclang&trg=$trglang&test=$parts[0]&metric=$metric";
        echo("[<a href=\"$link\">$parts[0]</a>]");
    }    
}



echo '<hr/>';
$langpairs = explode(' ',$testlangs);
if (sizeof($langpairs) > 20){
    $srclangs = array();
    $trglangs = array();
    foreach ($langpairs as $l){
        $langs = explode('-',$l);
        array_push($srclangs,$langs[0]);
        array_push($trglangs,$langs[1]);
    }
    $srclangs = array_unique($srclangs);
    $trglangs = array_unique($trglangs);
    echo('<table><tr><td>source:</td><td>');
    foreach ($srclangs as $l){
        if ($l == $srclang){
            echo("[$l]");
        }
        else{
            $link = "index.php?src=$l&trg=$trglang&test=$benchmark&metric=$metric";
            echo("[<a href=\"$link\">$l</a>]");
        }
    }
    echo('</td></tr><tr><td>target:</td><td>');
    foreach ($trglangs as $l){
        if ($l == $trglang){
            echo("[$l]");
        }
        else{
            $link = "index.php?src=$srclang&trg=$l&test=$benchmark&metric=$metric";
            echo("[<a href=\"$link\">$l</a>]");
        }
    }
    echo('</td></tr></table>');
}
// elseif (sizeof($langpairs) > 1){
else{
    echo('<table><tr><td>language pair:</td><td>');
    $invalid = true;
    foreach ($langpairs as $l){
        if ($l == $langpair){
            $invalid = false;
        }
        $langs = explode('-',$l);
        if (sizeof($langs) == 2){
            if ($l == $langpair){
                echo("[$l]");
            }
            else{
                $link = "index.php?src=$langs[0]&trg=$langs[1]&test=$benchmark&metric=$metric";
                echo("[<a href=\"$link\">$l</a>]");
            }
        }
    }
    echo('</td></tr></table>');
    if ( $invalid ){
        $oldlang = $langpair;
        $langpair = $langpairs[0];
        $parts = explode('-',$langpair);
        $srclang = $parts[0];
        $trglang = $parts[1];
        echo("Invalid language pair $oldlang for this benchmark: change to $langpair!");
    }
}



echo("<h1>OPUS-MT leaderboard</h1>");

$metrics = array('bleu', 'chrf');
$metriclinks = array();
foreach ($metrics as $m){
    if ($m != $metric){
        $metriclinks[$m] = "index.php?src=$srclang&trg=$trglang&test=$benchmark&metric=$m";
    }
}

echo("<ul>");
echo("<li>language pair: $langpair</li>");
echo("<li>benchmark: $benchmark</li>");
echo("<li>metrics: $metric");
foreach ($metriclinks as $m => $l){
    echo(" | <a href=\"$l\">$m</a>");
}
echo("</li></ul>");

echo("<img src=\"barchart.php?src=$srclang&trg=$trglang&test=$benchmark&metric=$metric\" alt=\"barchart\" />");


$file     = implode('/',[$leaderboard_url,$langpair,$benchmark,$metric]);
$file    .= '-scores.txt';
$lines = file($file);


$id=sizeof($lines);
echo('<table>');
echo("<tr><th>ID</th><th>Score</th><th>Model</th></tr>");
foreach ($lines as $line){
    $id--;
    $parts = explode("\t",$line);
    $model = explode('/',$parts[1]);
    $modelzip = array_pop($model);
    $modellang = array_pop($model);
    $link = "<a href=\"$parts[1]\">$modellang/$modelzip</a>";
    echo("<tr><td>$id</td><td>$parts[0]</td><td>$link</td></tr>");
}
echo('</table>');

?>
