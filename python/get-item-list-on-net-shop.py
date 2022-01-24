import chromedriver_binary
import pandas as pd
import sys
import time
from selenium import webdriver
from selenium.common.exceptions import NoSuchElementException
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By

def main(url):
    """
    あるネットショップから商品一覧を取得してEXCELファイルに出力するというプログラムです。

    Parameters
    ----------
    url : str
        あるネットショップの商品一覧のURL。
    """

    # ブラウザを起動
    options = Options()
    options.add_argument('--headless')
    driver = webdriver.Chrome(options=options)
    driver.get(url)

    index = 1
    hrefs = []
    for elems in driver.find_elements(By.XPATH, '//ul[@class="clearfix"]/li'):
        try:
            # 商品ページURL
            href = elems.find_element(By.XPATH, '//li[' + str(index) + ']/div[@class="product_body"]/div[1]/a').get_attribute('href')
        except NoSuchElementException as e:
            href = ''
        if href != "":
            hrefs.append(href)
        index += 1

    rows = []
    for href in hrefs:
        # 連続でdriver.getを行うとタイムアウトで終了してしまうため設定
        time.sleep(30)

        # 商品ページを取得
        driver.get(href)

        # カテゴリ
        category = find_element(driver, By.XPATH, '//dl[@id="s_cate"]/dd')
        # 商品名
        syo_name = find_element(driver, By.ID, 'item_h1')
        # 価格
        price_txt = find_element(driver, By.CLASS_NAME, 'price_txt')
        # アクセス
        ac_count = find_element(driver, By.CLASS_NAME, 'ac_count')
        # ほしいもの登録
        fav_count = find_element(driver, By.CLASS_NAME, 'fav_count')
        # レビュー・口コミ
        tabmenu_revcnt = find_element(driver, By.ID, 'tabmenu_revcnt')
        # お問い合わせ
        tabmenu_inqcnt = find_element(driver, By.ID, 'tabmenu_inqcnt')

        rows.append([category, syo_name, href, price_txt, ac_count, fav_count, tabmenu_revcnt, tabmenu_inqcnt])

    # ブラウザを終了
    driver.quit()

    # 商品一覧をEXCELファイルに出力
    df = pd.DataFrame(rows)
    df.columns = ['カテゴリ', '商品名' ,'商品ページURL', '価格', 'アクセス', 'ほしいもの登録', 'レビュー・口コミ', 'お問い合わせ']
    df.to_excel('item-list.xlsx', sheet_name='item-list', index=None)

def find_element(elems, by, value):
    """
    商品ページを検索して商品情報を取得します。

    Parameters
    ----------
    elems : list of WebElement
        商品ページ。
    by : str
        属性。
    value : str
        キーワード。

    Returns
    ----------
    text : str
        商品情報。
    """

    try:
        text = elems.find_element(by, value).text
    except NoSuchElementException as e:
        text = ''

    return text

if __name__ == "__main__":
    args = sys.argv
    if len(args) > 1:
        main(args[1])
    else:
        print('ERROR:: Arguments are too short')
