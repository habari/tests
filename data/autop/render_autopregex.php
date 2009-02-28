<?php

require dirname(__FILE__) . '../../../../../habari-trunk/htdocs/system/classes/format.php';
echo Format::autop(file_get_contents($_SERVER['argv'][1]));