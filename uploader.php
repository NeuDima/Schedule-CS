<?php
function gs_render_uploader_page() {
    if (!empty($_FILES['schedule_file']) && $_FILES['schedule_file']['error'] === UPLOAD_ERR_OK) {

        $uploadDir = plugin_dir_path(__FILE__) . 'parser/';
        $newFileName = 'schedule.xlsx';
        $uploadedPath = $uploadDir . $newFileName;

        echo '<pre>';
        echo 'Upload Dir: ' . $uploadDir . "\n";
        echo 'Uploaded Path: ' . $uploadedPath . "\n";
        echo 'Temp file: ' . $_FILES['schedule_file']['tmp_name'] . "\n";
        echo '</pre>';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!file_exists($_FILES['schedule_file']['tmp_name'])) {
            echo '<div class="notice notice-error"><p>Временный файл не найден.</p></div>';
            return;
        }

        if (move_uploaded_file($_FILES['schedule_file']['tmp_name'], $uploadedPath)) {
            require_once $uploadDir . 'parser.php';
            echo '<div class="notice notice-success"><p>Файл успешно загружен и обработан.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Ошибка при сохранении файла.</p></div>';
        }
    }
    ?>


    <div class="wrap">
        <h1 class="wp-heading-inline">Загрузить расписание</h1>

        <?php if (!empty($upload_result)) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?= esc_html($upload_result); ?></p>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('gs_upload_schedule', 'gs_schedule_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="schedule_file">Файл расписания (.xlsx)</label></th>
                    <td><input type="file" name="schedule_file" id="schedule_file" required class="regular-text" accept=".xlsx,.xls"></td>
                </tr>
            </table>

            <?php submit_button('Загрузить'); ?>
        </form>
    </div>
<?php
}


