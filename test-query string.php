<?php
$url = 'www.mysite.com/category/subcategory?myqueryhash';
echo parse_url($url, PHP_URL_QUERY); # output "myqueryhash"