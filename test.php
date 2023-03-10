<?php
include("../esercizio_meta/config.php");

$config = new Config("../esercizio_meta/xml_files/myConfig.xml");

print_r($config->dictionary);
