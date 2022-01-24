<?php
namespace Facebook\WebDriver;

require_once 'vendor/autoload.php';

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

/**
 * あるクラウドファンディングサイトをスクレイピングして取得したデータをデータベースに保存します。
 * なおスクレイピングするURLは非公開とさせていただきます。
 */
class SiteScraping {
    // テーブル名
    public static $table_name = 'cf_project_site_scraping';

    /**
     * コンストラクタ
     */
    public function __construct() {
    }

    /**
     * コンテンツのデータを取得してデータベースに保存します。
     */
    public function getContent() {
        global $wpdb;

        // ChromeDriverのURL
        $host = 'http://localhost:4444/';
        // スクレイピングするURL
        $url = '';
        // スクレイピング日時
        $scraping_dt = date("Y-m-d H:i:s");

        // ブラウザを起動
        $options = new ChromeOptions();
        $options->addArguments(['--headless']);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability( ChromeOptions::CAPABILITY, $options );
        $driver = RemoteWebDriver::create($host, $capabilities);
        $driver->get($url);

        // コンテンツとサイドバーが表示されるまで待機
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('wrapper')));

        // コンテンツのデータを取得してデータベースに保存
        $projects = $driver->findElements(WebDriverBy::xpath("//ul[@class='projects']/li"));
        foreach ($projects as $index => $project) {
            $n = $index + 1;
            // 画像URL
            $img_src = $project->findElement(WebDriverBy::xpath("//li[" . $n . "]/a/article/div[@class='image']/div/img"))->getAttribute('src') . "\n";
            // 画像を保存
            $file_name = explode('/', explode('?', $img_src)[0]);
            $file_name = $file_name[count($file_name) - 1];
            file_put_contents(__DIR__ . '/img/' . $file_name, file_get_contents($img_src));
            // タイトル
            $title = $project->findElement(WebDriverBy::xpath("//li[" . $n . "]/a/article/h4[@class='title']"))->getText() . "\n";
            // 日数
            $time = $project->findElement(WebDriverBy::xpath("//li[" . $n . "]/a/article/div[@class='attribute']/div[@class='time']/p"))->getText() . "\n";
            // 金額
            $money = $project->findElement(WebDriverBy::xpath("//li[" . $n . "]/a/article/div[@class='attribute']/div[@class='money']/p"))->getText() . "\n";
            // バーパーセント
            $bar_percent = $project->findElement(WebDriverBy::xpath("//li[" . $n . "]/a/article/div[@class='bar']/div[@class='bar-percent']"))->getText() . "\n";

            // // コンテンツのデータをデータベースに保存
            $wpdb->insert(
                $wpdb->prefix . self::$table_name, 
                array('scraping_dt' => $scraping_dt, 
                      'sort_no' => $n,
                      'img_src' => $img_src,
                      'title' => $title,
                      'time' => $time,
                      'money' => $money,
                      'bar_percent' => $bar_percent
                )
            ); 
        }

        // ブラウザを終了
        $driver->quit();
    }
}
