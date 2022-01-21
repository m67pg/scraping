import csv
import chromedriver_binary
from selenium import webdriver
from selenium.common.exceptions import NoSuchElementException
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait

def main():
    """
    ある検索サイトで商品一覧を取得してCSVファイルに出力するというプログラムです。
    なお検索サイトのURLとキーワードは非公開とさせていただきます。

    """

    # 検索サイトのURL
    url = ''
    # 検索サイトで入力するキーワード
    keyword = ''
    # CSVファイルとして出力する商品一覧
    csvlist = []

    # ブラウザを起動
    options = Options()
    options.add_argument('--headless')
    driver = webdriver.Chrome(options=options)
    driver.get(url)

    # 検索窓を表示するボタンが表示されるまで待機
    WebDriverWait(driver, 180).until(EC.presence_of_element_located((By.ID, 'showSearchBar')))

    # 検索窓にキーワードを入力し検索開始
    driver.find_element(By.ID, 'showSearchBar').click()
    input_elem = driver.find_element(By.ID, 'searchInput')
    input_elem.send_keys(keyword)
    input_elem.send_keys(Keys.RETURN)

    # 検索結果の商品一覧が表示されるまで待機
    WebDriverWait(driver, 15).until(EC.presence_of_element_located((By.CLASS_NAME, 'ag-center-cols-clipper')))

    # CSVファイルとして出力する商品一覧を作成
    for elems in driver.find_elements(By.XPATH, '//div[@class="ag-center-cols-container"]/div'):
        create_csv_list(elems, csvlist, 0)

    # 最下部へスクロール
    driver.find_element(By.TAG_NAME, 'body').click()
    driver.find_element(By.TAG_NAME, 'body').send_keys(Keys.PAGE_DOWN)

    # 最下部の商品一覧が表示されるまで待機
    WebDriverWait(driver, 15).until(EC.presence_of_element_located((By.XPATH, '//div[@class="ag-center-cols-container"]/div[@row-index="7"]')))

    # CSVファイルとして出力する商品一覧に最下部までの商品を追加
    for elems in driver.find_elements(By.XPATH, '//div[@class="ag-center-cols-container"]/div'):
        create_csv_list(elems, csvlist, 7)

    # row-indexで並び替え
    csvlist.sort(key=lambda x: x[5])
    # row-indexを削除
    for c in csvlist:
        c.pop(5)

    # 商品一覧をCSVファイルとして出力
    with open(keyword + '.csv', 'w', encoding='SJIS', newline='') as f:
        writer = csv.writer(f)
        writer.writerows(csvlist)

    # ブラウザを終了
    driver.quit()

def create_csv_list(elems, csvlist, start_position):
    """
    CSVファイルとして出力する商品一覧を作成します。

    Attributes
    ----------
    elems : list of WebElement
        検索結果の商品一覧。
    csvlist : array-like
        CSVファイルとして出力する商品一覧。
    start_position : int
        row-indexの開始位置。
    """

    n = str(elems.get_attribute('row-index'))
    index = int(n)
    if(index >= start_position):
        # 画像
        image = elems.find_element(By.XPATH, '//div[@row-index="' + n + '"]/div[1]/img').get_attribute('src')
        # 商品名
        title = elems.find_element(By.XPATH, '//div[@row-index="' + n + '"]/div[2]/span/a/span').text
        # 価格
        price = elems.find_element(By.XPATH, '//div[@row-index="' + n + '"]/div[3]/span/span').text
        try:
            # サイズ
            size  = elems.find_element(By.XPATH, '//div[@row-index="' + n + '"]/div[4]/span/span').text
        except NoSuchElementException as e:
            size  = ''
        # ASIN
        asin  = elems.find_element(By.XPATH, '//div[@row-index="' + n + '"]/div[5]').text

        csvlist.append([title, image, price, size, asin, index])

if __name__ == "__main__":
    main()
