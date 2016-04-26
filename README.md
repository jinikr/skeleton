## skeleton

Phalcon의 장점은 살리면서 구조화한 Micro Mvc Framework의 Wrapper이다.


### 환경
php7, Phalcon 2.1.x버전을 사용.

### 설치

```
git clone https://github.com/phalcon/zephir
cd zephir
./install -c

git clone http://github.com/phalcon/cphalcon
cd cphalcon
git checkout 2.1.x
zephir build --backend=ZendEngine3

git clone http://github.com/yejune/skeleton
cd skeleton
composer install
```

### 다음과 같은 구조.
```
home/
    app/
        config/
        controllers/
        models/
        helpers/
        middlewares/
        models/
        traits/
    public/
        css/
        img/
        js/
        .htaccess
        index.php
```

##### home이 base dir이다.
```
<?php echo __BASE__; ?>
```

```
Result : /home
```

##### PSR-1, PSR-2를 따라 클래스 이름은 반드시 `Studlycaps`

##### PSR-4 Autoloader 규칙을 따르며 네임스페이스 \App는 /home/app 폴더에 대응된다.


##### mod_rewrite를 사용하며 모든 요청을 index.php로 전달한다.


public/.htaccess

```
AddDefaultCharset UTF-8

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php?_url=/$1 [QSA,L]
</IfModule>
```

public/index.php


```
<?php

use \App\Helpers\Cores\Bootstrap;
use \Phalcon\DI\FactoryDefault as Di;
use \App\Helpers\Cores\Mvc\Micro;

try
{
    define('__BASE__', dirname(dirname(__FILE__)));

    {
        include_once __BASE__.'/vendor/autoload.php';
        include_once __BASE__.'/app/helpers/debug.php';
        include_once __BASE__.'/app/helpers/function.php';
    }

    (new Bootstrap(new Di))(new Micro)->handle();
}
catch (\Exception $e)
{
    echo '<pre>';
    print_r($e->getMessage());
    echo "\n\n";
    print_r($e->getTraceAsString());
    echo '</pre>';
}
```

### 파일 구조 설명
#### vendor
composer가 생성한 패키지를 보관

| file                   | description  |
| ---------------------| -----|
| autoload.php                        | 패키지 autoloader |

####app/helpers
Phalcon의 일부 클래스의 기능을 변경하기 위한 함수/클래스 폴더

| file        | namespace           | description  |
| ------------- |-------------| -----|
| function.php   | -                  | 사용자 함수 정의 |
| debug.php      | -                  | 디버그 모드 활성화 |
| cores/Boostrap.php     | \App\Helpers\Cores\Bootstrap | 각종 환경 설정 |
| cores/Http/Request.php      | \App\Helpers\Cores\Http\Request | Phalcon\Http\Request가 지원하지 않는 기능을 추가하여 대체 |
| cores/Mvc/Micro.php     | \App\Helpers\Cores\Mvc\Micro | Micro Framework의 route기능을 수정하여 대체 |


####app/config
각종 설정 파일

| file              | description  |
| ---------------| -----|
| environment.php |  설정 파일 |
| route.php |  route 설정 파일 |
| environment/ |  환경별 설정 파일 폴더 |
| environment/localhost.php |  로컬 환경 설정 파일 |
| environment/development.php |  개발 환경 설정 파일 |
| environment/staging.php |  스테이징 환경 설정 파일 |
| environment/product.php |  프로덕트 환경 설정 파일 |

#### app/controllers
app/config/route.php에 지정된 url이 호출하는 controller 클래스

| file        | namespace           |
| ------------- |-------------|
| V1.php   | \App\Controllers\V1        |
| V2.php   | \App\Controllers\V2       |

#### app/models
db CRUD 클래스

| file        | namespace         |
| ------------- |------------|
| User.php   | \App\Models\User    |
| Graph.php   | \App\Models\Graph    |

#### app/traits
코드 재사용을 위한 traits 클래스

| file        | namespace             |
| ------------- |------------|
| Auth.php   | \App\Traits\Auth             |
| Singleton.php   | \App\Traits\Singleton           |