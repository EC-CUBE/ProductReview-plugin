# 商品レビュー EC-CUBE 3.n 用サンプルプラグイン

EC-CUBE 3.n プラグイン実装参考用のサンプルプラグインです。  

## 機能概要
- フロントページへの表示の追加
- 入力フォームのあるページの追加
- データベースへのテーブルの追加と保存

## 利用方法

プラグインをインストール後、「表示用のコード」を記述することで、商品詳細ページに商品レビューを表示することができます。

### 追加場所
管理画面 > コンテンツ管理 > ページ管理 > 商品詳細ページ

### 追加する表示用コード

```Twig
{{ include('@ProductReview/default/product_review.twig', ignore_missing = true) }}
```

ページ末尾に表示する場合の記述例
```Twig
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
