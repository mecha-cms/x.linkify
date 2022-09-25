<?php

echo '<style>body{white-space:pre-wrap}</style>';
echo call_user_func("x\\linkify", file_get_contents(__DIR__ . D . 'test'));

exit;