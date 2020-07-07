<?php
return array(

    /**
     * 送信するメールの情報 default(各項目未指定の場合こちらが有効化)
     */
    'default'   => array(
        /**
         * API関連
         */
        // [必須] API実行を許すURL: ["http://example.com:port"(portは省略可): 複数(開発と本番とか)指定したいなら配列で
        'domain' => array('http://localhost:8888','http://testaccount.main.jp'),
        // [必須] API Key(適当な文字列を指定): API Postパラメータにname="key"として含める
        'key' => 'S8PMa9Db3Edm3PE3TGywMqRhfaG6YmkMexh',

        /**
         * メール設定関連
         */
        // [必須] fromメールアドレス
        'fromEmail'  => 'ayaka_yanagida@012grp.co.jp',
        // [必須] from名称(任意、必要ない場合空で)
        'fromName'   => 'はじめてのテレワーク相談窓口',
        // [to必須] 管理者に送るメールアドレス
        'adminEmail' => array(
            'to' => array(
                'ayaka_yanagida@012grp.co.jp'
                // 'hajime-telework@012grp.co.jp'
                // 'munehiro_okamoto@012grp.co.jp'
                // 'takahiro_bitou@012grp.co.jp'
            ),
            'cc' => array(),
            'bcc' => array(),
        ),
        // 管理者に送るメールタイトル
        'adminTitle' => '【なまえ】管理者用たいとる',
        // Return Path (ない場合fromEmailと同じ)
        'returnPath' => '',
        // [必須] 入力者に送るメールアドレスのinput name
        'userEmail'  => 'mail',
        // 入力者に送るメールタイトル
        'userTitle'  => '【なまえ】ユーザ用タイトル',


        //  * validation関連
        //  */
        // [必須] validate
        'validate' => array(
            'company' => array('required'),
            'test' => array('requiredAny')
        )
    ),

    /**
     * "contact/"のAPI設定
     */
    'contact' => array(
        // defaultから更新するものを書く

        // 管理者に送るメールタイトル
        'adminTitle' => '【管理者用】はじめてのテレワークに問い合わせがありました',
        // Return Path (ない場合fromEmailと同じ)
        'returnPath' => '',
        // [必須] 入力者に送るメールアドレスのinput name
        'userEmail'  => 'mail',
        // 入力者に送るメールタイトル
        'userTitle'  => 'はじめてのテレワークへのお問い合わせを承りました'
    ),
);
