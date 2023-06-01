<div class="wrap">
<h1><?php _e('1 Simple Maintenance Mode Settings', 'smmp_maintenance_mode'); ?></h1>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Maintenance Mode Switch', 'smmp_maintenance_mode'); ?></th>
                <td>
                    <input type="checkbox" name="smmp_maintenance_mode_enabled" <?php echo $maintenance_mode_enabled ? 'checked' : ''; ?>>
                    <label for="smmp_maintenance_mode_enabled"><?php _e('Enable Maintenance Mode', 'smmp_maintenance_mode'); ?></label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Assign Maintenance Display Page', 'smmp_maintenance_mode'); ?></th>
                <td>
                    <select name="smmp_maintenance_page_id">
                        <?php foreach (smmp_get_pages() as $page_id => $page_title) : ?>
                            <option value="<?php echo $page_id; ?>" <?php echo $maintenance_page_id == $page_id ? 'selected' : ''; ?>><?php echo $page_title; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Exceptions', 'smmp_maintenance_mode'); ?></th>
                <td>
                    <textarea name="smmp_exceptions" rows="5" cols="50"><?php echo esc_textarea($exceptions); ?></textarea>
                    <p class="description"><?php _e('Enter one URL per line. These URLs will be accessible during maintenance mode.', 'smmp_maintenance_mode'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Show Banner', 'smmp_maintenance_mode'); ?></th>
                <td>
                    <input type="checkbox" name="smmp_show_banner" <?php echo $show_banner ? 'checked' : ''; ?>>
                    <label for="smmp_show_banner"><?php _e('Show persistent banner notice in the admin area when maintenance mode is active', 'smmp_maintenance_mode'); ?></label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Show Top Bar Menu', 'smmp_maintenance_mode'); ?></th>
                <td>
                    <input type="checkbox" name="smmp_show_top_bar_menu" <?php echo $show_top_bar_menu ? 'checked' : ''; ?>>
                    <label for="smmp_show_top_bar_menu"><?php _e('Show the Maintenance Mode menu in the top bar', 'smmp_maintenance_mode'); ?></label>
                </td>
            </tr>            
        </table>
        <p class="submit">
            <input type="submit" class="button-primary" value="Save Changes">
        </p>
    </form>
</div>
