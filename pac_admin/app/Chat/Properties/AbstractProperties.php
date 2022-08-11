<?php
namespace App\Chat\Properties;

use App\Chat\Exceptions\ImmutablePropertyAccessException;
use Closure;

abstract class AbstractProperties
{
    private $params = [];
    private $immutable_all = false;

    /**
     * @param bool $immutable 変更不可にするかどうか
     */
    public function __construct(bool $immutable = false)
    {
        $this->immutable_all = $immutable;
    }

    /**
     * 値を設定または取得します。
     *
     * @param string $key キー
     * @param array $value 値 初めの要素を値として設定します（func_get_args()対応）
     * @param mixed $default $valueが空でキーに対する値が無い場合に返す値です
     *
     * @return mixed|self getterの場合（$valueが空）であれば$keyに対する値、setterの場合は$this
     */
    final protected function _getset(string $key, array $value = [], $default = null)
    {
        return $this->_getset_mu(false, $key, $value, $default);
    }

    final protected function _getset_immutable(string $key, array $value = [], $default = null)
    {
        return $this->_getset_mu(true, $key, $value, $default);
    }

    private function _getset_mu(bool $immutable, string $key, array $value = [], $default = null)
    {
        $imu = ($this->immutable_all || $immutable);
        if (count($value) < 1) {
            $ret = null;
            if (array_key_exists($key, $this->params)) {
                $ret = $this->params[$key];
            }
            //  else if ($imu && $default === null) {
            //     throw new ImmutablePropertyAccessException();
            // }

            if ($ret === null && $default !== null) {
                $ret = $default;
            }
        } else {

            if ($imu && array_key_exists($key, $this->params)) {
                throw new ImmutablePropertyAccessException();
            }
            $this->params[$key] = $value[0];
            $ret = $this;
        }
        return $ret;
    }

    /**
     * プロパティの値を設定または取得します。
     *
     * @param string $key キー
     * @param mixed $value 値
     * @param mixed $default キーに対する値がnullの場合に返す値
     */
    final public function property(string $key, $value = null, $default = null) {
        $vals = [];
        if (func_num_args() > 1) {
            $vals[] = $value;
        }
        return $this->_getset($key, $vals, $default);
    }

    final public function has(string $propertyKey) {
        return array_key_exists($propertyKey, $this->params);
    }

    /**
     * プロパティの値を取得します。
     * プロパティの値がNullの場合は引数のクロージャ―の実行結果をプロパティの値とします。
     *
     */
    final public function getIfNullSet(string $key, Closure $closure) {
        $val = $this->property($key);
        if ($val === null) {
            $val = $closure();
            $this->property($key, $val);
        }
        return $val;
    }

    /**
     *
     */
    final public function __set($name, $value)
    {
        $this->_getset($name, [$value]);
    }

    /**
     *
     */
    final public function __get($name)
    {
        return $this->_getset($name);
    }

    /**
     * 値がセットされたことのあるプロパティを配列として取得します。
     *
     * @return array
     */
    public function toArray() {
        return $this->params;
    }

    /**
     * 値がセットされたことのあるキーを取得します。
     *
     * @return array
     */
    public function getKeysHasBeenSet() {
        return array_keys($this->params);
    }

    /**
     * 変更不可かどうかを取得します。
     *
     * @return bool
     */
    public function isImmutable() {
        return $this->immutable_all;
    }
}
