# Php Smart Search/replace Functionality
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

### Keywords:

- '\<white_space\>'
- '\<until\>'
- '\<in_between\>'
- '\<comment\>'
- '\<variable\>' or '\<var\>'
- '\<string\>' or '\<str\>'
- '\<any\>'

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
$pattern = [',"<whitespace>?"]' => ['replace' => '"<1>"]']];
```
Here the `"<whitespace>?"` mean an optional white space may reside there, and the `"<1>"` means the value that matches the first placeholder should be put there.

