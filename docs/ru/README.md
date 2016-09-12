#Документация

##Использование

Сперва создайте модуль `admin` в директории модулей своего проекта (например `/path/to/project/frontend/modules/admin`). Создайте класс `Module` расширяющий `asdfstudio\admin\Module`:

```php
use asdfstudio\admin\Module as AdminModule;

class Module extends AdminModule {

}
```

Теперь добавьте следующте строки в свой кофигурационный файл, в секции `modules` и `bootstrap`:

```php
return [
    'bootstrap' => ['admin'],
    'modules' => [
    	...
        'admin' => [
            'class' => 'frontend\modules\admin\Module',
        ],
        ...
    ],
    ...
];
```

На этом всё. Теперь вы можете попасть в админку, зайдя на `/admin`.


###Регистрация моделей

Теперь нужно зарегистрировать первую модель в панели управления. Создайте класс `UserEntity` в своей папке `admin/entities`.
Класс должен наследовать `asdfstudio\admin\base\Admin`.

```php
use asdfstudio\admin\base\Admin;
use common\models\User;

class UserEntity extends Entity
{
    public function labels()
    {
        return ['User', 'Users']; // названия, используемые в админке
    }

    public function slug()
    {
        return 'user'; // путь внутри админки. например /admin/manage/user[/<id>[/edit]]
    }

    public function model()
    {
        return [
            'class' => User::className(); // класс модели пользователя
        ];
    }
}
```

Зарегистрируйте эту сущность в своём модуле:

```php
use asdfstudio\admin\Module as AdminModule;

class Module extends AdminModule {
	...
	public function init() {
		parent::init();

		$this->registerEntity(UserEntity::className()); // этот метод регистрирует сущность в админке

		$this->sidebar->addItem(UserEntity::className()); // а этот добавляет ссылку в сайдбар
	}
	...
}

```

Теперь переходите на `/admin/manage/user` и увидите таблицу со всеми пользователями сайта.

Для примера смотрите [asdf-studio/yii2-blog-module](https://github.com/asdf-studio/yii2-blog-module).


####Формы

Если вы по каким-либо причинам не хотите показывать в форме все поля модели, то вам нужно создать отдельный класс формы, наследующий `asdfstudio\admin\forms\Form`, и перечислить в нём необходимые поля:

```php
use asdfstudio\admin\forms\Form;

class UserForm extends Form
{
    public function fields() { // список полей формы, выводимой при создании и редактировании модели
        return [
            'username' => [
                'class' => Input::className(), // обычное текстовое поле
            ],
            'email' => [
                'class' => Input::className(),
            ],
            'role' => [
                'class' => Select::className(), // выпадащий список
                'items' => [User::ROLE_USER => 'Пользователь', User::ROLE_ADMIN => 'Администратор'],
            ],
            'status' => [
                'class' => Select::className(),
                'items' => [User::STATUS_ACTIVE => 'Активен', User::STATUS_DELETED => 'Удалён', User::STATUS_BANNED => 'Заблокирован'],
            ],
        ];
    }
}
```

После создания формы нужно расширить сущность `UserEntity` методом `form()`:

```php
public function form() {
    return [
        'class' => UserForm::className(),
    ];
}
```

Для создания каких-либо дополнительных действий предусмотрен специальный механизм, называемый `actions`. Добавьте в класс `UserForm` следующий метод:

```php
public function actions()
{
    return array_merge(parent::actions(), [
        'ban' => [ // добавляется действие `ban`, которое делает пользователя неактивным
            'class' => Button::className(),
            'label' => 'Забанить',
            'options' => [
                'class' => 'btn btn-danger'
            ],
            'action' => 'ban', // указывается вызываемый метод, может быть как строкой, так и функцией, в которую будет передана модель и форма
            'visible' => !$this->model->getIsNewRecord(),
        ],
    ]);
}

public function actionBan()
{
    $this->model->status = User::STATUS_BANNED;
    return true; // если возвращает `false`, то последующие действия не выполняются и модель не сохраняется
}
```

####Таблицы

Формат записи атрибутов модели такой же как у [GridlView](http://www.yiiframework.com/doc-2.0/guide-output-data-widgets.html#grid-columns).

TBD

####Детальная информация

Формат записи атрибутов модели такой же как у [DetailView](http://www.yiiframework.com/doc-2.0/guide-output-data-widgets.html#detailview).

TBD

###Создание произвольных страниц

Для создания произвольной страницы, вы долны создать контроллер в папке `@app/modules/admin/controllers`.
Он должен наследовать `asdfstudio\admin\controllers\Controller`:

```php
namespace frontend\modules\admin\controllers;

use asdfstudio\admin\controllers\Controller;

class MyController extends Controller
{
    public function actionIndex() {
        return $this->render('@app/modules/admin/views/my/index');
    }
}
```

Есть одна неразрешенная проблема с `$viewPath`. Yii2 не разрешает переопределять его во время выполнения, так что нам придётся передавать полный путь к представлению.
Или вы можете переопределить метод `render()` как показано ниже:

```php
public function render($view, $params = []) {
    return parent::render("@app/modules/admin/views/{$this->id}/{$view}", $params);
}
```

После создания контроллера нам надо зарегистрировать его в админке:

```php
public function init()
{
    parent::init();

    $this->registerController('my', MyController::className()); // creating controller alias (@see $controllerMap)
    $this->registerRoutes([
        $this->urlPrefix . '/my' => $this->id . '/my/index' // creating rule
    ]);
}
```

Не забудьте создать представление `my/index`!
Теперь можете перейти на `/admin/my` и вы увидите свою страницу.

Чтобы добавить страницу в сайдбар нужно в вызвать следующий метод:

```php
$this->sidebar->addItem('My Controller', ['my/index']);
```
