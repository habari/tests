<?php

// Test the bitmask class
include "../htdocs/system/classes/bitmask.php";

define('POST_FLAG_ALLOWS_COMMENTS'  ,1);
define('POST_FLAG_ALLOWS_TRACKBACKS',1 << 1);
define('POST_FLAG_ALLOWS_PINGBACKS' ,1 << 2);

$flags= array(
        'allows_comments'=>POST_FLAG_ALLOWS_COMMENTS
      , 'allows_trackbacks'=>POST_FLAG_ALLOWS_TRACKBACKS
      , 'allows_pingbacks'=>POST_FLAG_ALLOWS_PINGBACKS);

$bitmask= new Bitmask($flags);

print 'Setting allowed comments to true<br>';
$bitmask->allows_comments= true;
print 'Setting allow trackbacks to false<br>';
$bitmask->allows_trackbacks= false;
print 'Setting allow pingbacks to true<br>';
$bitmask->allows_pingbacks= true;

echo 'testing if comments are allowed: ' . ($bitmask->allows_comments ? 'yes' : 'no') . '<br/>';
echo 'testing if trackbacks are allowed: ' . ($bitmask->allows_trackbacks ? 'yes' : 'no') . '<br/>';
echo 'testing if pingbacks are allowed: ' . ($bitmask->allows_pingbacks ? 'yes' : 'no') . '<br/>';
?>
