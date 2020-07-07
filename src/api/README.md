LP Form (Ver 2.x API版)
======

LP用のformをgulpでつくるもの。

具体的には、
- validate用のAPI
- メール送信用のAPI

のバックエンド側処理を用意し、フロント側で自由に叩きます。

**※これは2.x(API版)の説明です。gulp組込版は1系です。**

## ■なぜAPI版にしたか

- 1系組込版だとフォームの細かいUI(JSで項目を増やす等)に対応しにくく、UI構築の制約になる。
- 同時に遷移も制約を課す必要がある(フォーム > 確認 > 完了の画面で遷移することが必須とか)。
- 組み込むための規約をキツキツにする必要がある(formのnameにlp-formと指定するとか)。

など一言で言えば自由度が低いため。

つまりディレクターやデザイナーの自由なアイデアを実現するには
結局個別対応が必要になってきたため、効率化を目指せず、
せっかくのパッケージ化の意義が薄れてきたため。

## ■注意事項

あらゆるパターンを想定して作っているものではないので、需要があれば作り込みます。

具体的には以下のような状況です。

- ライブラリ等の細かいバージョンでの確認はしていません。
- gulpで叩いて書き出されるdestディレクトリ内に、API用の処理を含めたディレクトリ(例: api/ , gulp taskで設定)を1つ作ります。


## ■動作確認環境と必要なもの

### front

- node v6.4.0
- npm 5.5.1
- gulp 3.9.1

### back

- PHP 5.6 


## インストール

このリポジトリをcloneして、php/lp-form に配置

以下のような構造になる

```
.
├── README.md
├── build
├── dest
├── gulp
├── gulpfile.babel.js
├── node_modules
├── package.json
├── php
│   └── lp-form
│       ├── README.md
│       ├── VERSION.txt
│       ├── action
│       ├── bean
│       ├── config.php
│       ├── config.php.example
│       ├── index.js
│       ├── index.php
│       ├── router.json
│       └── view
└── src
```

## ■基本設定

### /router.json

APIのURLを設定します。以下のような形式で指定します。

```
{
    "コンテンツ名": {
        "validate": "バリデーションaction名",
        "send": "メール送信action名",
    }
}
```
上記のように設定すると以下のAPI URLが発行されます。

(※gulp側でdest/api/に設置するようにした場合)

* バリデーション用API URL
    - http://example.com/api/コンテンツ名/バリデーションaction名
* メール送信用API URL
    - http://example.com/api/コンテンツ名/メール送信action名

routerにはいくつかの書き方ができます。
```
■ validateは使わずsendしか使わない場合、指定しなくてもOK
    {
        "contacts": {
            "send": "send"
        }
    }

■ "*"指定すると、
    {
        "contacts": "*"
    }
    
    以下と同じ

    {
        "contacts": {
            "validate": "validate",
            "send": "send"
        }
    }
    
■ 1サイト内に複数Formがある場合を踏まえ、複数コンテンツ発行可能。
    {
        "contacts": "*",
        "inquiry": "*",
        "toiawase": "*"
    }
```

### /config.php

サーバ側の挙動を設定。
default設定と各コンテンツ(routerで設定したコンテンツ名)用の設定があります。

#### 構成
```
    'default' => array(
        // デフォルト設定
    ),
    'コンテンツ名' => array(
        // routerで設定した「コンテンツ名」の設定
        // ここで設定しなかった項目はdefaultのものが使われます。
    ),
    'コンテンツ名2' => array(
        // routerで複数のコンテンツを設定した場合、記述します。
    ),
```

項目は以下、 

| key | 必須 | 形式 | 説明 |
| ---- | ---- | ---- | ---- |
| domain | ○ | 文字列 or 配列 | API利用するドメインhttpから指定。port指定可。 |
| key | ○ | 文字列 | API key。Ajaxリクエスト時この文字列を"key"パラメータとして送信する必要がある。 |
| fromEmail | ○ | 文字列 | メールのfrom指定されるメールアドレス |
| fromName |  | 文字列 | メールのfrom指定される名前 |
| adminEmail | ○ | 文字列 or 配列 | 管理者用メールアドレス。send時に送信される。文字列の場合to指定となる。toは必須。 |
| adminTitle |  | 文字列 | 管理者に送信するメールのタイトル。 |
| returnPath |  | 文字列 | 入力者・管理者に送信するメールのreturn-path(ない場合fromEmailと同じ)。 |
| userEmail |  | 文字列 | 入力者に送信するメールアドレスのinput name |
| userTitle |  | 文字列 | 入力者に送信するメールのタイトル。 |
| validate | ○ | 配列 | バリデーションルール |

※ /config.php.exampleのコメントの通りなので、こちらも参照


#### 入力項目とバリデーション設定
"validate"項目には以下のようにバリデーションルール指定

```
    'validate' => array(
        // 'inputのname' => array('ルール1', 'ルール2') の形で指定
        'name'    => array('required'),
        'phone'   => array('required'),
        'url'     => array('url'),
        // userEmailに含まれる項目はemailバリデーションを含むことが望ましい
        'mail'    => array('email'),
        // 正規表現の場合はデリミタ/~~/で挟む
        'tel' => array(
            'required', 
            '/^(0\d{1,4}[\s-]?\d{1,4}[\s-]?\d{1,4}|\+\d{1,3}[\s-]?\d{1,4}[\s-]?\d{1,4}[\s-]?\d{1,4})$/')
        ),
        // input nameが複数項目(tags[])の場合"[]"は外して"tags"記述
        'tags' => array('requiredAny'),
        // tagsの値に"インターネット回線"が含まれる場合必須
        'note1' => array('requiredIf:tags:インターネット回線'),
        // tagsの値が存在する場合必須
        'note2' => array('requiredIf:tags'),
        // tagsの値に"インターネット回線"が含まれない場合必須
        'note3' => array('requiredUnless:tags:インターネット回線'),
        // tagsの値が存在しない場合必須
        'note4' => array('requiredUnless:tags'),
        // 何もバリデーションしない場合もプログラムで利用するので記述すること
        'weekday' => array(),
        'time'    => array(),
    )
```

以下のような形で入れる。
```
'inputのname' => array('バリデーションルール1', 'バリデーションルール2'),
```

複数チェックボックスなど複数項目の際、input nameに"[]"を含む場合"[]"は外して指定
```
// url[] の場合
'url' => array(
    'requiredAny' // ←これはurl[]の項目が1つ以上存在することをチェック
    'url' // ←これはurl[]の「各項目ごと」にurl形式かをチェックする
),
```

何もバリデーションしない場合もプログラムで認識するために以下のように記述する。
```
// 何もバリデーションしない場合もプログラムで利用するので記述すること
'body' => array(),
```


#### バリデーションルール

バリデーションルールは現状以下を用意。

- required
    - 必須
- email
    - メールアドレス
- url
    - メールアドレス
- 正規表現
    - "/〜〜/"とデリミタで囲む
- requiredAny (複数項目用)
    - 複数項目の場合に1つ以上要素があることをチェック(複数項目でない場合無視される)
- requiredIf:name:value(option)
    - form nameの値がvalueの場合必須(valueが複数:配列の場合、含まれる場合必須)
    - valueは指定なくてもよく、その場合form name存在時必須チェックする
        - 例) requiredIf:name
- requiredUnless:name:value(option)
    - requiredIfの逆。
    - form nameの値がvalueでない場合必須(valueが複数:配列の場合、含まない場合必須)
    - valueは指定なくてもよく、その場合form name空の時必須チェックする
        - 例) requiredUnless:name

バリデーションエラーが起きると、APIは422エラーを返す。
validate action時はもちろん、send action時もチェックは行われる。

## ■Debug MODE
開発時は細かいエラーが必要な場合もあるので、出せるようにしました。
/index.phpの最上部に以下を記述してください。

```
ini_set('display_errors', 'On');
```

もともとPHPエラーを出す設定なのですが、APIのエラーメッセージが詳細まで出るようにしています。

**※本番では必ず記述を削除してください。**

## ■API仕様

APIはセキュリティ対策のため、以下のような制約を設けています。

- POST methodしか受け付けません。
- AjaxでのPOSTしか受け付けません。
- Ajax時"key"という名前のパラメータで、config.phpで指定の文字列を入力値と共に送信してください。
送信しない場合、受け付けません。
- HTTP_ORIGINがconfig.phpの"domain"項目で指定のscheme/domain/portからしか受け付けません。
- 同様にrefererもconfig.phpの"domain"項目で指定のscheme/domain/portからしか受け付けません。

上記の制約によるエラーはDebug MODE=onだと500エラーになり、どの制約に引っかかったかメッセージがでます。

offだと404 Not Foundとだけの表示となりますのでこれは本番用です。

### APIリクエストする

単純にPOSTすればOKです。

```
// jQueryの例
$.ajax({
    url: '/api/contacts/validate',
    type: 'post',
    cache: false,
    data: {
        'key': 'API Key', // ←これ必須
        // ↓以下はform入力
        'name': 'お名前',
        'mail': 'user@example.com',
        'tags': [ // ←"tags[]"のような複数項目は配列で
            'タグ1',
            'タグ2'
        ]
    },
    dataType: 'json',
}).done(function(res) {
    // 成功時の処理
}).fail(function(jqXHR, textStatus, errorThrown) {
    if (jqXHR.status === 422) {
        // バリデーションエラー
    } else {
        // それ以外のエラー
    }
});
```


### APIレスポンス

#### 成功時レスポンス
code=200で返ってきます。
```
{
    "code": 200
}
```

#### バリデーションエラー時レスポンス
code=422で返ってきます。
```
{
    "code": 422,
    // 引っかかったエラー種類
    "errors": {
        "name": ["required"],
        "mail": ["email"]
        "phone": ["required"]
        "tags": [ // ←"tags[]"のような複数項目は配列で
            ["requiredAny"]
        ],
    },
    "message": "バリデーションエラーがあります。"
}
```

#### その他のエラー時レスポンス
code=404 or 500で返ってきます。
```
{
    "code": 404,
    "message": "Not Found."
}
```

## ■メール内容設定

以下のファイルを設定します。

- view/コンテンツ名/toAdmin.tpl
    - 管理者向けのメール内容を設定
- view/コンテンツ名/toUser.tpl
    - 入力者向けのメール内容を設定

ファイル内では、入力項目を以下のように出力できます。
```
例) input nameがcompanyの場合

__company__
```

また、input要素以外でも出力可能なものがあります。
```
__send_date__
    送信日時を出力します。
__ip_address__
    送信者のIPアドレスを出力します。
__user_agent__
    送信者のUser Agentを出力します。
```


## ■gulp task設定

### task作成

./dest に api というディレクトリとして出力

(APIアクセスURLが "/api/コンテンツ名/action名" となります)
```
import gulp from 'gulp';
import lpForm from '../../src/php/lp-form/';

gulp.task('lpForm', () => {
    lpForm.dest('./dest/api')
});
```

あとは様々な場所で上記taskを呼び出せばいい。

**例) defaultに加える**
```
gulp.task('default', function(callback){
	return runSequence(
		'stylusReload',
		'pugReload',
		'jsReload',
		['watch', 'server', 'jsBundle', 'copycss', 'copyimg', 'copyFont'], 'lpForm', callback);
});
```

**例) pug更新時にPHPソースにも更新htmlを吐き出す**
```
gulp.task('pugReload', function(callback){
	return runSequence(['pug', 'bsReload'], 'lpForm', callback);
});
```

**例) PHPのソース(tpl変更等)をwatchする**
```
gulp.watch(['php/lp-form/**'], ['lpForm']);
```

## ■ローカル開発時

dest配下を"browser-sync"あたりを使ってブラウザ表示させたりすると思いますが、
API実行のためにはPHPが動く必要があるので、proxyにPHP動く場所を指定ください。

```
gulp.task('server', () => {
    browserSync({
        ghostMode: {
            clicks: false,
            forms: false,
            scroll: false
        },
        open: 'external',
        online: true,
        // ↓こういうの指定
        proxy: 'localhost:8888', // MAMP Server
    });
});
```


## ■デプロイ

./dest/をUPすればOKです。