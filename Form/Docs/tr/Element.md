
## Form Elementleri Oluşturmak

Form element sınıfı html formları, html form elementleri ve form etiketi ile ilgili girdileri kolayca oluşturmanıza yardımcı olur. Ayrıca form güvenliğine ilişkin veriler örneğin Csrf token form metodu kullanıldığında otomatik olarak oluşturulur.

<ul>
    <li><a href="#form">$this->element->form()</a></li>
    <li><a href="#formClose">$this->element->formClose()</a></li>
    <li><a href="#formMultipart">$this->element->formMultipart()</a></li>
</ul>


### Sınıfı Yüklemek

-------

```php
$this->c['element']->method();
```

### Servis Kurulumu

Form element sınıfı opsiyonel olarak kullanılır bu yüzden çalışabilmesi için aşağıdaki gibi bir servis kurulumuna ihtiyaç duyar.

```php
namespace Service;

use Obullo\Container\Container;
use Obullo\Form\Element as FormElement;
use Obullo\Service\ServiceInterface;

class Element implements ServiceInterface
{
    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register(Container $c)
    {
        $c['element'] = function () use ($c) {
            return new FormElement($c);
        };
    }
}

// END Element service

/* End of file Element.php */
/* Location: .app/classes/Service/Element.php */
```

<a name="form"></a>


#### $this->element->form()

Creates an opening form tag with a base URL <b>built from your config preferences</b>. It will optionally let you add form attributes and hidden input fields.

The main benefit of using this tag rather than hard coding your own HTML is that it permits your site to be more portable in the event your URLs ever change.

Here's a simple example:

```php
echo $this->element->form('email/send');
```

The above example would create a form that points to your base URL plus the "email/send" URI segments, like this:

```php
<form method="post" action="http:/example.com/index.php/email/send" />
```

<b>Adding Attributes</b>

Attributes can be added by passing an associative array to the second parameter, like this:

```php
$attributes = array('class' => 'email', 'id' => 'myform');
echo $this->element->form('email/send', $attributes);
```

The above example would create a form similar to this:

```php
<form method="post" action="http:/example.com/index.php/email/send"  class="email"  id="myform" />
```

<b>Adding Hidden Input Fields</b>

Hidden fields can be added by passing an associative array to the third parameter, like this:

```php
$hidden = array('username' => 'Joe', 'member_id' => '234');
echo $this->element->form('email/send', '', $hidden);
```

The above example would create a form similar to this:

```php
<form method="post" action="http:/example.com/index.php/email/send">
<input type="hidden" name="username" value="Joe" />
<input type="hidden" name="member_id" value="234" />
```

#### $this->element->formMultipart()

This function is absolutely identical to the $this->element->open() tag above except that it adds a multipart attribute, which is necessary if you would like to use the form to upload files with.

#### $this->element->hidden('name', 'value' , $attributes = '')

Lets you generate hidden input fields. You can either submit a name/value string to create one field:

```php
<?php
$this->element->hidden('username', 'johndoe',  $attr = " id='username' " );

// Would produce:
<input type="hidden" name="username" value="johndoe" id='username'  />
```

Or you can submit an associative array to create multiple fields:

```php
$data = array(
              'name'  => 'John Doe',
              'email' => 'john@example.com',
              'url'   => 'http://example.com'
        );
echo $this->element->hidden($data);

// Would produce:

<input type="hidden" name="name" value="John Doe" />
<input type="hidden" name="email" value="john@example.com" />
<input type="hidden" name="url" value="http://example.com" />
```

#### $this->element->input('name', 'value',$attributes = '')

Lets you generate a standard text input field. You can minimally pass the field name and value in the first and second parameter:

```php
echo $this->element->input('username', 'johndoe', $attributes = '');
```

Or you can pass an associative array containing any data you wish your form to contain:

```php
$data = array(
    'name'      => 'username',
    'id'        => 'username',
    'value'     => 'johndoe',
    'maxlength' => '100',
    'size'      => '50',
    'style'     => 'width:50%',
);

echo $this->element->input($data);

// Would produce:

<input type="text" name="username" id="username" value="johndoe" maxlength="100" size="50" style="width:50%" />
```

If you would like your form to contain some additional data, like Javascript, you can pass it as a string in the third parameter:

```php
$js = 'onclick="someFunction()"';
echo $this->element->input('username', 'johndoe', $js);
```

#### $this->element->password()

This function is identical in all respects to the <dfn>$this->element->input()</dfn> function above except that it sets a "password" type.

#### $this->element->upload()

This function is identical in all respects to the <dfn>$this->element->input()</dfn> function above except that it sets a "file" type, allowing it to be used to upload files.

#### $this->element->textarea()

This function is identical in all respects to the <dfn>$this->element->input()</dfn> function above except that it generates a "textarea" type. Note: Instead of the "maxlength" and "size" attributes in the above example, you will specify "rows" and "cols".

#### $this->element->dropdown()

Lets you create a standard drop-down field. The first parameter will contain the name of the field, the second parameter will contain an associative array of options, and the third parameter will contain the value you wish to be selected. You can also pass an array of multiple items through the third parameter, and Obullo will create a multiple select for you. Example:

```php
$options = array(
    'small'  => 'Small Shirt',
    'med'    => 'Medium Shirt',
    'large'  => 'Large Shirt',
    'xlarge' => 'Extra Large Shirt',
);

$shirtsOnSale = array('small', 'large');
echo $this->element->dropdown('shirts', $options, 'large');

// Would produce:

<select name="shirts">
<option value="small">Small Shirt</option>
<option value="med">Medium Shirt</option>
<option value="large" selected="selected">Large Shirt</option>
<option value="xlarge">Extra Large Shirt</option>
</select>

echo $this->element->dropdown('shirts', $options, 'shirtsOnSale');

// Would produce:

<select name="shirts" multiple="multiple">
<option value="small" selected="selected">Small Shirt</option>
<option value="med">Medium Shirt</option>
<option value="large" selected="selected">Large Shirt</option>
<option value="xlarge">Extra Large Shirt</option>
</select>
```

If you would like the opening <b>select</b> to contain additional data, like an id attribute or JavaScript, you can pass it as a string in the fourth parameter:

```php
$js = 'id="shirts" onChange="someFunction();"';
echo $this->element->dropdown('shirts', $options, 'large', $js);
```

If the array passed as $options is a multidimensional array, $this->element->dropdown() will return the array keys as the label.

```php
<?php
echo $this->element->dropdown('business_size', '@getSchema.users.business_size.func', 'xlarge');
```

Adding custom options.

```php
<?php
echo $this->element->dropdown('business_size', array(array('' => 'Please specify a field .. '), '@getSchema.users.business_size.func'));
```

```php
<?php
echo $this->element->dropdown('business_size', '@getSchema.users.business_size.func.list', 'medium');
```

#### $this->element->fieldset()

Lets you generate fieldset/legend fields.

```php
<?php
echo $this->element->fieldset('Address Information');
echo "<p>fieldset content here</p>\n";
echo $this->element->fieldsetClose();

// Produces
<fieldset>
<legend>Address Information</legend>
<p>form content here</p>
</fieldset>
```

#### $this->element->fieldsetClose()

Produces a closing <b>fieldset</b> tag. The only advantage of using this function is it permits you to pass data to it which will be added below the tag. For example:

```php
<?php
$string = "</div></div>";

echo $this->element->fieldsetClose($string);

// Would produce:
</fieldset>
</div></div>
```

As with other functions, if you would like the tag to contain additional data, like JavaScript, you can pass it as a string in the fourth parameter:

```php
<?php
$js = 'onClick="someFunction()"';

echo $this->element->checkbox('newsletter', 'accept', true, $js)
```

#### $this->element->radio()

This function is identical in all respects to the <dfn>$this->element->checkbox()</dfn> function above except that is sets it as a "radio" type.

#### $this->element->submit()

Lets you generate a standard submit button. Simple example:

```php
<?php
echo $this->element->submit('mysubmit', 'Submit Post!');

// Would produce:

<input type="submit" name="mysubmit" value="Submit Post!" />
```

#### $this->element->reset()

Lets you generate a standard reset button. Use is identical to <dfn>$this->element->submit()</dfn>.

#### $this->element->button()

Lets you generate a standard button element. You can minimally pass the button name and content in the first and second parameter:

```php
<?php
echo $this->element->button('name','content');

// Would produce
<button name="name" type="button">Content</button> 
```

Or you can pass an associative array containing any data you wish your form to contain: 

```php
<?php
$data = array(
    'name'    => 'button',
    'id'      => 'button',
    'value'   => 'true',
    'type'    => 'reset',
    'content' => 'Reset'
);

echo $this->element->button($data);

// Would produce:
<button name="button" id="button" value="true" type="reset">Reset</button>  
```

If you would like your form to contain some additional data, like JavaScript, you can pass it as a string in the third parameter: 

```php
<?php
$js = 'onClick="someFunction()"';
echo $this->element->button('mybutton', 'Click Me', $js);
```

#### $this->element->formClose()

Produces a closing tag. The only advantage of using this function is it permits you to pass data to it which will be added below the tag. For example:

```php
<?php
$string = "</div></div>";

echo $this->element->formClose($string);

// Would produce:

</form>
</div></div>
```

#### $this->element->prep()

Allows you to safely use HTML and characters such as quotes within form elements without breaking out of the form. Consider this example:

```php
$string = 'Here is a string containing <strong>"quoted"</strong> text.';
<input type="text" name="myform" value="$string" />
```

Since the above string contains a set of quotes it will cause the form to break. The form\prep function converts HTML so that it can be used safely:

```php
<input type="text" name="myform" value="<?php echo $this->element->prep($string); ?>" />
```


**Note:** If you use any of the form helper functions listed in this page the form values will be prepped automatically, so there is no need to call this function. Use it only if you are creating your own form elements.


### Tehlikeli Girdi Değerlerinden Kaçış

You may need to use HTML and characters such as quotes within your form elements. In order to do that safely, you’ll need to use common function html_escape().

Aşağıdaki örneği gözönünde bulundurursak:

```php
$string = 'Here is a string containing "quoted" text.';

<input type="text" name="myfield" value="<?php echo $string; ?>" />
```

Since the above string contains a set of quotes, it will cause the form to break. The html_escape() function converts HTML special characters so that it can be used safely:

```php
<input type="text" name="myfield" value="<?php echo $this->c['clean']->escape($string); ?>" />
Note
```

If you use any of the form helper functions listed on this page, the form values will be automatically escaped, so there is no need to call this function. Use it only if you are creating your own form elements.




### Function Reference

-----

#### $this->element->form($action = '', $attributes = '', $hidden = array());

Form Declaration Creates the opening portion of the form.

#### $this->element->formClose($extra = '');

Form close tag

#### $this->element->button($data = '', $content = '', $extra = '');

Form button

#### $this->element->checkbox($data = '', $value = '', $checked = false, $extra = '');

Checkbox field

#### $this->element->dropdown($name = '', $options = '', $selected = array(), $extra = '');

Drop-down Menu

#### $this->element->fieldset($legend_text = '', $attributes = array());

Used to produce ```<fieldset><legend>text</legend>```. To close fieldset use form_fieldset_close()

#### $this->element->fieldsetClose($extra = '');

Fieldset Close

#### $this->element->hidden($name, $value = '', $extra = '', $recursing = false);

Generates hidden fields.  You can pass a simple key/value string or an associative array with multiple values.

#### $this->element->input($data = '', $value = '', $extra = '');

Text input field

#### $this->element->label($label_text = '', $id = '', $attributes = "");

Form label


#### $this->element->formMultipart($action, $attributes = array(), $hidden = array());

Form Declaration - Multipart type Creates the opening portion of the form, but with "multipart/form-data".

#### $this->element->password($data = '', $value = '', $extra = '');

Identical to the input function but adds the "password" type

#### $this->element->prep($str = '', $field_name = '');

Formats text so that it can be safely placed in a form field in the event it has HTML tags.

#### $this->element->radio($data = '', $value = '', $checked = false, $extra = '')

Radio button

#### $this->element->reset($data = '', $value = '', $extra = '')

Reset button

#### $this->element->submit($data = '', $value = '', $extra = '')

Submit button

#### $this->element->textarea($data = '', $value = '', $extra = '')

Textarea filed

#### $this->element->upload($data = '', $value = '', $extra = '')

Identical to the input function but adds the "file" type