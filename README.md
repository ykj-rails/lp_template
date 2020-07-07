# template_lp
LP用テンプレ。
スピード重視のため、色々と突貫。

## 開発環境
- node v12.13.1
    - .node-versionファイル追加済み（nodenvによる自動切替可能）
- npm 6.12.1(yarnコマンドを使わないこと！)
- pug(htmlテンプレートエンジン)
- stylus(CSSメタ言語)
- webpack v4.41.2(gulpは未使用)

## Install
```
$ npm ci
```

## Usage
```
# ローカルサーバ立ち上げ&監視
$ npm start

# iconfont生成
$ npm run iconfont

# -------------------- # 

# build(開発)
$ npm run dev

# build(本番)
$ npm run build

```
### buildコマンド起動時の注意点
**buildコマンドを間違えるとパスがおかしくなるので注意！**  
buildコマンド時のパスの制御は下記で行っているので、要確認！

- `src/_pug/_config.pug`
- `src/js/modules/lp-form.js`

**build前に確認する作業**

- _config.pugのmodeを変更する。
- src/api/config.phpの`[to必須] 管理者に送るメールアドレス`部分を テスト用、本番用でメールの送信先を変更する。

## ディレクトリ構成

```
project
├── public（ここに生成されたファイルをFTPなどでアップする）
│   
├── src（諸々弄るファイルが入ってる）
│   ├── _pug
│   │   ├── include（head, header, footerなどのinc系ディレクトリ）
│   │   ├── tmp（テンプレートが入ってる。default［通常］とthanks［応募完了］の2種類）
│   │   ├── _config.pug（ページ設定用ファイル。meta情報もここで管理。）
│   │   └── _mixins.pug（ページで繰り返し利用されるものをmixinとしてまとめてます。追加大歓迎！）
│   ├── _stylus
│   │   ├── _foundation（base系。mixinやvariablesもここで管理。iconFont.stylもここに入る）
│   │   ├── _layout（header, footer, wrapper, mainのstylファイル）
│   │   ├── _object
│   │   │   ├── _component（コンポーネントはここにまとめる）
│   │   │   ├── _project（ページ固有のスタイルはここにまとめる）
│   │   │   └── _utility（ユーティリティクラス。既に用意してあるので、必要に応じてimportしてください）
│   │   └── style.styl（stylファイルをここで全部inportしてる。必要に応じてimportを調整してください）
│   ├── api（lp-formのファイル置き場。詳細は中にあるREADMEを読んでね）
│   ├── font（fontファイル置き場）
│   ├── img（画像置き場）
│   ├── js
│   │   ├── libs（プラグイン置き場。現在picturefillのみ）
│   │   ├── modules（フォームのバリデーションやscroll、追従などいくつか既に入っているので、必要に応じて使用してください）
│   │   └── app.js
│   ├── complete.pug
│   └── index.pug
├── package-lock.json
├── package.json
└── webpack.config.js
```

## HTML
- pug（htmlテンプレートエンジン）使用
- `src/_pug/_config.pug`内で、パス制御等を行っているので、必ず中身をチェックすること！

### htmlからphpに変換が必要なものがある場合
shellscriptで使用している`rename`はbrewからインストールする。
`brew install rename` でインストール可能。

## CSS
- stylus（CSSメタ言語）使用
- ディレクトリ構成はFLOCSS
- 命名規則はBEM（MindBEMding）
  - 単語が4つ以上並ぶ場合はプリフィックスで_ - をつけ省略OK
  - -pプリフィックスのみつけない

## JS
- javascript（ES6）
- jquery未使用
- 追従、タブ切り替え、フォームのバリデーションや送信などは実装済み（細かい部分は調整してください）。
- plugin / ライブラリ
    - picturefill

## フォームについて
- lp-form使用
- バリデーション・確認・送信のJSは実装済み
    - `src/_pug/include/_inc_form.pug`に詳細を記載しているので、使い方についてはそっちを確認ください。
