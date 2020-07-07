<?php
namespace Action;

use Bean\Validate;

require_once dirname(__FILE__).'/AbstractActionBase.php';
require_once dirname(__FILE__).'/../bean/Validate.php';

/**
 * Created by PhpStorm.
 * User: hoshi
 * Date: 2017/08/02
 * Time: 16:24
 */
class ValidateAction extends AbstractActionBase
{

    /**
     * execute
     */
    public function execute()
    {
        $validate = new Validate($this->data, $this->config()['validate']);
        if ($errors = $validate->errors()) {
            return $this->responseError(422, $errors, 'バリデーションエラーがあります。');
        } else {
          require_once dirname(__FILE__).'/Session.php';
            return $this->responseSuccess();
        }
    }

}
