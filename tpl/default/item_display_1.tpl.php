<?php
/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
?>

<div class="display display_1">
    <div class="poster_preview">
        <a href="?page=view&id=<?= $tdata['id'] ?>&type=<?= $tdata['ilink'] ?> ">
            <img class="img_poster_preview"  alt="" src="<?= $tdata['poster'] ?>"/>
        </a>
    </div>
    <div class="item_details">
        <div class="item_title"><?= $tdata['title'] ?></div>
        <hr/>
        <div class="item_desc">
            <?php if (!empty($tdata['source'])) { ?>
                <span class="item_source_link">[<a href="<?= $tdata['guid'] ?>" target=_blank ><?= $tdata['source'] ?></a>]</span>
                <?php
            }
            if (!empty($tdata['hsize'])) {
                ?>
                <span class="item_size">[<?= $tdata['hsize'] ?>]</span>
                <?php
            }
            if (!empty($tdata['rating'])) {
                ?>
                <span class="item_rating">[<?= $tdata['L_RATING_MIN'] . $tdata['rating'] ?>]</span>
                <?php
            }
            if (!empty($tdata['download'])) {
                ?>
                <span class="item_download_link"><a href="<?= basename($_SERVER['REQUEST_URI']) . '&download=' . rawurlencode($tdata['download']) ?>">[<?= $tdata['L_DOWNLOAD_MIN'] ?>]</a></span>
            <?php } ?>
        </div>  

    </div>
</div>
