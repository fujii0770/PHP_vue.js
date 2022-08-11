"""PDF 1Line抽出機能を提供する

Pythonからの利用方法:
    pdf_index.run("extract", "{parameter (json)}")

コマンドからの利用方法:
    python3 -m pdf_index extract "{parameter (json)}"
"""

from .run import run
__all__ = ["run"]
