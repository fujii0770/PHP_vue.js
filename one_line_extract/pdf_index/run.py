"""API実行モジュール

API実行には run を利用してください
"""
import json
import traceback
from . import extract, core

# コマンドに対応する関数は次に従う必要がある
# 正常終了時
# -> stdoutへ処理結果を出力する
# エラーとしたい時
# -> 例外を発生させる
#    ※stdout, stderrへの出力はこのモジュールで行います
func_map = {
    "extract": extract.main,
}


def run(command, param_json):
    '''コマンドを実行する

    正常終了時は0、エラー発生時は1を返す
    '''
    error_code = _run(command, param_json)
    is_error = error_code is not None

    if is_error:
        # エラー情報出力 (stdout)
        error_json = json.dumps({"error": error_code})
        print(error_json)
        return 1
    else:
        return 0


def _run(command, param_json):
    '''コマンドを実行する

    JSONパース、コマンド呼び出し、エラーハンドリングをする
    正常終了時はNone、エラー発生時はエラーコードを返す
    '''
    # パラメータデコード、型チェック
    try:
        param = json.loads(param_json)
    except json.JSONDecodeError:
        traceback.print_exc()
        return "parameter_format"

    if not isinstance(param, dict):
        return "parameter:(root)"

    # 対応する関数取得
    func = func_map.get(command)
    if func is None:
        return "invalid_command"

    try:
        try:
            # 実行
            func(param)
            # 正常終了
            return None
        except Exception:
            # エラー情報出力 stderr
            traceback.print_exc()
            raise
    except core.ParameterError as e:
        return "parameter:" + e.message
    except core.CodedError as e:
        return e.message
    except Exception:
        return "python_unexpected"
