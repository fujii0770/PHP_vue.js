'''パッケージで利用するエラーやよく利用される関数を含むモジュール'''
import tempfile
import subprocess
import contextlib


class Error(Exception):
    '''パッケージ内で使うエラー'''
    pass


class ParameterError(Error):
    '''パラメータに関するエラー'''

    def __init__(self, name):
        # パラメータ名
        self.message = name

    def __str__(self):
        return self.message


class CodedError(Error):
    '''任意のエラーコードを設定できるエラー'''

    def __init__(self, code):
        # エラーコード
        self.message = code

    def __str__(self):
        return self.message


def check_dict(target, key, value_type):
    '''keyの存在と型を確認する

    指定通りでなければParameterErrorを投げる'''
    if key not in target:
        raise ParameterError(key)
    if not isinstance(target[key], value_type):
        raise ParameterError(key)


def _generate_pdf_to_image(option_format, ext):
    '''PDF画像化関数を作成する'''

    @contextlib.contextmanager
    def pdf_to_image(pdf_path, page_num, dpi,
                     x=None, y=None, w=None, h=None):
        '''PDFの指定範囲を画像化する

        pdftoppmを利用する
        実行に失敗した場合エラーを投げる
        '''
        # pdftoppm について
        # x, y に負ではないページの外を指す値を指定すると、ページ全体の画像が得られる
        # この場合、何も指定しなかった時と比べて画像の幅や高さが1小さくなることがある？
        # w, h は大きくしてもページの端までで切られる

        is_whole_page = x is y is w is h is None

        with tempfile.TemporaryDirectory() as tempdir:
            image_root = tempdir + "/image"
            image_path = image_root + "." + ext

            position_options = [] if is_whole_page else [
                "-x", str(x), "-y", str(y), "-W", str(w), "-H", str(h)
            ]

            # pdftoppm を利用
            cmd = ["pdftoppm", option_format,
                   "-singlefile",
                   "-f", str(page_num),
                   "-r", str(dpi),
                   *position_options,
                   pdf_path, image_root]

            process = subprocess.run(cmd,
                                     stdin=subprocess.DEVNULL,
                                     stdout=subprocess.DEVNULL)

            if process.returncode != 0:
                raise CodedError("imaging_command_failed")

            yield image_path

    return pdf_to_image


pdf_to_temp_png = _generate_pdf_to_image("-png", "png")
pdf_to_temp_jpg = _generate_pdf_to_image("-jpeg", "jpg")
