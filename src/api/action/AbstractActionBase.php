<?php
namespace Action;

/**
 * Created by PhpStorm.
 * User: hoshi
 * Date: 2017/08/03
 * Time: 15:02
 */
abstract class AbstractActionBase
{
    /**
     * @var $contentName
     */
    protected $contentName;

    /**
     * @var $config
     */
    private $config;

    /**
     * post data
     * @var array
     */
    protected $data;

    /**
     * AbstractActionBase constructor.
     * @param $contentName
     */
    public function __construct($contentName)
    {
        // contentName
        $this->contentName = $contentName;

        // config
        $this->setConfig($contentName);

        // escape
        $this->data = $this->escape($_POST);

        // リクエスト自体のチェック
        if ($message = $this->requestWithErrors()) {
            $this->shutdown($message);
        }
    }

    /**
     * execute
     * @return mixed
     */
    public abstract function execute();

    /**
     * 成功時response
     * @param array $data
     * @return string
     */
    protected function responseSuccess($data = array())
    {
        $ret = ['code' => 200];
        if (!empty($data)) {
            $ret['data'] = $data;
        }
        return $this->response($ret);
    }

    /**
     * エラー時response
     * @param $code
     * @param $errors
     * @param null $message
     * @return string
     */
    protected function responseError($code, $errors = null, $message = null)
    {
        $code = is_numeric($code) ? (int) $code : 500;
        $ret = ['code' => $code];
        if (!empty($errors)) {
            $ret['errors'] = $errors;
        }
        if (!empty($message)) {
            $ret['message'] = $message;
        }
        return $this->response($ret);
    }

    /**
     * json response共通
     * @param $data
     * @return string
     */
    private function response($data)
    {

        http_response_code($data['code']);
        header('Content-Type: application/json; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');
        return json_encode(
            $data,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );
    }

    /**
     * 強制終了
     * display_errorsの場合詳細表示
     * それ以外は404にする
     * @param null $message
     */
    protected function shutdown($message = null)
    {
        $data = [
            'code' => 404,
            'message' => 'Not Found.',
        ];
        if (ini_get('display_errors')) {
            $data = [
                'code' => 500,
                'message' => $message,
            ];
        }
        echo $this->response($data);
        exit;
    }

    /**
     * configをset
     * @param $contentName
     */
    private function setConfig($contentName)
    {
        $config = require_once dirname(dirname(__FILE__)) . '/config.php';
        if (!empty($config['default']) && !empty($config[$contentName])) {
            $this->config = array_replace_recursive($config['default'], $config[$contentName]);
        } elseif (!empty($config['default'])) {
            $this->config = $config['default'];
        } elseif (!empty($config[$contentName])) {
            $this->config = $config[$contentName];
        }

        // 必須の要素の存在チェック
        $requiredKeys = array(
            'domain',
            'key',
            'fromEmail',
            'fromName',
            'adminEmail',
            'userEmail',
            'validate',
        );
        foreach ($requiredKeys as $key) {
            if (empty($this->config[$key])) {
                $this->shutdown("configに'{$key}'が指定されていません。");
            }
        }
    }

    /**
     * config 取得
     * @return mixed
     */
    protected function config()
    {
        return $this->config;
    }

    /**
     * request自体のチェック
     */
    private function requestWithErrors()
    {
        // POSTでない
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return 'methodがPOSTではありません。';
        }
        // API Keyがない,または合っていない
        if (empty($this->config['key']) || empty($this->data['key']) || $this->config['key'] !== $this->data['key']) {
            return 'API keyが不正です。';
        }
        // Ajaxでない
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            return 'Ajaxリクエストのみ受け付けています。';
        }
        // HTTP_ORIGINは外部を許さないように
        // IEやFFではHTTP_ORIGINが飛んでこないのでset時のみチェック
        // headerやhtaccessで設定したほうがいいかも
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            if (empty($_SERVER['HTTP_ORIGIN']) || !$this->isValidDomain($_SERVER['HTTP_ORIGIN'])) {
                return 'HTTP_ORIGINが不正です。';
            }
        }
        // refererも同様に外部を許さないように
        if (empty($_SERVER['HTTP_REFERER']) || !$this->isValidDomain($_SERVER['HTTP_REFERER'])) {
            return 'HTTP_REFERERが不正です。';
        }

        return null;
    }

    /**
     * 許可されたドメインかどうかを調べる
     * @param $domain
     * @return bool
     */
    private function isValidDomain($domain)
    {
        if (empty($this->config['domain']) || empty($domain)) {
            return false;
        }

        // $this->config['domain'] が複数(配列)の場合もあるので統一
        $configs = $this->config['domain'];
        if (!is_array($configs)) {
            $configs = [$configs];
        }

        // 判定関数
        $isValid = function ($configDomain, $paramDomain) {
            $compareKeys = array('scheme', 'host', 'port');
            foreach ($compareKeys as $key) {
                if (!empty($configDomain[$key])) {
                    if ($configDomain[$key] !== $paramDomain[$key]) {
                        return false;
                    }
                }
            }
            return true;
        };

        foreach ($configs as $config) {
            $configDomain = parse_url($config);
            $paramDomain = parse_url($domain);
            if ($isValid($configDomain, $paramDomain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 入力値のトリムとHTML文字エスケープ
     * @param $data
     * @return array
     */
    private function escape($data)
    {
        $func = function($data) use (&$func) {
            $ret = array();
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $ret[$key] = $func($value, $key);
                } else {
                    $value = trim(mb_convert_kana($value, "s"));
                    $value = htmlspecialchars($value);
                    $ret[$key] = $value;
                }
            }
            return $ret;
        };
        return $func($data);
    }

}
