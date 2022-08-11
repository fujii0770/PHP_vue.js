"""メイン機能 1Line抽出モジュール"""
import json
import re
import decimal
import shutil
import contextlib
import pdfplumber
import pyocr.builders
from PIL import Image
from . import core


def check_param_type(param):
    '''パラメータ 存在・型チェック'''
    core.check_dict(param, "pdf_path", str)
    core.check_dict(param, "page", int)
    core.check_dict(param, "dpi", int)
    core.check_dict(param, "x1", int)
    core.check_dict(param, "y1", int)
    core.check_dict(param, "x2", int)
    core.check_dict(param, "y2", int)


def to_xywh(x1, y1, x2, y2):
    '''2点の位置から左上の位置・幅・高さを計算'''
    x = min(x1, x2)
    y = min(y1, y2)
    w = max(x1, x2) - x + 1
    h = max(y1, y2) - y + 1

    return (x, y, w, h)


def check_and_get_rectangle(param):
    '''パラメータから(x,y,w,h)を計算し返す

    xもしくはyが負の場合エラーを投げる
    '''
    x1 = param["x1"]
    y1 = param["y1"]
    x2 = param["x2"]
    y2 = param["y2"]

    result = to_xywh(x1, y1, x2, y2)
    x, y, w, h = result

    if x < 0 or y < 0:
        raise core.CodedError("negative_area")

    return result


def exclude_unwanted_chars(value):
    '''1Line抽出で不要な文字を削除'''
    value = value.strip()
    # 空白除去
    value = re.sub(r"[ 　]", "", value)
    return value


def add_result(results, result_type, value):
    '''抽出・認識の成否を判断しresultsへ加える'''
    DENY_MULTILINE = True

    result = {"type": result_type}

    # pdfplumberのextract_text()はテキストがないとNoneを返す
    if value is not None:
        value = exclude_unwanted_chars(value)
        if len(value) == 0:
            value = None

    if value is None:
        result["status"] = "empty"
    else:
        if DENY_MULTILINE and '\n' in value:
            result["status"] = "multiline"
        else:
            result["status"] = "success"

    if result["status"] == "success":
        result["text"] = value

    results.append(result)


def adjust_bbox(page, bbox, scale):
    '''bboxが1px(=scaleの値)分はみ出している場合、はみ出ないようにする

    これは画像が1px大きい(その大きさである)ことがあるため
    それよりも大きい値の範囲が含まれていればエラーを投げる
    '''
    # 右端、下端よりも外を含んでいないかチェック
    _, _, right_x, bottom_y = bbox
    if right_x > page.width + scale or bottom_y > page.height + scale:
        raise core.CodedError("out_of_page_area")

    # ページの外を含まないようにする
    right_x = min(right_x, page.width)
    bottom_y = min(bottom_y, page.height)

    return bbox[0:2] + (right_x, bottom_y)


def main(param):
    '''extractコマンドに対応する関数'''
    check_param_type(param)

    if param["page"] < 1:
        raise core.ParameterError("page")
    if param["dpi"] < 1:
        raise core.ParameterError("dpi")

    pdf_path = param["pdf_path"]
    page_num = param["page"]  # 1が最初のページ
    dpi = param["dpi"]

    x, y, w, h = check_and_get_rectangle(param)

    # pdfplumberでの処理
    with pdfplumber.open(pdf_path) as pdf:
        if not page_num - 1 < len(pdf.pages):
            raise core.CodedError("page_not_exists")

        page = pdf.pages[page_num - 1]

        scale = decimal.Decimal(72) / decimal.Decimal(dpi)
        bbox = tuple([i*scale for i in (x, y, x+w, y+h)])
        bbox = adjust_bbox(page, bbox, scale)

        # relative=Trueはページ開始位置が0, 0でないPDFのために必要
        cropped = page.crop(bbox, relative=True)

        results = []

        add_result(results, "embedded", cropped.extract_text())

        make_chars_only_first_char(cropped)
        add_result(results, "embedded_workaround", cropped.extract_text())

    ocr_result = ocr(
        pdf_path, page_num, dpi, x, y, w, h)
    add_result(results, "ocr", ocr_result)

    json_str = json.dumps({
        "results": results
    })

    print(json_str)


def get_tool_tesseract():
    '''pyocrで利用可能なOCRツールからTesseractを探して返す

    なければエラーを投げる
    '''
    all_tools = pyocr.get_available_tools()
    tesseracts = filter(lambda x: "Tesseract" in x.get_name(), all_tools)

    use_tool = next(tesseracts, None)

    if use_tool is None:
        raise Exception("cannot find tesseract")
    else:
        return use_tool


def run_tesseract_oneline(image):
    '''1行の画像としてTesseractで認識させ結果を返す'''
    TESSERACT_LANG = "jpn"
    PSM_ONELINE = 7

    tool = get_tool_tesseract()

    ocr_result = tool.image_to_string(
        image,
        lang=TESSERACT_LANG,
        builder=pyocr.builders.TextBuilder(tesseract_layout=PSM_ONELINE)
    )

    return ocr_result


@contextlib.contextmanager
def generate_area_image(pdf_path, page_num,
                        positions_dpi, x, y, w, h, image_dpi=300):
    '''PDFの指定範囲を画像化する

    ページ外を含む範囲を指定した場合はNoneとなる
    '''

    # 生成画像のDPIに合わせて変更
    scale = image_dpi / positions_dpi
    x, y, w, h = [round(x*scale) for x in [x, y, w, h]]

    generate_tmp_img = core.pdf_to_temp_png(
        pdf_path, page_num, image_dpi, x, y, w, h)

    with generate_tmp_img as img_path, Image.open(img_path) as image:
        # x, y に右端より右や下端より下を指定すると、ページ全体の画像になってしまう
        # これは、これよりも前のpdfplumberを利用したチェック時点で引っかかると思われる
        # 念のためチェックしている
        is_got_whole_page = image.width > w or image.height > h
        if is_got_whole_page:
            # この場合、Noneとする
            yield None
        else:
            yield image


def generate_image_with_padding(image, padding_x, padding_y):
    '''余白をつけた画像を生成する'''
    padded = Image.new(image.mode, (image.width + padding_x * 2,
                                    image.height + padding_y * 2),
                       # RGB 想定
                       (255, 255, 255))
    padded.paste(image, (padding_x, padding_y))

    return padded


def image_output(image, path):
    '''画像をファイル出力する

    画像がファイルの場合は元ファイルをコピーする
    '''
    is_image_file = hasattr(image, "filename") and image.filename != ""
    if is_image_file:
        shutil.copyfile(image.filename, path)
    else:
        image.save(path)


def ocr(pdf_path, page_num, positions_dpi, x, y, w, h,
        img_copy_to=None):
    '''PDFの指定範囲をTesseractで認識させる'''

    def process_image_and_ocr(area_image):
        '''画像を加工後認識させる'''
        # 余白付加 4辺10px
        padded = generate_image_with_padding(area_image, 10, 10)
        text = run_tesseract_oneline(padded)

        return padded, text

    # 指定範囲の画像作成
    with generate_area_image(pdf_path, page_num, positions_dpi, x, y, w, h) \
            as area_image:
        if area_image is None:
            # もしもページ外扱いとなった場合、文字列はないこととする
            return ""

        ocr_image, text = process_image_and_ocr(area_image)

        # デバッグ用
        if img_copy_to is not None:
            image_output(ocr_image, img_copy_to)

        return text


def make_chars_only_first_char(cropped):
    '''pdfplumberの持つ.charsを初めの1文字のみに書き換える

    PDFファイルによっては生じる問題への対策
    '''
    for char in cropped.chars:
        first, _ = get_first_char(char["text"])
        char["text"] = first


def get_first_char(text):
    '''textから初めの一文字を取り出す'''
    # LibreOffice 6.0.7.3 等で出力したPDFで生じる問題の対策
    # * 1文字が想定されているが複数文字が入っている問題

    # 最初のコードポイントのみを取り出す
    # ただし2つめのコードポイントが異体字セレクタならばそれも含む

    # 次の場合には対応しない = 2つめのコードポイント以降が取り除かれる
    # * ZWJ
    # Emoji 関連
    # * Emoji Modifier Sequence
    # * Regional Indicator Symbol

    # 結合文字 パ "\u309A\u30CF" 等
    # 次のことから対応しなくてよいと思われるため、特別な処理はしない
    # LibreOffice出力PDF -> パ "\u30D1" となる
    # Word出力PDF -> 2文字扱いとなり、分かれている

    len_original = len(text)

    if len_original >= 2:
        # 2つめのコードポイント
        secondary = ord(text[1])

        # 異体字セレクタか
        is_vs = 0x180B <= secondary <= 0x180D or \
            0xFE00 <= secondary <= 0xFE0F or \
            0xE0100 <= secondary <= 0xE01EF

        end = 2 if is_vs else 1
    else:
        end = 1

    is_changed = len_original != end

    return text[:end], is_changed
