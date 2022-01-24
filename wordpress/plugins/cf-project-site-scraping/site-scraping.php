<?php
namespace Facebook\WebDriver;

require_once 'vendor/autoload.php';

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

/**
 * ����N���E�h�t�@���f�B���O�T�C�g���X�N���C�s���O���Ď擾�����f�[�^���f�[�^�x�[�X�ɕۑ����܂��B
 * �Ȃ��X�N���C�s���O����URL�͔���J�Ƃ����Ă��������܂��B
 */
class SiteScraping {
    // �e�[�u����
    public static $table_name = 'cf_project_site_scraping';

    /**
     * �R���X�g���N�^
     */
    public function __construct() {
    }

    /**
     * �R���e���c�̃f�[�^���擾���ăf�[�^�x�[�X�ɕۑ����܂��B
     */
    public function getContent() {
        global $wpdb;

        // ChromeDriver��URL
        $host = 'http://localhost:4444/';
        // �X�N���C�s���O����URL
        $url = '';
        // �X�N���C�s���O����
        $scraping_dt = date("Y-m-d H:i:s");

        // �u���E�U���N��
        $options = new ChromeOptions();
        $options->addArguments(['--headless']);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability( ChromeOptions::CAPABILITY, $options );
        $driver = RemoteWebDriver::create($host, $capabilities);
        $driver->get($url);

        // �R���e���c�ƃT�C�h�o�[���\�������܂őҋ@
        $driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('wrapper')));

        // �R���e���c�̃f�[�^���擾���ăf�[�^�x�[�X�ɕۑ�
        $projects = $driver->findElements(WebDriverBy::xpath("//ul[@class='projects']/li"));
        foreach ($projects as $index => $project) {
            $n = $index + 1;
            // �摜URL
            $img_src = $project->findElement(WebDriverBy::xpath("//li[" . $n . "]/a/article/div[@class='image']/div/img"))->getAttribute('src') . "\n";
            // �摜��ۑ�
            $file_name = explode('/', explode('?', $img_src)[0]);
            $file_name = $file_name[count($file_name) - 1];
            file_put_contents(__DIR__ . '/img/' . $file_name, file_get_contents($img_src));
            // �^�C�g��
            $title = $project->findElement(WebDriverBy::xpath("//li[" . $n . "]/a/article/h4[@class='title']"))->getText() . "\n";
            // ����
            $time = $project->findElement(WebDriverBy::xpath("//li[" . $n . "]/a/article/div[@class='attribute']/div[@class='time']/p"))->getText() . "\n";
            // ���z
            $money = $project->findElement(WebDriverBy::xpath("//li[" . $n . "]/a/article/div[@class='attribute']/div[@class='money']/p"))->getText() . "\n";
            // �o�[�p�[�Z���g
            $bar_percent = $project->findElement(WebDriverBy::xpath("//li[" . $n . "]/a/article/div[@class='bar']/div[@class='bar-percent']"))->getText() . "\n";

            // // �R���e���c�̃f�[�^���f�[�^�x�[�X�ɕۑ�
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

        // �u���E�U���I��
        $driver->quit();
    }
}
