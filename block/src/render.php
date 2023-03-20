<?php

function openlab_render_activity_block( $atts ) {
    ob_start();
    ?>
    <div>
        <p>BP templates to be inserted</p>
    </div>
    <?php
    $html = ob_get_clean();

    return $html;
}