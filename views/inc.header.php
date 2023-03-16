<?php

/**
 * Display header
 *
 * @param string $title Header title
 *
 * @return void
 */
function duplicator_header($title)
{
    echo "<h1>" . esc_html($title) . "</h1>";
}
