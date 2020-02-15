# EspyFM PHP Client

## LightFM &amp; Elasticsearch Wrapper

[![Source Code][badge-source]][source]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]

EspyFM is a wrapper around Lyst's [LightFM](https://gitlab.com/lyst/lightfm)
library.

With thanks to the Moontoast Math Library for the skeleton used here.

## Installation

The preferred method of installation is via [Composer][]:

```
composer require flaxandteal/espyfm-php
```

## Examples

```php
$espyService = new \EspyFM\EspyFMService;
$user = [
    'category1' => 'toast',
    'category2' => 'visible',
    'myCategorySet' => ['brown', 'white'],
];
$espyRecommendations = $espyService->getRecommendation($user);

$espyRecommendations->forEach(function ($recommend) {
  var_dump($recommend);
});
```

This produces something like the following output:

```
string(18) "Toasters"
string(18) "Carbonization"
```

## License

Copyright &copy; 2020- Flax &amp; Teal Limited
Pre-existing skeleton is copyright &copy; 2013-2016 Moontoast, Inc.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.


[composer]: https://getcomposer.org/

[badge-source]: https://img.shields.io/badge/source-flaxandteal/espyfm-php.svg?style=flat-square
[badge-release]: https://img.shields.io/packagist/v/flaxandteal/espyfm-php.svg?style=flat-square
[badge-license]: https://img.shields.io/gitlab/license/flaxandteal/espyfm-php.svg?style=flat-square

[source]: https://gitlab.com/flaxandteal/espyfm-php
[release]: https://packagist.org/packages/flaxandteal/espyfm-php
[license]: https://gitlab.com/flaxandteal/espyfm-php/blob/master/LICENSE
