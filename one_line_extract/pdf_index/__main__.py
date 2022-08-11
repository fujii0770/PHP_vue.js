"""コマンド実行を処理するモジュール"""
import sys
import argparse
from . import run


def main():
    '''コマンド実行を処理する'''
    parser = argparse.ArgumentParser(prog="pdf_index")
    parser.add_argument("function")
    parser.add_argument("parameter")
    args = parser.parse_args()

    return_code = run(args.function, args.parameter)

    sys.exit(return_code)


if __name__ == "__main__":
    main()
