# 勤怠管理アプリ

模擬案件で作成した勤怠管理アプリです。
スタッフ登録、ログイン、出勤時間、休憩開始・終了、退勤を記録することができます。

## 環境構築

**Docker ビルド**

1. `git@github.com:hosokawauso/attendance-management.git`
   `attendance-management.git`
2. DockerDesktop アプリを立ち上げる
3. `docker-compose up -d --build`

**Laravel 環境構築**

1. `docker-compose exec php bash`
2. `composer install`
3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.env ファイルを作成
4. .env に以下の環境変数を追加

````text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

````

5. アプリケーションキーの作成

```bash
php artisan key:generate
````

6. マイグレーションの実行

```bash
php artisan migrate
```

7. シーディングの実行

```bash
php artisan db:seed
```

## テストケース

このプロジェクトは Docker Compose（php / mysql）環境を前提に、Laravel の Feature テストを中心に動作確認します。  
※ **開発用DB（laravel_db）とは別に、テスト専用DB（demo_test）を作成**して実行してください（事故防止）。

### 1. テスト用DBを作成（MySQLコンテナ）

1.`docker-compose exec mysql bash`

2.`mysql -u root -p`

3.`> CREATE DATABASE demo_test`

### 2. .env.testingを作成

1. `docker-compose exec php bash`
2. `cp .env .env.testing`
3. .env.testing に以下の環境変数に追加・変更

```text
APP_ENV=testing
APP_KEY=
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=demo_test
DB_USERNAME=root
DB_PASSWORD=root

````

4. アプリケーションキーの作成

```bash
php artisan key:generate --env=testing
````

5. マイグレーションの実行

```bash
php artisan migrate --env=testing
```

6. Feature/Unit テスト

```bash
docker compose exec app php artisan test
```

## 使用技術(実行環境)

・PHP 7.4.9  
・Laravel 8.83.8  
・MySQL 8.0.26  
・Fortify 1.19.1  
・Blade  
・Docker/Docker Compose  
・Git/GitHub  
・MaliHog  

## ER 図
![alt text](attendande-management_ER_Figure.png)

## URL

- 開発環境: http://localhost/
- phpMyAdmin: http://localhost:8080/
- MaliHog: http://localhost:8025/
