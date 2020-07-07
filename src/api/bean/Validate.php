<?php
namespace Bean;

class Validate
{
    /**
     * @var
     */
    private $data;

    /**
     * @var
     */
    private $rules;

    /**
     * エラーをためておく
     * @var
     */
    private $errors;

    /**
     * 配列そのものをチェックするvalidation rule
     * @var array
     */
    private $arrayRules = array(
        'requiredAny',
    );

    /**
     * Validate constructor.
     * @param $data
     * @param $rules
     */
    public function __construct($data, $rules)
    {
        $this->data = $data;
        $this->rules = $rules;

        $this->execute();
    }

    /**
     * エラーを返す
     * @return bool
     */
    public function errors()
    {
        return !empty($this->errors) ? $this->errors : false;
    }

    /**
     * validateチェック実行
     * name or name[] の場合のフォーム要素にしか対応していない
     */
    private function execute()
    {
        $errors = array();
        foreach ($this->rules as $name => $rules) {
            if (!empty($rules)) {
                $checkingValue = @$this->data[$name];

                // invalid時, ruleを配列に入れる
                foreach ($rules as $rule) {
                    if (in_array($rule, $this->arrayRules)) {
                        // 配列チェックの場合
                        if ($errorKey = $this->isValidArray($checkingValue, $rule)) {
                            @$errors[$name][0][] = $errorKey; // keyは0固定
                        }
                    } else {
                        // それ以外
                        if (is_array($checkingValue)) {
                            // 配列の各要素に対しチェックを行う
                            foreach ($checkingValue as $arrayKey => $arrayValue) {
                                if ($errorKey = $this->isValid($arrayValue, $rule)) {
                                    @$errors[$name][$arrayKey][] = $errorKey;
                                }
                            }
                        } else {
                            // 配列でない
                            if (!in_array($rule, $this->arrayRules)) {
                                if ($errorKey = $this->isValid($checkingValue, $rule)) {
                                    @$errors[$name][] = $errorKey;
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->errors = $errors;
        return empty($errors);
    }

    /**
     * ある一つの要素のvalidation
     * @param $value
     * @param $rule
     * @return null|string : エラー時エラーkey(ルール名)を返す
     */
    private function isValid($value, $rule)
    {
        $errorKey = null;
        if (substr($rule, 0, 1) === '/') {
            // 正規表現
            $result = $this->validRegex($value, $rule);
            $errorKey = 'regex';
        } elseif (preg_match('/^requiredIf:.+$/', $rule)) {
            // requiredIf
            $result = $this->validRequiredIf($value, $rule);
            $errorKey = 'requiredIf';
        } elseif (preg_match('/^requiredUnless:.+$/', $rule)) {
            // requiredUnless
            $result = $this->validRequiredUnless($value, $rule);
            $errorKey = 'requiredUnless';
        } else {
            // 用意されたvalidate
            $validateMethod = "valid" . ucfirst($rule);
            $result = $this->{$validateMethod}($value);
            $errorKey = $rule;
        }

        return !$result ? $errorKey : null;
    }

    /**
     * 配列要素のvalidation
     * @param $values
     * @param $rule
     * @return null|string : エラー時エラーkey(ルール名)を返す
     */
    private function isValidArray($values, $rule)
    {
        $errorKey = null;
        $validateMethod = "valid" . ucfirst($rule);
        $result = $this->{$validateMethod}($values);

        return !$result ? $rule : null;
    }

    /**
     * 必須チェック
     * @param $value
     * @return bool
     */
    private function validRequired($value)
    {
        return isset($value) && !empty($value);
    }

    /**
     * 正規表現チェック
     * @param $value
     * @param $regex
     * @return bool
     */
    private function validRegex($value, $regex)
    {
        // 存在しない場合このバリデーションでは引っ掛けない
        if (!$this->validRequired($value)) {
            return true;
        }
        return (bool) preg_match($regex, $value);
    }

    /**
     * メールアドレスチェック
     * @param $value
     * @return bool
     */
    private function validEmail($value)
    {
        $regex = '/(\\A(?:^([a-z0-9][a-z0-9_\\-\\.\\+\\?]*)@([a-z0-9][a-z0-9\\.\\-]{0,63}\\.(com|org|net|biz|info|name|net|pro|aero|coop|museum|[a-z]{2,4}))$)\\z)|(^[a-z0-9\?\.\/_-]{3,30}@(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)$)/i';
        return $this->validRegex($value, $regex);
    }

    /**
     * URLチェック
     * @param $value
     * @return bool
     */
    private function validUrl($value)
    {
        $regex = '/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/';
        return $this->validRegex($value, $regex);
    }

    /**
     * 配列の中身が1つ以上あるか
     * @param $values
     * @return bool
     */
    private function validRequiredAny($values)
    {
        if (empty($values)) {
            return false;
        }

        foreach ($values as $value) {
            if ($this->validRequired($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * "formパラメータ名"="値"の場合に必須チェック
     * "formパラメータ名"が配列の場合は"値"を含む場合
     * @param $value
     * @param $rule
     *      "requiredIf:formパラメータ名:値" で飛んでくる
     *      値は任意で、値が存在しない場合、formパラメータ名の存在のみチェック
     * @return bool
     */
    private function validRequiredIf($value, $rule)
    {
        $params = explode(':', $rule);
        if (empty($params[1])) {
            return false; // 設定ミス
        }

        $targetName = $params[1];
        $targetValue = !empty($params[2]) ? $params[2] : null;

        if (!empty($targetValue)) {
            // 値存在時: "formパラメータ名"="値"の場合に必須チェック
            if ((!is_array(@$this->data[$targetName]) && @$this->data[$targetName] == $targetValue)
                || (is_array(@$this->data[$targetName]) && in_array($targetValue, @$this->data[$targetName]))) {
                return $this->validRequired($value);
            }
        } else {
            // 値ない時: "formパラメータ名"が存在の場合に必須チェック
            if (!empty($this->data[$targetName])) {
                return $this->validRequired($value);
            }
        }

        // それ以外は任意でOK
        return true;
    }

    /**
     * "formパラメータ名"!="値"の場合に必須チェック
     * "formパラメータ名"が配列の場合は"値"を含まない場合
     * @param $value
     * @param $rule
     *      "requiredIf:formパラメータ名:値" で飛んでくる
     *      値は任意で、値が存在しない場合、formパラメータ名の存在しない場合のみチェック
     * @return bool
     */
    private function validRequiredUnless($value, $rule)
    {
        $params = explode(':', $rule);
        if (empty($params[1])) {
            return false; // 設定ミス
        }

        $targetName = $params[1];
        $targetValue = !empty($params[2]) ? $params[2] : null;

        if (!empty($targetValue)) {
            // 値存在時: "formパラメータ名"!="値"の場合に必須チェック
            if ((!is_array(@$this->data[$targetName]) && @$this->data[$targetName] != $targetValue)
                || (is_array(@$this->data[$targetName]) && !in_array($targetValue, @$this->data[$targetName]))) {
                return $this->validRequired($value);
            }
        } else {
            // 値ない時: "formパラメータ名"が存在しない場合に必須チェック
            if (empty($this->data[$targetName])) {
                return $this->validRequired($value);
            }
        }

        // それ以外は任意でOK
        return true;
    }

}