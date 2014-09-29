## View Class

Jelly View Class

### Initializing the Class

------

#### $this->getFieldData();

The function get form element data.

```php
<?php
Array
(
    [name] => username
    [title] => Username
    [rules] => required|min(3)|max(15)
    [attribute] =>  id="username"
    [type] => input
    [value] =>
)
```

#### $this->getExtraData();

The function form element extra data. This extra data for groups.

```php
<?php
Array
(
    [label] => Username
    [type] => input
    [func] => 
    [group] => 0
)
```

#### View class call the any element class.

```php
<?php
// E.g. Obullo\Jelly\View\Elements\Textarea
$Element = 'Obullo\Jelly\View\Elements\\'. ucfirst($val['extra']['type']);
$this->element = new $Element; // Initialized element class
$this->element->render($this); // Send "View" object
```

After that you can follow the elements.

### Elements

-----

<ul>
    <li>Button</li>
    <li>Captcha</li>
    <li>Checkbox</li>
    <li>Dropdown</li>
    <li>Hidden</li>
    <li>Input</li>
    <li>Password</li>
    <li>Radio</li>
    <li>Reset</li>
    <li>Submit</li>
    <li>Textarea</li>
    <li>Upload</li>
</ul>

```php
<?php
$c->laod('jelly/form as jellyForm');
$data  = $view->getFieldData();
$extra = $view->getFieldExtraData();
```

#### $view->isAjax();

If the form is ajax we set attribute <b>onClick="submitAjax('userForm');"</b>

```php
<?php
if ($view->isAjax() === true) {
    $formId = "'". $view->formData['form_id'] ."'";
    $data['attribute'] = ' onClick="'. sprintf($this->jellyForm->params['ajax.function'], $formId) .'" ';
}
```

#### $view->isGroup();

If this elements of a group, the <kbd>groupedElementDiv()</kbd> function we can send "repeatedDiv" adds div.

```php
<?php
if ($view->isGroup() === true) {
    return $this->jellyForm->groupedElementDiv($element, 'repeatedDiv');
}
```

#### $view->isGrouped();

If this elements of grouped, the <kbd>groupedElementDiv()</kbd> function we can send "groupedDiv" adds div.

```php
<?php
if ($view->isGrouped() === true) {
    return $this->jellyForm->groupedElementDiv(
        $view->getGroupElementsTemp(),
        'groupedDiv',
        $extra['label']
    );
}
```

Using complete with group.

```php
<?php
if ($view->isGroup() === true) {
    if ($view->isGrouped() === true) {
        return $this->jellyForm->groupedElementDiv(
            $view->getGroupElementsTemp(),
            'groupedDiv',
            $extra['label']
        );
    }
}
return $this->jellyForm->groupedElementDiv($element, 'repeatedDiv');
```

### Function Reference

-----

#### $this->isAjax();

Form is ajax? Returns to boolean.

#### $this->isGroup();

Elements is group? Returns to boolean.

#### $this->isGrouped()

Elements is grouped? Returns to boolean.

#### $this->getGroupElementsTemp()

Get group elements temporary data.

#### $this->render($data = array())

Render html form.

#### $this->form()

Returns to html form.