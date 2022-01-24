<?php
/*
Plugin Name: CFプロジェクトサイトスクレイピング
Description: あるクラウドファンディングサイトをスクレイピングして取得したデータをデータベースに保存します。
Version: 1.0
*/

require_once 'site-scraping.php';

use Facebook\WebDriver\SiteScraping;

register_activation_hook(__FILE__, 'cf_project_site_scraping_install');
register_uninstall_hook ( __FILE__, 'cf_project_site_scraping_delete_data' );
    
/**
 * 初回読み込み時にスクレイピングしたデータを保存するテーブルを作成します。
 */
function cf_project_site_scraping_install() {
    global $wpdb;
        
    $table = $wpdb->prefix . SiteScraping::$table_name;
    $charset_collate = $wpdb->get_charset_collate();

    if ($wpdb->get_var("show tables like '$table'") != $table) {
        $sql = "CREATE TABLE  {$table} (
                    scraping_dt datetime  comment 'スクレイピング日時',
                    sort_no int  comment '並び順', 
                    img_src VARCHAR(256) comment '画像URL',
                    title VARCHAR(128) comment 'タイトル',
                    time VARCHAR(30) comment '日数',
                    money VARCHAR(30) comment '金額',
                    bar_percent VARCHAR(30) comment 'バーパーセント'
                ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
    
/**
 * プラグイン削除時にスクレイピングしたデータを保存するテーブルを削除します。
 */
function cf_project_site_scraping_delete_data() {
    global $wpdb;
    $table = $wpdb->prefix . SiteScraping::$table_name;
    $sql = "DROP TABLE IF EXISTS {$table}";
    $wpdb->query($sql);
}


/**
 * あるクラウドファンディングサイトをスクレイピングするアクションを追加します。
 */
function my_auto_function() {
    $site_scraping = new SiteScraping();
    $site_scraping->getContent();
}
add_action('my_auto_function_cron', 'my_auto_function');

/**
 * 指定された一定の期間をおいてWordPressのコアファイルによって実行されるアクションを登録します。
 * アクションは誰かがサイトを訪れたときに、予定されていた時間をすぎていた場合、実行されます。
 * 
 * なお、スケジュールの変更はプラグイン「WP Crontrol」で行うものとします。
 */
if (!wp_next_scheduled('my_auto_function_cron')) {
  date_default_timezone_set('Asia/Tokyo');
  wp_schedule_event(date("Y-m-d H:i:s"), 'daily', 'my_auto_function_cron');
}
