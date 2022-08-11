# one_line_extract

PDFから1行抽出するための Python モジュールです。pac_user から利用されます。

動作させるにはセットアップが必要です。

## セットアップ手順

CentOS 7 でのセットアップ手順です。

### 必要なプログラムのインストール

#### pdftoppm

pac_user にも必要なものであるため、インストール手順は省略

インストールされていることを確認

```shell-session
$ pdftoppm -v
pdftoppm version 0.26.5
Copyright 2005-2014 The Poppler Developers - http://poppler.freedesktop.org
Copyright 1996-2011 Glyph & Cog, LLC
```

#### python3

インストール

```shell-session
$ sudo yum install python3
```

確認

```shell-session
$ python3 -V
Python 3.6.8
```

#### Tesseract OCR

version 4 をインストールする

リポジトリ登録

```shell-session
$ sudo yum-config-manager --add-repo https://download.opensuse.org/repositories/home:/Alexander_Pozdnyakov/CentOS_7/
$ sudo rpm --import https://build.opensuse.org/projects/home:Alexander_Pozdnyakov/public_key
```

Tesseract 本体をインストール

```shell-session
$ sudo yum install tesseract
```

確認

```shell-session
$ tesseract -v
tesseract 4.1.1-rc2-20-g01fb
 leptonica-1.76.0
  libjpeg 6b (libjpeg-turbo 1.2.90) : libpng 1.5.13 : libtiff 4.0.3 : zlib 1.2.7 : libwebp 0.3.0
 Found AVX512BW
 Found AVX512F
 Found AVX2
 Found AVX
 Found FMA
 Found SSE
```

日本語用学習済みモデルをインストール

次のコマンドで、[tessdata_fast](https://github.com/tesseract-ocr/tessdata_fast) にあるのと同じ日本語用学習済みモデルがインストールされます。

```shell-session
$ sudo yum install tesseract-langpack-jpn tesseract-langpack-jpn-vert
```

確認

```shell-session
$ tesseract --list-langs
List of available languages (4):
eng
jpn
jpn_vert
osd
```

### Python venv セットアップ

pac_user が apache ユーザーで動いているものとします。もしそうでない場合は、`sudo -u apache` を適当に置き換えてください。

この README があるディレクトリへ移動

（一例）

```shell-session
$ cd /var/www/pac/
$ cd one_line_extract/
```

venv を作成

これによりディレクトリ venv が作成されます。このディレクトリは .gitignore に含まれています。

```shell-session
$ sudo -u apache python3 -m venv ./venv
```

pip を upgrade

```shell-session
$ sudo -u apache ./venv/bin/pip install --upgrade pip
```

ここで、次のような警告が出ても無視してかまいません。

```shell-session
The directory {} or its parent directory is not owned by the current user and the cache has been disabled. Please check the permissions and owner of that directory. If executing pip with sudo, you may want sudo's -H flag.
```

必要なライブラリのインストール

各ライブラリはライセンスに従って利用する必要があります。

```shell-session
$ sudo -u apache ./venv/bin/pip install pdfplumber pyocr Pillow
```

ここで、次のような警告が出ても無視してかまいません。

```shell-session
The directory {} or its parent directory is not owned by the current user and the cache has been disabled. Please check the permissions and owner of that directory. If executing pip with sudo, you may want sudo's -H flag.
```

セットアップ手順は以上です。
