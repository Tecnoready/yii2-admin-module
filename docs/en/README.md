#Documentation

##Usage

First create module `admin` in your project's modules dir (e.g. `/path/to/project/frontend/modules/admin`). Then create class `Module` extending `asdfstudio\admin\Module`:

```php
use asdfstudio\admin\Module as AdminModule;

class Module extends AdminModule {

}
```

Now add this code into your config file to `modules` and `bootstrap` sections:

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

It's all. Now you can you can go to the page by `/admin`.


###Registration of models

Now you need to register your first model in dashboard. Create class `UserEntity` in `admin/entities`.
Class should inherit `asdfstudio\admin\base\Admin`.

```php
use asdfstudio\admin\base\Admin;
use common\models\User;

class UserEntity extends Entity
{
    public function labels()
    {
        return ['User', 'Users']; // labels using in admin
    }

    public function slug()
    {
        return 'user'; // path inside admin, e.g. /admin/manage/user[/<id>[/edit]]
    }

    public function model()
    {
        return [
            'class' => User::className(); // model class name
        ];
    }
}
```

Register this entity in your module:

```php
use asdfstudio\admin\Module as AdminModule;

class Module extends AdminModule {
    ...
    public function init() {
        parent::init();

        $this->registerEntity(UserEntity::className()); // this method register entity in admin

        $this->sidebar->addItem(UserEntity::className()); // this adds link to sidebar
    }
    ...
}

```

Now go to `/admin/manage/user` and see table with all site users.

For example see [asdf-studio/yii2-blog-module](https://github.com/asdf-studio/yii2-blog-module).


####Forms

If you have any reason to hide some fields , then you should create new class extending `asdfstudio\admin\forms\Form` and declare needed fields inside:

```php
use asdfstudio\admin\forms\Form;

class UserForm extends Form
{
    public function fields() { // fields list, which will shown on creating or updating model
        return [
            'username' => [
                'class' => Input::className(), // plain text field
            ],
            'email' => [
                'class' => Input::className(),
            ],
            'role' => [
                'class' => Select::className(), // dropdown list
                'items' => [User::ROLE_USER => 'User', User::ROLE_ADMIN => 'Administrator'],
            ],
            'status' => [
                'class' => Select::className(),
                'items' => [User::STATUS_ACTIVE => 'Active', User::STATUS_DELETED => 'Deleted', User::STATUS_BANNED => 'Banned'],
            ],
        ];
    }
}
```

After creating form need to add method `form()` into `UserEntity`:

```php
public function form() {
    return [
        'class' => UserForm::className(),
    ];
}
```

For creating additional actions there are special feature called `actions`. Add this code to `UserForm`:

```php
public function actions()
{
    return array_merge(parent::actions(), [
        'ban' => [ // added action `ban`, which makes user banned
            'class' => Button::className(),
            'label' => 'Ban',
            'options' => [
                'class' => 'btn btn-danger'
            ],
            'action' => 'ban', // declares called method, can be string or callable which will be passed model and form
            'visible' => !$this->model->getIsNewRecord(),
        ],
    ]);
}

public function actionBan()
{
    $this->model->status = User::STATUS_BANNED;
    return true; // if returns `false` next actions will be ignored and model wouldn't save
}
```

####Tables

Attributes format is similar to [GridlView](http://www.yiiframework.com/doc-2.0/guide-output-data-widgets.html#grid-columns).

TBD

####Detailed

Attributes format is similar to [DetailView](http://www.yiiframework.com/doc-2.0/guide-output-data-widgets.html#detailview).

TBD

###Creating custom pages

For creating custom page you should create controller in your `@app/modules/admin/controllers` dir.
It should inherit `asdfstudio\admin\controllers\Controller`:

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

There is problem with `$viewPath`. Yii2 doesn't allow to override `$viewPath` in runtime, so we need to pass full path to our view.
Also you can override `render()` method:

```php
public function render($view, $params = []) {
    return parent::render("@app/modules/admin/views/{$this->id}/{$view}", $params);
}
```

After creating controller we need to register it in admin module:

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

Don't forget to create view `my/index`!
Now go to `/admin/my` and you can see your page.

For add page into sidebar you should call this method:

```php
$this->sidebar->addItem('My Controller', ['my/index']);
```
