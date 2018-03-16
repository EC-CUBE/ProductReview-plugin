# 商品レビュー EC-CUBE 3.n 用サンプルプラグイン

EC-CUBE 3.n プラグイン実装参考用のサンプルプラグインです。  

## 機能概要
- フロントページへの表示の追加
- 入力フォームのあるページの追加
- データベースへのテーブルの追加と保存

## インストール方法

EC-CUBE 3.n でのプラグインのインストール方法については、EC-CUBE 3.n 開発ドキュメントのプラグインのインストールの項を参考にしてください。

## 利用方法

プラグインをインストールしただけでは、商品詳細ページに何も表示されません。
商品詳細ページに商品レビューを表示すするためには、以下の「表示用のコード」を記述する必要があります。

### 記述する表示用コード

```Twig
{{ include('@ProductReview/default/product_review.twig', ignore_missing = true) }}
```
### 記述する場所

管理画面から `コンテンツ管理 > ページ管理 > 商品詳細ページ` へ移動すると、商品詳細ページのTwigコードが編集できます。
下記のように、Twigコードの末尾に表示用コードを追加記述してください。

```Twig
          :
        (省略)
          :
        {% if Product.freearea %}
            {# <div class="ec-productRole__description">{{ include(template_from_string(Product.freearea)) }}
            </div> #}
        {% endif %}
        
        {# 商品レビューの表示 #}
        {{ include('@ProductReview/default/product_review.twig', ignore_missing = true) }}
        
    </div>
{% endblock %}
```

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
