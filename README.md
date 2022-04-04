# Php Smart Search/replace Functionality
[![tests](https://github.com/imanghafoori1/php-smart-search-replace/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/imanghafoori1/php-smart-search-replace/actions/workflows/tests.yml)
## It is much easier than using regex.

### Installation:

```
composer require imanghafoori/php-search-replace
```

### Usage:



1- Lets say you want to remove double semi-colon occurances like these:
```php
$user = 1;;
$user = 2; ;
$user = 3;
;

```
Then you can define a pattern like this:
```php
$pattern = [';;' => ['replace' => ';']];
```
This will catch all the 3 cases above since the neutral php whitespaces are ignored while searching.

-------------------

#### Placeholders:

Here is a copmerehensive list of placeholders you can use:

- `<var>` or `<variable>`: for variables like: `$user`
- `<str>` or `<string>`: for hard coded strings: `'hello'` or "hello"
- `<class_ref>`: for class references:  `\App\User::where(...` , `User::where`
- `<full_class_ref>`: only for full references:  `\App\User::`
- `<until>`: to capture all the code until you reach a certain character.
- `<comment>`: for commands (does not capture doc-blocks)
- `<doc_block>`: for doc-blocks
- `<statement>`: to capture a whole php statement.
- `"<name:nam1,nam2>"` or `<name>`: for method or function names. `->where` or `::where`
- `<white_space>`: for whitespace blocks
- `<bool>` or `'<boolean>'`: for true or false (acts case-insensetive)
- `<number>`: for numeric values
- `<cast>`: for type-casts like: `(array) $a;`
- `<int>` or `<integer>`: for integer values
- `<visibility>`: for public, protected, private
- `<float>`: for floating point number
- `"<global_func_call:func1,func2>"`: to detect global function calls.
- `<in_between>`: to capture code within a pair of  `{...}` or `(...)` or `[...]`
- `<any>`: captures any token.
- **You can also define your own keywords if needed!**

You just define a class for your new keyword and append the class path to the end of `Finder::$keywords[] = MyKeyword::class` property.
Just like the default keywords.


### Example:
lets say you want to remove the optional comma from arrays:
```php
$a = [
   '1',
   '2',
];
$b = ['1','2',];
```
Then you can define a pattern like this:
```php
$pattern = [',<whitespace>?]' => ['replace' => '"<1>"]']];
```
Here the `<whitespace>?` mean an optional whitespace may reside there, and the `"<1>"` means the value that matches the first placeholder should be put there.

