# PHPでのmessenger bot sample

## Requirements
* Heroku
* composer
* php

## 準備するもの

`.env`ファイルを作成し、以下のように編集する

```.env
FACEBOOK_PAGE_ACCESS_TOKEN="ここに自分のアクセストークン"
FACEBOOK_PAGE_VERIFY_TOKEN="ここに自分で設定した検証用トークン"
```

`composer`をインストール

Macの場合はhomebrewでいれる
```
$ brew install homebrew/php/composer
```

## 始め方

```
$ composer install
```
