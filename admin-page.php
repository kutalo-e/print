<?php
die;
if (isset($_GET['upd'])) {
    echo "<div style='white-space:pre-wrap'>";

    echo "</div>";
    die;
}
?>

<h2>Plugin settings "Bit DataUpdater"</h2>
<div class="metabox-holder wp-core-ui" id="poststuff">
    <div class="postbox">
        <div class="handlediv"></div>
        <h3 class="hndle"><span>Sports</span></h3>
        <div class="inside">
            <?php
            /*
             * The form handler is in the file settings.php
             */
            ?>
            <form class="ones_form" name="action" action="" method="post" enctype="multipart/form-data" target="ones_form_target">
                <?php
                global $wpdb;

                echo '<b>Shortcodes for sports</b><br>';
                $sports = $wpdb->get_results (
                    "SELECT * FROM `{$wpdb->prefix}bit_sports`",
                    ARRAY_A
                );
                foreach ($sports as $sport) { ?>
                    [get_bit_html id=<?php echo $sport['id']; ?> name='<?php echo $sport['name']; ?> Betting Odds Comparison']<br>
                    <?php
                }
                echo '<br>';
                foreach ($sports as $sport) {
                    ?>
                    <label title="Displayed on the site, unless overridden" for="bit_sports__<?php echo $sport['id']; ?>">Sport name (id=<?php echo $sport['id']; ?>):
                        <input id="bit_sports__<?php echo $sport['id']; ?>" type="text" name="bit_sports__<?php echo $sport['id']; ?>" value="<?php echo $sport['name']; ?>" style="width: 15%; min-width: 128px;">
                    </label>
                    <label title="For matching providers" for="bit_sports_alt_name__<?php echo $sport['id']; ?>">, Aliases:
                        <input id="bit_sports_alt_name__<?php echo $sport['id']; ?>" type="text" name="bit_sports_alt_name__<?php echo $sport['id']; ?>" value="<?php echo $sport['alt_name']; ?>" style="width: 55%; min-width: 168px;">
                    </label>
                    <label title="For Vitalbet, Mbitcasino, Beteast" for="bit_sports_category_id__<?php echo $sport['id']; ?>">, CategoryID:
                        <input id="bit_sports_category_id__<?php echo $sport['id']; ?>" type="text" name="bit_sports_category_id__<?php echo $sport['id']; ?>" value="<?php echo $sport['CategoryID']; ?>" style="width: 5%; min-width: 40px;">
                    </label>
                    <br><br>
                    <?php
                }
                ?>
                <label for="bit_sports_new_name">Add new 'sport'
                    <input id="bit_sports_new_name" type="text" name="bit_sports_new_name" value="" style="width: 15%; min-width: 128px;">
                </label>
                <label for="bit_sports_new_name_alt">, Aliases:
                    <input id="bit_sports_new_name_alt" type="text" name="bit_sports_new_name_alt" value="" style="width: 55%; min-width: 168px;">
                </label>
                <label title="For Vitalbet, Mbitcasino, Beteast" for="bit_sports_new_name_category_id">, CategoryID:
                    <input id="bit_sports_new_name_category_id" type="text" name="bit_sports_new_name_category_id" value="" style="width: 5%; min-width: 40px;">
                </label>
                <br><br>
                <?php wp_nonce_field('bit_updater_sports_settings','bit_updater_sports_settings_field'); ?>
                <input class="button button-primary button-large" type="submit" name="submit" id="form-button" value="Save sports">
            </form>
        </div>
    </div>
</div>

<div class="metabox-holder wp-core-ui" id="poststuff">
    <div class="postbox">
        <div class="handlediv"></div>
        <h3 class="hndle"><span>Vendor names</span></h3>
        <div class="inside">
            <?php
            /*
             * The form handler is in the file settings.php
             */
            ?>
            <form class="ones_form" name="action" action="" method="post" enctype="multipart/form-data" target="ones_form_target">
                <?php

                $vendors = $wpdb->get_results (
                    "SELECT * FROM `{$wpdb->prefix}bit_vendor`",
                    ARRAY_A
                );
                foreach ($vendors as $vendor) {
                    ?>
                    <label for="bit_vendor__<?php echo $vendor['id']; ?>">Vendor name (id = <?php echo $vendor['id']; ?>):
                        <input id="bit_vendor__<?php echo $vendor['id']; ?>" type="text" name="bit_vendor__<?php echo $vendor['id']; ?>" value="<?php echo $vendor['name']; ?>" style="width: 30%; min-width: 128px;">
                    </label>
                    <label for="bit_vendor_url__<?php echo $vendor['id']; ?>">, vendor URL
                        <input id="bit_vendor_url__<?php echo $vendor['id']; ?>" type="text" name="bit_vendor_url__<?php echo $vendor['id']; ?>" value="<?php echo $vendor['vendor_url']; ?>" style="width: 24%; min-width: 128px;">
                    </label>
                    <label for="bit_vendor_odd_url__<?php echo $vendor['id']; ?>">, additional ODD URL
                        <input id="bit_vendor_odd_url__<?php echo $vendor['id']; ?>" type="text" name="bit_vendor_odd_url__<?php echo $vendor['id']; ?>" value="<?php echo $vendor['odd_url']; ?>" style="width: 10%; min-width: 128px;">
                    </label><br><br>
                    <?php
                }
                ?>
                <label for="bit_sports_new_vendor">Add new 'vendor'
                    <input id="bit_sports_new_vendor" type="text" name="bit_sports_new_vendor" value="" style="width: 40%; min-width: 168px;">
                </label>
                <label for="bit_vendor_new_url">, vendor URL
                    <input id="bit_vendor_new_url" type="text" name="bit_vendor_new_url" value="" style="width: 10%; min-width: 128px;">
                </label>
                <label for="bit_vendor_new_odd_url">, additional ODD URL
                    <input id="bit_vendor_new_odd_url" type="text" name="bit_vendor_new_odd_url" value="" style="width: 10%; min-width: 128px;">
                </label><br><br>
                <?php wp_nonce_field('bit_updater_vendor_settings','bit_updater_vendor_settings_field'); ?>
                <input class="button button-primary button-large" type="submit" name="submit" id="form-button" value="Save vendor names">
            </form>
        </div>
    </div>
</div>
<a class="button button-primary button-large" href="http://www.bitcoinesport.com/?bit-update" target="_blank">Update the data</a>
