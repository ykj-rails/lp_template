<?php
namespace Action;

use Bean\Validate;
use Bean\Mail;

require_once dirname(__FILE__).'/AbstractActionBase.php';
require_once dirname(__FILE__).'/../bean/Validate.php';
require_once dirname(__FILE__).'/../bean/Mail.php';

/**
 * Created by PhpStorm.
 * User: hoshi
 * Date: 2017/08/02
 * Time: 16:24
 */
class SendAction extends AbstractActionBase
{

    /**
     * execute
     */
    public function execute()
    {
        $config = $this->config();

        // ここでもvalidate
        $validate = new Validate($this->data, $config['validate']);
        if ($errors = $validate->errors()) {
            return $this->responseError($errors, 422, 'invalid');
        }

        // カレントの言語を日本語に設定する
        mb_language('ja');
        // 内部文字エンコードを設定する
        mb_internal_encoding('UTF-8');

        // メール送信
        $adminResult = false;

        // from
        $from = !empty($config['fromName']) ? mb_encode_mimeheader($config['fromName']) : '';
        $from .= !empty($from) ? "<{$config['fromEmail']}>" : $config['fromEmail'];

        // return path
        $returnPath = !empty($config['returnPath']) ? $config['returnPath'] : $config['fromEmail'];

        // 管理者に送信
        if (!empty($config['adminEmail'])) {
            $toAdmin = new Mail();
            $toAdmin->setFrom($from);
            $toAdmin->setTitle($config['adminTitle']);
            $toAdmin->setReturnPath($returnPath);
            $toAdmin->setMessage($this->createMessageToAdmin());

            if (is_array($config['adminEmail'])) {
                $toAdmin->setTo($config['adminEmail']['to']);
                if (!empty($config['adminEmail']['cc'])) {
                    $toAdmin->setCc($config['adminEmail']['cc']);
                }
                if (!empty($config['adminEmail']['bcc'])) {
                    $toAdmin->setBcc($config['adminEmail']['bcc']);
                }
            } else {
                $toAdmin->setTo($config['adminEmail']);
            }

            $adminResult = $toAdmin->sendMail();
        } else {
            $adminResult = true;
        }

        if ($adminResult && !empty($config['userEmail']) && !empty($this->data[$config['userEmail']])) {
            // 管理者に送信できて入ればユーザにも送信(メールパラメータが存在しない場合は何もしない)
            $toUser = new Mail();
            $toUser->setFrom($from);
            $toUser->setTo($this->data[$config['userEmail']]);
            $toUser->setTitle($config['userTitle']);
            $toUser->setReturnPath($returnPath);
            $toUser->setMessage($this->createMessageToUser());
            $userResult = $toUser->sendMail();
        } else {
            $userResult = true;
        }

        if ($adminResult && $userResult) {
            return $this->responseSuccess();
        } else {
            return $this->responseError(500, null, '送信失敗しました。');
        }
    }

    /**
     * ユーザ用メール内容
     * @return mixed|string
     */
    private function createMessageToUser()
    {
        return $this->createMessage(dirname(__FILE__) . '/../view/' . $this->contentName . '/toUser.tpl');
    }


    /**
     * admin用メール内容
     * @return mixed|string
     */
    private function createMessageToAdmin()
    {
        return $this->createMessage(dirname(__FILE__) . '/../view/' . $this->contentName . '/toAdmin.tpl');
    }

    /**
     * メール内容作成
     * @param $template
     * @return mixed|string
     */
    private function createMessage($template)
    {
        ob_start();
        require_once $template;
        $body = ob_get_contents();
        ob_end_clean();

        // メール内の動的文言を置換
        $config = $this->config();
        foreach ($config['validate'] as $name => $rule) {
            $value = @$this->data[$name];
            if (is_array($value)) {
                $body = str_replace("__{$name}__", implode("\n", $value), $body);
            } else {
                $body = str_replace("__{$name}__", $value, $body);
            }
        }

        // 予約語
        // send_date
        $body = str_replace("__send_date__", date('Y/n/j H:i:s'), $body);
        // ip
        $body = str_replace("__ip_address__", $_SERVER['REMOTE_ADDR'], $body);
        // user agent
        $body = str_replace("__user_agent__", $_SERVER['HTTP_USER_AGENT'], $body);


        return $body;
    }
}
