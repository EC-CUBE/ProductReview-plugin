# 商品レビュー管理プラグイン

[![Build Status](https://travis-ci.org/EC-CUBE/ProductReview-plugin.svg?branch=product-review-renew)](https://travis-ci.org/EC-CUBE/ProductReview-plugin)
[![Build status](https://ci.appveyor.com/api/projects/status/833sedvtsvf01hcm/branch/product-review-renew?svg=true)](https://ci.appveyor.com/project/lqdung-lockon/productreview-plugin/branch/product-review-renew)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/9cdecf86-cff0-4d66-a6d4-9ae715ec1741/mini.png)](https://insight.sensiolabs.com/projects/9cdecf86-cff0-4d66-a6d4-9ae715ec1741)
[![Coverage Status](https://coveralls.io/repos/github/eccubevn/ProductReview-plugin/badge.svg?branch=product-review-renew)](https://coveralls.io/github/eccubevn/ProductReview-plugin?branch=product-review-renew)

## 概要
商品詳細ページに、商品へのレビューを表示・投稿することができるようになるプラグインです。
管理画面から、レビュー公開・非公開の切り替えや、一覧をCSVで保存することができます。

## フロント

### F1:商品詳細ページから商品レビューを投稿することができる。

|入力項目|必須|その他|
|---|:---:|---|
|投稿者名|○|&nbsp;|
|投稿者URL|&nbsp;|&nbsp;|
|性別|&nbsp;|男性/女性|
|おすすめレベル|○|★〜★★★★★|
|タイトル|○|&nbsp;|
|コメント|○|&nbsp;|

投稿内容は管理者が確認して表示設定するまで公開されない。

### F2:商品レビューを商品詳細ページに一覧で見ることができる。
- 投稿日時の降順で5件まで表示（設定でカスタマイズ可能）
- 管理者によって公開設定されたレビューのみ表示
- 表示内容
    - タイトル
    - 投稿日時
    - 投稿者名(投稿者URLがある場合はリンク)
    - おすすめレベル
    - コメント
    
## 管理画面

### A1:投稿された商品レビューを管理できる
#### A1-1:商品レビューを検索できる
- 検索条件
    - 投稿者名
    - 投稿者URL
    - 商品名
    - 商品コード
    - 性別
    - おすすめレベル
    - 投稿日(開始/終了)

#### A1-2:検索結果をCSV形式のファイルでダウンロードできる
|項目名|備考|
|---|---|
|商品名|&nbsp;|
|レビュー表示|公開/非公開|
|投稿日|&nbsp;|
|投稿者名|&nbsp;|
|投稿者URL|&nbsp;|
|性別|男性/女性|
|おすすめレベル|★〜★★★★★|
|タイトル|&nbsp;|
|コメント|&nbsp;|

#### A1-3:商品レビュー編集できる

#### A1-4:商品レビューを削除できる

### A2:プラグインの設定画面で、商品レビューの表示件数を設定できる

## オプション
### 商品レビューの表示位置を変更することができる。
商品詳細ページのtwigファイルに`<!--# product-review-plugin-tag #-->`と入力すると、その位置に表示を行う。
