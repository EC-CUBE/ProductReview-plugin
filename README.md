# 商品レビュー EC-CUBE 4系

[![Build Status](https://travis-ci.org/EC-CUBE/ProductReview-plugin.svg?branch=feature%2F1.0.0)](https://travis-ci.org/EC-CUBE/ProductReview-plugin)
[![Build status](https://ci.appveyor.com/api/projects/status/oni9ptnqfs37uqdb?svg=true)](https://ci.appveyor.com/project/ECCUBE/ProductReview-plugin-9n48w)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5c61b4f6-edad-4908-9a9a-6b4f38574a93/mini.png)](https://insight.sensiolabs.com/projects/5c61b4f6-edad-4908-9a9a-6b4f38574a93)
[![Coverage Status](https://coveralls.io/repos/github/EC-CUBE/ProductReview-plugin/badge.svg)](https://coveralls.io/github/EC-CUBE/ProductReview-plugin)

## 機能概要
- フロントページへの表示の追加
- 入力フォームのあるページの追加
- データベースへのテーブルの追加と保存

## インストール方法

EC-CUBE4系でのプラグインのインストール方法については、EC-CUBE4系開発ドキュメントのプラグインのインストールの項を参考にしてください。

## カスタマイズの詳細

### プラグイン全般

- データベースへのテーブル(plg_product_review)の追加
- ルーティングの追加
- メッセージの追加
- ログの出力

### フロント画面

- 商品詳細ページに投稿されたレビューを表示
  - フロントページへの表示の追加
  - データベースからの読み出し
- 「レビューを書く」ボタンを押すとレビュー入力画面を表示
  - ページの追加
  - フォームの追加)
- レビューの投稿
  - POST処理
  - データベースへの保存

### 管理画面

- 機能なし
